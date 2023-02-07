<?php

namespace App\Http\Controllers\API\HumanResource\ITF;

use App\Http\Controllers\ApiController;
use App\Models\General\ActualSign;
use App\Models\HumanResource\ITF\ItfDetail;
use App\Models\HumanResource\ITF\ItfMain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItfController extends ApiController
{
    public function saveItf(Request $request)
    {
        DB::beginTransaction();
        try {

        log::debug($request);

        $guid = $this->getGuid();
        $reqRef = $this->getItfRef($request->companyId);

        $itfData = $request->itineraryData;
        $itfData = json_decode($itfData, true);


        // ITF Main one row
        $itfMain = new ItfMain();
        $itfMain->reference = $reqRef;
        $itfMain->request_date = now();
        $itfMain->date_needed = now();
        $itfMain->status = 'In Progress';
        $itfMain->UID = $request->loggedUserId;
        $itfMain->fname = $request->loggedUserFirstName;
        $itfMain->lname = $request->loggedUserLastName;
        $itfMain->department = $request->loggedUserDepartment;
        $itfMain->reporting_manager = $request->reportingManagerName;
        $itfMain->position = $request->loggedUserPosition;
        $itfMain->GUID = $guid;
        $itfMain->TITLEID = $request->companyId;
        $itfMain->webapp = 1;

        $itfMain->save();

        // ITF details
        for ($i = 0; $i < count($itfData); $i++) {
            $itfArray[] = [

                'main_id' => $itfMain->id,
                'client_id' => $itfData[$i]['client_id'],
                'client_name' => $itfData[$i]['client_name'],
                'time_start' => date_create($itfData[$i]['time_start']),
                'time_end' => date_create($itfData[$i]['time_end']),
                'actual_start' => null,
                'actual_end' => null,
                'purpose' => $itfData[$i]['purpose'],
            ];
        }
        ItfDetail::insert($itfArray);

//         //Insert general.actual_sign
//         for ($x = 0; $x < 5; $x++) {
//             $array[] = array(
// 'PROCESSID'         => $itfMain->id,
// 'USER_GRP_IND'      => '0',
// 'FRM_NAME'          => 'Itinerary Request',
// 'FRM_CLASS'         => 'frmItinerary',
// 'STATUS'            => 'Not Started',
// 'TS'                => now(),
// 'DUEDATE'           => now(),
// 'ORDERS'            => $x,
// 'REFERENCE'         => $reqRef,
// 'PODATE'            => now(),
// 'DATE'              => now(),
// 'INITID'            => $request->loggedUserId,
// 'FNAME'             => $request->loggedUserFirstName,
// 'LNAME'             => $request->loggedUserLastName,
// 'DEPARTMENT'        => $request->loggedUserDepartment,
// 'RM_ID'             => $request->reportingManagerId,
// 'REPORTING_MANAGER' => $request->reportingManagerName,
// 'PROJECTID'         => '1',
// 'PROJECT'           => $request->loggedUserDepartment,
// 'COMPID'            => $request->companyId,
// 'COMPANY'           => $request->companyName,
// 'TYPE'              => 'Itinerary Request',
// 'CLIENTID'          => '1',
// 'CLIENTNAME'        => $request->companyName,
// 'Max_approverCount' => '5',
// 'DoneApproving'     => '0',
// 'Payee'             => 'N/A',
//             );
//         }

//         if ($array[0]['ORDERS'] == 0) {
//             $array[0]['USER_GRP_IND'] = 'Approval of Reporting Manager';
//             $array[0]['STATUS'] = 'In Progress';
//         }

//         if ($array[1]['ORDERS'] == 1) {
//             $array[1]['USER_GRP_IND'] = 'Input of Actual Time (Initiator)';
//         }

//         if ($array[2]['ORDERS'] == 2) {
//             $array[2]['USER_GRP_IND'] = 'Approval of Reporting Manager';
//         }

//         if ($array[3]['ORDERS'] == 3) {
//             $array[3]['USER_GRP_IND'] = 'Acknowledgement of Human Resource';
//         }

//         if ($array[4]['ORDERS'] == 4) {
//             $array[4]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
//         }

//         ActualSign::insert($array);

        $isInserted = $this->insertActualSign($request, $itfMain->id, 'Itinerary Request', $reqRef);
        if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');

        DB::commit();
        return response()->json(['message' => 'Your Itinerary request was successfully submitted.'], 200);




    } catch (\Exception $e) {
        DB::rollback();
        log::debug($e);
    
        // throw error response
        return response()->json($e, 500);
    }



    }

    public function getItfMain($id)
    {
        $reMain = ItfMain::findOrFail($id);
        return $this->showOne($reMain);
    }

    public function getItfDetails($id)
    {
        $ItfDetails = ItfDetail::where('main_id', $id)->get();
        return response()->json(['data' => $ItfDetails, 'code' => 200]);
    }

    public function getItfActualDetails($id)
    {
        $ItfDetails = DB::select("SELECT 
        a.`id`,
        a.`main_id`,
        a.`client_id`,
        a.`client_name`, 
        a.`time_start`, 
        a.`time_end`, 
        IFNULL	(a.`actual_start`,a.`time_start`) AS 'actual_start',
        IFNULL	(a.`actual_end`,a.`time_end`) AS 'actual_end',
        a.`purpose`
        FROM humanresource.`itinerary_details` a 
        WHERE a.`main_id` = '" . $id . "'");
        return response()->json(['data' => $ItfDetails, 'code' => 200]);
    }

    public function approveActualItfInputs(Request $request){
        
        $this->deleteItfDetails($request);
        $this->insertItfDetails($request);
        $this->approveActualSIgnApi($request);

        return response()->json(['message' => 'Itinerary Request has been Successfully approved'], 200);

    }
}
