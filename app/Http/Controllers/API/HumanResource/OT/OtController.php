<?php

namespace App\Http\Controllers\API\HumanResource\OT;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\HumanResource\OT\OtMain;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        log::debug($request);

        $guid = $this->getGuid();
        $reqRef = $this->getOtRef($request->companyId);

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        $dateRequested = date_create($request->dateRequested);

        $mainID = DB::select("SELECT IFNULL(MAX(main_id),0) + 1 AS main FROM humanresource.`overtime_request`");


        // insert to hr.ot main table
        for ($i = 0; $i < count($otData); $i++) {

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
                'PRJID' => $otData[$i]['PRJID']
            ];
        }

        DB::table('humanresource.overtime_request')->insert($setOTData);

        $this->insertAttachments($request,$mainID[0]->main,$reqRef);


        //Insert general.actual_sign
        for ($x = 0; $x < 4; $x++) {
            $array[] = array(
                'PROCESSID' => $mainID[0]->main,
                'USER_GRP_IND' => '0',
                'FRM_NAME' => $request->form,
                // 'TaskTitle'=>'',
                // 'NS'=>'',
                'FRM_CLASS' => 'frmOvertimeRequest', //Hold
                'REMARKS' => '',
                'STATUS' => 'Not Started',
                // 'UID_SIGN'=>'0',
                // 'TS'=>'',
                'DUEDATE' => now(),
                // 'SIGNDATETIME'=>'',
                'ORDERS' => $x,
                'REFERENCE' => $reqRef,
                'PODATE' => now(),
                // 'PONUM'=>'',
                'DATE' => now(),
                'INITID' => $request->loggedUserId,
                'FNAME' => $request->loggedUserFirstName,
                'LNAME' => $request->loggedUserLastName,
                // 'MI'=>'',
                'DEPARTMENT' => $request->loggedUserDepartment,
                'RM_ID' => $request->reportingManagerId,
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'PROJECTID' => '0',
                'PROJECT' => $request->loggedUserDepartment,
                'COMPID' => $request->companyId,
                'COMPANY' => $request->companyName,
                'TYPE' => $request->form,
                'CLIENTID' => '0',
                'CLIENTNAME' => $request->companyName,
                // 'VENDORID'=>'0',
                // 'VENDORNAME'=>'',
                'Max_approverCount' => '4',
                // 'GUID_GROUPS'=>'',
                'DoneApproving' => '0',
                // 'WebpageLink'=>'pc_approve.php',
                // 'ApprovedRemarks'=>'',
                'Payee' => 'N/A',
                // 'CurrentSender'=>'0',
                // 'CurrentReceiver'=>'0',
                // 'NOTIFICATIONID'=>'0',
                // 'SENDTOID'=>'0',
                // 'NRN'=>'imported',
                // 'imported_from_excel'=>'0',
                'Amount'=>0,

                // to follow
                // 'user_grp_info' => '1', // 0 = Reporting Manager, 1 = For Approval of Management, 2 = Releasing of Cash, 3 = Initiator, 4 = Acknowledgement of Accountung
                // 'orders'=>$x, //01234
                // 'status' => 'Not Started' //in-progress & not started
            );
        }

        if ($array[0]['ORDERS'] == 0) {
            $array[0]['USER_GRP_IND'] = 'Acknowledgement of Reporting Manager';
            $array[0]['STATUS'] = 'In Progress';
        }

        if ($array[1]['ORDERS'] == 1) {
            $array[1]['USER_GRP_IND'] = 'Input of Actual Overtime (Initiator)';
        }

        if ($array[2]['ORDERS'] == 2) {
            $array[2]['USER_GRP_IND'] = 'Approval of Reporting Manager';
        }

        if ($array[3]['ORDERS'] == 3) {
            $array[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
        }

        DB::table('general.actual_sign')->insert($array);
    
        
        return response()->json(['message' => 'Your Overtime Request was successfully submitted.'], 200);
        
    }

    public function getOtMain($id){
        // $otData = OtMain::where('main_id',$id)->get();

        $otData = DB::select("SELECT *,(SELECT b.`project_name` FROM general.`setup_project` b WHERE b.`project_id` = a.`PRJID` ) AS 'PRJNAME' FROM humanresource.`overtime_request` a WHERE a.`main_id` = '".$id."' AND a.`status` <> 'Removed'");

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


    
}
