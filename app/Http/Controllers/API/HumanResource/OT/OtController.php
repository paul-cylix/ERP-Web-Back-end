<?php

namespace App\Http\Controllers\API\HumanResource\OT;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\HumanResource\OT\OtMain;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\General\ActualSign;

class OtController extends ApiController
{
    public function validateOT(Request $request)
    {

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $in = $otData[0]['ot_in'];
        $in = date_create($in);
        $in = date_format($in, 'Y-m-d H:i:s');

        $out = $otData[0]['ot_out'];
        $out = date_create($out);
        $out = date_format($out, 'Y-m-d H:i:s');

        $employee_id = $otData[0]['employee_id'];



        if($request->isActual === 'true'){

            $actualin = $otData[0]['ot_in_actual'];
            $actualin = date_create($actualin);
            $actualin = date_format($actualin, 'Y-m-d H:i:s');
    
            $actualout = $otData[0]['ot_out_actual'];
            $actualout = date_create($actualout);
            $actualout = date_format($actualout, 'Y-m-d H:i:s');

            if (DB::table('humanresource.overtime_request')
            ->where('employee_id', $employee_id)
            ->where('ot_in_actual', $actualin)
            ->where('ot_out_actual', $actualout)

            ->exists()
            ) {
                return response()->json(['message' => 'This Overtime date is already exist!'], 202);
            }
            return response()->json(['message' =>'True Added Successfully'], 200);

            
        }


        if (DB::table('humanresource.overtime_request')
            ->where('employee_id', $employee_id)
            ->where('ot_in_actual', $in)
            ->where('ot_out_actual', $out)

            ->exists()
        ) {
            return response()->json(['message' => 'This Overtime date is already exist!'], 202);
        }
        return response()->json(['message' =>'Added Successfully'], 200);
    }


    public function validateActualOT(Request $request)
    {

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $in = $otData[0]['ot_in_actual'];
        $in = date_create($in);
        $in = date_format($in, 'Y-m-d H:i:s');

        $out = $otData[0]['ot_out_actual'];
        $out = date_create($out);
        $out = date_format($out, 'Y-m-d H:i:s');


        $employee_id = $otData[0]['employee_id'];


        if (DB::table('humanresource.overtime_request')
            ->where('employee_id', $employee_id)
            ->where('ot_in_actual', $in)
            ->where('ot_out_actual', $out)

            ->exists()
        ) {
            return response()->json(['message' => 'This Overtime date is already exist!'], 202);
        }
        return response()->json(['message' => 'Added Successfully'], 200);
    }




    




    public function saveOT(Request $request)
    {
        DB::beginTransaction();
        try
        {  

        log::debug($request);

        $guid = $this->getGuid();
        $reqRef = $this->getOtRef($request->companyId);

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $dateRequested = date_create($request->dateRequested);

        $mainID = DB::select("SELECT IFNULL(MAX(main_id),0) + 1 AS main FROM humanresource.`overtime_request`");


        // insert to hr.ot main table
        for ($i = 0; $i < count($otData); $i++) {

            log::debug($otData);

            $ot_date = date_create($otData[$i]['overtime_date']);
            $ot_in = date_create($otData[$i]['ot_in']);
            $ot_out = date_create($otData[$i]['ot_out']);

            $setOTData[] = [
                'reference' => $reqRef,
                'request_date' => now(),
                'overtime_date' => date_format($ot_date, 'Y-m-d'),
                'ot_in' => date_format($ot_in, 'Y-m-d H:i:s'),
                'ot_out' => date_format($ot_out, 'Y-m-d H:i:s'),
                'ot_totalhrs' => $otData[$i]['ot_totalhrs'],
                'employee_id' => $otData[$i]['employee_id'],
                'employee_name' => $otData[$i]['employee_name'],
                'purpose' => $otData[$i]['purpose'],
                'status' => 'In Progress',
                'UID' => $request->loggedUserId,
                'fname' => $request->loggedUserFirstName,
                'lname' => $request->loggedUserLastName,
                'department' => $request->loggedUserDepartment,
                'reporting_manager' => $request->reportingManagerName,
                'position' => $request->loggedUserPosition,
                'ts' => now(),
                'GUID' => $guid,
                // 'comments' => , 
                // 'ot_in_actual' => , 
                // 'ot_out_actual' => ,
                // 'ot_totalhrs_actual' => , 
                'main_id' => $mainID[0]->main,
                'remarks' => $otData[$i]['purpose'],
                'cust_id' => $otData[$i]['cust_id'],
                'cust_name' => $otData[$i]['cust_name'],
                'TITLEID' => $request->companyId,
                'PRJID' => $otData[$i]['PRJID'],
                'webapp' => 1
            ];
        }

        DB::table('humanresource.overtime_request')->insert($setOTData);

        $this->insertAttachments($request,$mainID[0]->main,$reqRef);


//         //Insert general.actual_sign
//         for ($x = 0; $x < 4; $x++) {
//             $array[] = array(
// 'PROCESSID'         => $mainID[0]->main,
// 'USER_GRP_IND'      => '0',
// 'FRM_NAME'          => $request->form,
// 'FRM_CLASS'         => 'frmOvertimeRequest',             //Hold
// 'REMARKS'           => '',
// 'STATUS'            => 'Not Started',
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
// 'PROJECTID'         => '0',
// 'PROJECT'           => $request->loggedUserDepartment,
// 'COMPID'            => $request->companyId,
// 'COMPANY'           => $request->companyName,
// 'TYPE'              => $request->form,
// 'CLIENTID'          => '0',
// 'CLIENTNAME'        => $request->companyName,
// 'Max_approverCount' => '4',
// 'DoneApproving'     => '0',
// 'Payee'             => 'N/A',
// 'Amount'            => 0,
//             );
//         }

//         if ($array[0]['ORDERS'] == 0) {
//             $array[0]['USER_GRP_IND'] = 'Acknowledgement of Reporting Manager';
//             $array[0]['STATUS'] = 'In Progress';
//         }

//         if ($array[1]['ORDERS'] == 1) {
//             $array[1]['USER_GRP_IND'] = 'Input of Actual Overtime (Initiator)';
//         }

//         if ($array[2]['ORDERS'] == 2) {
//             $array[2]['USER_GRP_IND'] = 'Approval of Reporting Manager';
//         }

//         if ($array[3]['ORDERS'] == 3) {
//             $array[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
//         }

//         DB::table('general.actual_sign')->insert($array);
    

        $isInserted = $this->insertActualSign($request, $mainID[0]->main, 'Overtime Request', $reqRef);
        if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');


        
        DB:: commit();
        return response()->json(['message' => 'Your Overtime Request was successfully submitted.'], 200);

        }
        catch(\Exception $e)
        {
            DB:: rollback();
            return response()->json($e, 500);
        }
        
    }

    public function getOtMain($id){
        // $otData = OtMain::where('main_id',$id)->get();
        // $otData = DB::select("SELECT *,(SELECT b.`project_name` FROM general.`setup_project` b WHERE b.`project_id` = a.`PRJID` ) AS 'PRJNAME' FROM humanresource.`overtime_request` a WHERE a.`main_id` = '".$id."' AND a.`status` <> 'Removed'");
        $otData = DB::select("SELECT *,(SELECT b.`project_name` FROM general.`setup_project` b WHERE b.`project_id` = a.`PRJID` ) AS 'PRJNAME', (SELECT c.`id` FROM general.`users` c WHERE c.`UserFull_name` = a.`reporting_manager` LIMIT 1) AS 'IDOFRM' FROM humanresource.`overtime_request` a WHERE a.`main_id` = '".$id."' AND a.`status` <> 'Removed'");
        return response()->json($otData, 200);
    }

    public function getActualOtMain($id){
        $otData = DB::select("SELECT
        a.`employee_id`,
        a.`employee_name`,
        a.`ot_in`,
        a.`ot_out`,
        a.`ot_totalhrs`,
        IFNULL(a.`ot_in_actual`, a.`ot_in`) AS 'ot_in_actual',
        IFNULL(a.`ot_out_actual`, a.`ot_out`) AS 'ot_out_actual',
        IFNULL(a.`ot_totalhrs_actual`, a.`ot_totalhrs`) AS 'ot_totalhrs_actual',
        a.`purpose`,
        a.`reference`,
        a.`request_date`,
        a.`main_id`,
        a.`reporting_manager`,
        a.`PRJID`,
        a.`cust_name`,
        a.`cust_id`,
        (SELECT project_name FROM general.`setup_project` WHERE project_id = a.`PRJID`) AS 'PRJNAME',
        a.`overtime_date`,
        a.`id`,
        a.`status`
      FROM
        humanresource.`overtime_request` a
      WHERE a.`main_id` = $id AND a.`status` <> 'Removed'" );
        return response()->json($otData, 200);
    }

    public function approveOTbyInit(Request $request){
        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $arrOTId = $request->otId; 
        $arrOTId = json_decode($arrOTId, true);


        if (!empty($arrOTId)) {
            foreach ($arrOTId as $id){
                DB::table('humanresource.overtime_request')->where('id', $id)->update(['status' => "Removed"]);
            }
        } 
        

        if(!empty($otData)){

            for($i = 0; $i <count($otData); $i++) {
                $ot_in = date_create($otData[$i]['ot_in']);   
                $ot_out = date_create($otData[$i]['ot_out']);  
                $ot_in_actual = date_create($otData[$i]['ot_in_actual']);
                $ot_out_actual = date_create($otData[$i]['ot_out_actual']);   
 
                
                // DB::update("UPDATE humanresource.`overtime_request` SET `ot_in_actual` = '".date_format($ot_in_actual, 'Y-m-d H:i:s')."', ot_out_actual = '".date_format($ot_out_actual, 'Y-m-d H:i:s')."', ot_totalhrs_actual = '".$otData[$i]['ot_totalhrs_actual']."' WHERE `id` = '".$otData[$i]['id']."' ;");

                DB::table('humanresource.overtime_request')->where('id', $otData[$i]['id'])
                ->update(
                    [
                        'ot_in' => $ot_in,
                        'ot_out' => $ot_out,
                        'ot_totalhrs' => $otData[$i]['ot_totalhrs'],
                        'purpose' => $otData[$i]['purpose'],
                        'ot_in_actual' => $ot_in_actual,
                        'ot_out_actual' => $ot_out_actual,
                        'ot_totalhrs_actual' => $otData[$i]['ot_totalhrs_actual'],
                        'remarks' => $otData[$i]['purpose'],
                        'cust_id' => $otData[$i]['cust_id'],
                        'cust_name' => $otData[$i]['cust_name'],
                        'PRJID' => $otData[$i]['PRJID']
                    ]
                );
            }
            // DB::table('general.actual_sign')->where('PROCESSID', $request->processId)->where('FRM_NAME',$request->form)->where('COMPID',$request->companyId)
            // ->update(
            //     [
            //         'PROJECTID'  => $ot_in,
            //         'PROJECT'    => $ot_in,
            //         'CLIENTID'   => $ot_in,
            //         'CLIENTNAME' => $ot_in
            //     ]
            // );
            

            DB::update("UPDATE general.`actual_sign` SET `webapp` = 1, `status` = 'Completed', UID_SIGN = '".$request->loggedUserId."', SIGNDATETIME = NOW(), ApprovedRemarks = '" .$request->remarks. "' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_NAME` = '".$request->form."' AND `COMPID` = '".$request->companyId."'  ;");
            DB::update("UPDATE general.`actual_sign` SET `status` = 'In Progress' WHERE `status` = 'Not Started' AND PROCESSID = '".$request->processId."' AND `FRM_NAME` = '".$request->form."' AND `COMPID` = '".$request->companyId."' LIMIT 1;");
        
            return response()->json(['message' => 'Overtime Request has been Successfully approved'], 200);
            
        };
    }

    public function isRmApproval($id, $companyId){
       $isRmApproval = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a WHERE a.`FRM_NAME` = 'Overtime Request' AND a.`PROCESSID` = '".$id."' AND a.`COMPID` = '".$companyId."' AND a.`ORDERS` = 2 AND a.`STATUS` = 'In Progress'), FALSE) AS 'isRmApproval'");
       return response()->json(
        ['message' => 'Request has been success',
         'data' => $isRmApproval,
         'status' => true
        ], 200);

    }

    // TRANSFER overtime_request to 10.0.9.10 (server) from Alibaba Cloud
    public function cloneCloudHROT(){
        // GET OT data from Alibaba
        $response = Http::get('http://portal.cylix.ph/ctiportal/public/api/get-getHROT');

        // CHECK if there is OT data to transfer
        if ($response['status']) {

        $overtime_request = $response['overtime_request'];

        // INSERT RESPONSE OT DATA to 10.0.9.10 / server
        DB::beginTransaction();
        try { 
        for ($i = 0; $i < count($overtime_request); $i++) {
            $request_date  = date_create($overtime_request[$i]['request_date']);
            $overtime_date = date_create($overtime_request[$i]['overtime_date']);
            $ot_in         = date_create($overtime_request[$i]['ot_in']);
            $ot_out        = date_create($overtime_request[$i]['ot_out']);
            $ts            = date_create($overtime_request[$i]['ts']);

            $setOTData[] = [
                'reference'         => '',
                'request_date'      => date_format($request_date, 'Y-m-d'),      // meron
                'overtime_date'     => date_format($overtime_date, 'Y-m-d'),     // meron
                'ot_in'             => date_format($ot_in, 'Y-m-d H:i:s'),       // meron
                'ot_out'            => date_format($ot_out, 'Y-m-d H:i:s'),      // meron
                'ot_totalhrs'       => $overtime_request[$i]['ot_totalhrs'],     // meron ot_hours
                'employee_id'       => $overtime_request[$i]['employee_id'],     // meron employee_id
                'employee_name'     => $overtime_request[$i]['employee_name'],   // meron emplyee_name
                'purpose'           => $overtime_request[$i]['purpose'],         // meron purpose
                'status'            => 'In Progress',                            // static
                'UID'               => $overtime_request[$i]['UID'],             // meron user_id
                'fname'             => '',                                       // for query
                'lname'             => '',                                       // for query
                'department'        => '',                                       // for query
                'reporting_manager' => '',                                       // for query
                'position'          => '',                                       // for query
                'ts'                => date_format($ts, 'Y-m-d H:i:s'),
                'GUID'              => '',                                       // generate
                'main_id'           => 0,                                        // wala pa ito yung primary key nila
                'remarks'           => $overtime_request[$i]['purpose'],         // meron purpose
                'cust_id'           => $overtime_request[$i]['cust_id'],         // meron project_id
                'cust_name'         => $overtime_request[$i]['cust_name'],       // meron project_name
                'TITLEID'           => $overtime_request[$i]['TITLEID'],         // meron companyId
                'PRJID'             => $overtime_request[$i]['PRJID']            // wala pa
            ];
        }

        
            $insert_response = DB::table('humanresource.overtime_request_temp')->insert($setOTData);
            DB::commit();
            log::debug($insert_response);
        } catch (\Exception $e){
            DB:: rollback();
            $insert_response = 0;
            log:: debug($insert_response);
        }

        // AFTER you INSERT OT Data from alibaba server UPDATE OT cloud data to transferred = 1
        if ($insert_response) {
            $resp = Http::get('http://portal.cylix.ph/ctiportal/public/api/get-transferredHROT');
        }

        // RETURN a response if success or failed
        if ($resp['status']) {
            return response()->json([
                'status'  => true,
                'message' => 'Transfer Successfully.'
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Transfer Failed.'
            ], 200);
        }

        // RESPONSE if nothing to transfer
        } else {
            return response()->json([
                'status'  => true,
                'message' => 'No overtime data to transfer.'
            ], 200);
        }
    }

    // TRANSFER Data from overtime_request_temp to overtime_request
    public function createOTfromOTTemp() {
        $result = DB::table('humanresource.overtime_request_temp')->where('transferred', 0)->get();
        $result = count($result) ? $result : null;

        $guid = $this->getGuid();
        $reqRef = $this->getOtRef(1); // problem paano kapag iba ng company // for the meantime lagi muna naka set sa 1 ito
    }

    public function saveNewOTDrafts(Request $request){

        DB::beginTransaction();
        try {

        // log::debug($request->);
        $draftReference = $this->getDraftOtRef($request->companyId);
        $guid = $this->getGuid();

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $mainID = DB::select("SELECT IFNULL(MAX(main_id),0) + 1 AS main FROM humanresource.`overtime_request`");

        // insert to hr.ot main table
        if(count($otData)){
        for ($i = 0; $i < count($otData); $i++) {

            $ot_date = date_create($otData[$i]['overtime_date']);
            $ot_in   = date_create($otData[$i]['ot_in']);
            $ot_out  = date_create($otData[$i]['ot_out']);

            $setOTData[] = [
                'draft_iden'        => 1,
                'draft_reference'   => $draftReference,
                'request_date'      => now(),
                'overtime_date'     => date_format($ot_date, 'Y-m-d'),
                'ot_in'             => date_format($ot_in, 'Y-m-d H:i:s'),
                'ot_out'            => date_format($ot_out, 'Y-m-d H:i:s'),
                'ot_totalhrs'       => $otData[$i]['ot_totalhrs'],
                'employee_id'       => $otData[$i]['employee_id'],
                'employee_name'     => $otData[$i]['employee_name'],
                'purpose'           => $otData[$i]['purpose'],
                'status'            => 'Draft',
                'UID'               => $request->loggedUserId,
                'fname'             => $request->loggedUserFirstName,
                'lname'             => $request->loggedUserLastName,
                'department'        => $request->loggedUserDepartment,
                'reporting_manager' => $request->reportingManagerName,
                'position'          => $request->loggedUserPosition,
                'ts'                => now(),
                'GUID'              => $guid,
                'main_id'           => $mainID[0]->main,
                'remarks'           => $otData[$i]['purpose'],
                'cust_id'           => $otData[$i]['cust_id'],
                'cust_name'         => $otData[$i]['cust_name'],
                'TITLEID'           => $request->companyId,
                'PRJID'             => $otData[$i]['PRJID'],
                'webapp'            => 1
            ];
        }
        DB::table('humanresource.overtime_request')->insert($setOTData);
        }


        $actualSign                    = new ActualSign;
        $actualSign->PROCESSID         = $mainID[0]->main;
        $actualSign->USER_GRP_IND      = 'Acknowledgement of Reporting Manager';
        $actualSign->FRM_NAME          = 'Overtime Request';
        $actualSign->FRM_CLASS         = 'frmOvertimeRequest';
        $actualSign->REMARKS           = '';
        $actualSign->STATUS            = 'Draft';
        $actualSign->TS                = now();
        $actualSign->DUEDATE           = now();
        $actualSign->ORDERS            = 0;
        $actualSign->REFERENCE         = $draftReference;
        $actualSign->PODATE            = now();
        $actualSign->DATE              = now();
        $actualSign->INITID            = $request->loggedUserId;
        $actualSign->FNAME             = $request->loggedUserFirstName;
        $actualSign->LNAME             = $request->loggedUserLastName;
        $actualSign->DEPARTMENT        = $request->loggedUserDepartment;
        $actualSign->RM_ID             = $request->reportingManagerId;
        $actualSign->REPORTING_MANAGER = $request->reportingManagerName;
        $actualSign->PROJECTID         = '0';
        $actualSign->PROJECT           = $request->loggedUserDepartment;
        $actualSign->COMPID            = $request->companyId;
        $actualSign->COMPANY           = $request->companyName;
        $actualSign->TYPE              = $request->form;
        $actualSign->CLIENTID          = '0';
        $actualSign->CLIENTNAME        = $request->companyName;
        $actualSign->Max_approverCount = '4';
        $actualSign->DoneApproving     = '0';
        $actualSign->Payee             = 'N/A';
        $actualSign->Amount            = 0;
        $actualSign->save();

        DB::commit();
        return response()->json(['message' => 'Draft saved successfully!'], 200);
    
        } catch (\Exception $e) {
            DB::rollback();
            // throw error response
            return response()->json($e, 500);
        }
    }

    public function saveOTDrafts(Request $request){
        DB::beginTransaction();
        try {

        DB::table('humanresource.overtime_request')
            ->where('main_id',$request->processId)
            ->where('status', 'Draft')
            ->where('TITLEID', $request->companyId)
            ->delete();

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);


        // insert to hr.ot main table
        if(count($otData)){
        for ($i = 0; $i < count($otData); $i++) {

            $ot_date = date_create($otData[$i]['overtime_date']);
            $ot_in   = date_create($otData[$i]['ot_in']);
            $ot_out  = date_create($otData[$i]['ot_out']);

            $setOTData[] = [
                'draft_iden'        => 1,
                'draft_reference'   => $request->referenceNumber,
                'request_date'      => now(),
                'overtime_date'     => date_format($ot_date, 'Y-m-d'),
                'ot_in'             => date_format($ot_in, 'Y-m-d H:i:s'),
                'ot_out'            => date_format($ot_out, 'Y-m-d H:i:s'),
                'ot_totalhrs'       => $otData[$i]['ot_totalhrs'],
                'employee_id'       => $otData[$i]['employee_id'],
                'employee_name'     => $otData[$i]['employee_name'],
                'purpose'           => $otData[$i]['purpose'],
                'status'            => 'Draft',
                'UID'               => $request->loggedUserId,
                'fname'             => $request->loggedUserFirstName,
                'lname'             => $request->loggedUserLastName,
                'department'        => $request->loggedUserDepartment,
                'reporting_manager' => $request->reportingManagerName,
                'position'          => $request->loggedUserPosition,
                'ts'                => now(),
                'GUID'              => $request->guid,
                'main_id'           => $request->processId,
                'remarks'           => $otData[$i]['purpose'],
                'cust_id'           => $otData[$i]['cust_id'],
                'cust_name'         => $otData[$i]['cust_name'],
                'TITLEID'           => $request->companyId,
                'PRJID'             => $otData[$i]['PRJID'],
                'webapp'            => 1
            ];
        }
        DB::table('humanresource.overtime_request')->insert($setOTData);
        }

        ActualSign::where('PROCESSID', $request->processId)
            ->where('COMPID', $request->companyId)
            ->where('FRM_NAME', 'Overtime Request')
            ->update(['RM_ID' => $request->reportingManagerId,'REPORTING_MANAGER' => $request->reportingManagerName]);

        DB::commit();
        return response()->json(['message' => 'Draft saved successfully!'], 200);
    
        } catch (\Exception $e) {
            DB::rollback();
            // throw error response
            return response()->json($e, 500);
        }
    }

    public function saveOTF(Request $request) {
        DB::beginTransaction();
        try {
            $reqRef = $this->getOtRef($request->companyId);
            $otData = $request->overtimeData;
            $otData = json_decode($otData, true);

            DB:: table('humanresource.overtime_request')
            ->where('main_id',$request->processId)
            ->where('status', 'Draft')
            ->where('TITLEID', $request->companyId)
            ->delete();

            if(count($otData)){
                // insert to hr.ot main table
                for ($i = 0; $i < count($otData); $i++) {

                    $ot_date = date_create($otData[$i]['overtime_date']);
                    $ot_in   = date_create($otData[$i]['ot_in']);
                    $ot_out  = date_create($otData[$i]['ot_out']);

                    $setOTData[] = [
                        'reference'         => $reqRef,
                        'draft_reference'   => $request->referenceNumber,
                        'request_date'      => now(),
                        'overtime_date'     => date_format($ot_date, 'Y-m-d'),
                        'ot_in'             => date_format($ot_in, 'Y-m-d H:i:s'),
                        'ot_out'            => date_format($ot_out, 'Y-m-d H:i:s'),
                        'ot_totalhrs'       => $otData[$i]['ot_totalhrs'],
                        'employee_id'       => $otData[$i]['employee_id'],
                        'employee_name'     => $otData[$i]['employee_name'],
                        'purpose'           => $otData[$i]['purpose'],
                        'status'            => 'In Progress',
                        'UID'               => $request->loggedUserId,
                        'fname'             => $request->loggedUserFirstName,
                        'lname'             => $request->loggedUserLastName,
                        'department'        => $request->loggedUserDepartment,
                        'reporting_manager' => $request->reportingManagerName,
                        'position'          => $request->loggedUserPosition,
                        'ts'                => now(),
                        'GUID'              => $request->guid,
                        'main_id'           => $request->processId,
                        'remarks'           => $otData[$i]['purpose'],
                        'cust_id'           => $otData[$i]['cust_id'],
                        'cust_name'         => $otData[$i]['cust_name'],
                        'TITLEID'           => $request->companyId,
                        'PRJID'             => $otData[$i]['PRJID'],
                        'webapp'            => 1
                    ];
                }

                DB::table('humanresource.overtime_request')->insert($setOTData);
            }

            ActualSign::where('PROCESSID', $request->processId)
                ->where('FRM_NAME', 'Overtime Request')
                ->where('COMPID', $request->companyId)
                ->where('STATUS', 'Draft')
                ->delete();

                    //Insert general.actual_sign
//         for ($x = 0; $x < 4; $x++) {
//             $array[] = array(
// 'PROCESSID'         => $request->processId,
// 'USER_GRP_IND'      => '0',
// 'FRM_NAME'          => $request->form,
// 'FRM_CLASS'         => 'frmOvertimeRequest',             //Hold
// 'REMARKS'           => '',
// 'STATUS'            => 'Not Started',
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
// 'PROJECTID'         => '0',
// 'PROJECT'           => $request->loggedUserDepartment,
// 'COMPID'            => $request->companyId,
// 'COMPANY'           => $request->companyName,
// 'TYPE'              => $request->form,
// 'CLIENTID'          => '0',
// 'CLIENTNAME'        => $request->companyName,
// 'Max_approverCount' => '4',
// 'DoneApproving'     => '0',
// 'Payee'             => 'N/A',
// 'Amount'            => 0,
//             );
//         }

//         if ($array[0]['ORDERS'] == 0) {
//             $array[0]['USER_GRP_IND'] = 'Acknowledgement of Reporting Manager';
//             $array[0]['STATUS']       = 'In Progress';
//         }

//         if ($array[1]['ORDERS'] == 1) {
//             $array[1]['USER_GRP_IND'] = 'Input of Actual Overtime (Initiator)';
//         }

//         if ($array[2]['ORDERS'] == 2) {
//             $array[2]['USER_GRP_IND'] = 'Approval of Reporting Manager';
//         }

//         if ($array[3]['ORDERS'] == 3) {
//             $array[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
//         }

        // DB:: table('general.actual_sign')->insert($array);

        $isInserted = $this->insertActualSign($request, $request->processId, 'Overtime Request', $reqRef);
        if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');

            DB:: commit();
            return response()->json(['message' => 'Your Overtime Request was successfully submitted.'], 200);
        } catch (\Exception $e) {
            DB:: rollback();
            // throw error response
            return response()->json($e, 500);
        }
    }

}
