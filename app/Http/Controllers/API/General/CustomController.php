<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\CAF\CafMain;
use App\Models\Accounting\PC\PcMain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\RFP\RfpMain;
use App\Models\Accounting\RFP\RfpDetail;
use App\Models\Accounting\RFP\RfpLiquidation;
use App\Models\General\ActualSign;
use App\Models\General\Attachments;
use App\Models\Accounting\RE\ReMain;
use App\Models\HumanResource\ITF\ItfMain;
use App\Models\HumanResource\LAF\LafMain;
use App\Models\HumanResource\OT\OtMain;
use Illuminate\Support\Facades\Log;

class CustomController extends ApiController
{
    public function getReportingManager($id)
    {
        $mgrs = DB::select("SELECT RMID, RMName FROM general.`systemreportingmanager` WHERE UID = $id ORDER BY RMName");
        return $mgrs;
    }

    public function getClient($prjid)
    {
        $client = DB::select("SELECT Business_Number as 'clientID', ifnull(business_fullname, '') AS 'clientName', (SELECT Main_office_id FROM general.`setup_project` WHERE `project_id` = '" . $prjid . "' LIMIT 1) as 'mainID' FROM general.`business_list` WHERE Business_Number IN (SELECT `ClientID` FROM general.`setup_project` WHERE `project_id` = '" . $prjid . "')");
        return $client;
    }

    public function getBusinessList($companyId)
    {
        $businesslist = DB::select("SELECT a.`Business_Number`, a.`business_fullname` FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = '" . $companyId . "' AND a.`Type` = 'CLIENT' ORDER BY a.`business_fullname` ASC");
        return $businesslist;
    }

    public function getEmployees($companyId)
    {
        $employees = DB::select("SELECT SysPK_Empl, Name_Empl FROM humanresource.`employees` WHERE Status_Empl LIKE 'Active%' AND CompanyID = $companyId ORDER BY Name_Empl");
        return $employees;
    }

    public function getMediumOfReport(){
        $mediumofreport = DB::select("SELECT id, item FROM general.`setup_dropdown_items` WHERE `type` = 'Medium of Report' AND `status` = 'Active' ORDER BY OrderingPref ASC;");
        return $mediumofreport;
    }

    public function getLeaveType(){
        $leavetype = DB::select("SELECT id, item FROM general.`setup_dropdown_items` WHERE `type` = 'Leave Type' AND `status` = 'Active' ORDER BY OrderingPref ASC;");
        return $leavetype;
    }


    // Workflow Modals

    // RFP withdraw Button in In-progress Workflow
    public function withdrawnByIDRemarks(Request $request)
    {
        if ($request->form === 'Request for Payment') {

            // nag edit ako sa frm_class to frm_name
            DB::update("UPDATE general.`actual_sign` AS a SET  a.`webapp` = '1', a.`STATUS` = 'Withdrawn', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->withdrawRemarks . "' 
        WHERE a.`PROCESSID` = '" . $request->reqId . "' AND a.`FRM_CLASS` = 'REQUESTFORPAYMENT';");
            DB::update("UPDATE accounting.`request_for_payment` a SET a.`STATUS` = 'Withdrawn'  WHERE a.`ID` = '" . $request->reqId . "';");

            return response()->json(['message' => 'Request has been Successfully withdrawn'], 200);
        }


        if ($request->form === 'Reimbursement Request') {

            $this->withdrawActualSign($request);
            ReMain::where('id', $request->reqId)
                ->update([
                    'STATUS' => 'Withdrawn',
                ]);

            return response()->json(['message' => 'Reimbursement Request has been Successfully withdrawn'], 200);
        }



        if ($request->form === 'Petty Cash Request') {
            $this->withdrawActualSign($request);

            PcMain::where('id', $request->reqId)
                ->update([
                    'STATUS' => 'Withdrawn',
                ]);

            return response()->json(['message' => 'Petty Cash Request has been Successfully withdrawn'], 200);
        }

        if ($request->form === 'Cash Advance Request') {
            $this->withdrawActualSign($request);

            
            CafMain::where('id', $request->reqId)
                ->update([
                    'status' => 'Withdrawn',
                ]);

            return response()->json(['message' => 'Cash Advance Request has been Successfully withdrawn'], 200);
        }






        if ($request->form === 'Overtime Request') {
            $this->withdrawActualSign($request);

            OtMain::where('main_id', $request->reqId)->where('status', '!=', 'Removed')
                ->update([
                    'status' => 'Withdrawn',
                ]);

            return response()->json(['message' => 'Overtime Request has been Successfully withdrawn'], 200);
        }


        if ($request->form === 'Itinerary Request') {
            $this->withdrawActualSign($request);
            ItfMain::find($request->reqId)->update(['status' => 'Withdrawn']);
            return response()->json(['message' => 'Itinerary Request has been Successfully withdrawn'], 200);
        }

        if ($request->form === 'Leave Request') {
            $this->withdrawActualSign($request);
            LafMain::where('main_id', $request->reqId)->update(['status' => 'Withdrawn']);
            return response()->json(['message' => 'Leave Request has been Successfully withdrawn'], 200);
        }

        if($request->frmClass === 'sales_order_frm'){
            Log::debug($request);

            $this->withdrawActualSign($request);
            DB::update("UPDATE sales_order.`sales_orders` a SET a.`Status` = 'Withdrawn'  WHERE a.`id` = '".$request->reqId."' AND a.`titleid` = '".$request->companyId."' ");
            return response()->json(['message' => 'Sales Order Request has been Successfully withdrawn'], 200);
            
        }
    }

    // change to withdrawn 
    public function withdrawActualSign($request)
    {
        ActualSign::where('PROCESSID', $request->reqId)
            ->where('FRM_NAME', $request->form)
            ->where('COMPID', $request->companyId)
            ->where('STATUS', 'In Progress')

            ->update([
                'STATUS' => 'Withdrawn',
                'SIGNDATETIME' => now(),
                'ApprovedRemarks' => $request->withdrawRemarks,
                'UID_SIGN' => $request->loggedUserId,
                'webapp' => '1'
            ]);
    }


    public function rejectedByIDRemarks(Request $request)
    {


        if ($request->form === 'Request for Payment') {
            // DB::update("UPDATE general.`actual_sign` AS a SET a.`webapp` = '1', a.`status` = 'Rejected', a.`UID_SIGN` = '" . $request->loggedUserId . "', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' 
            // WHERE  a.`PROCESSID` = '" . $request->processId . "' AND a.`STATUS` = 'In Progress' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "' ;");
            $this->rejectedRequest($request);
            DB::update("UPDATE accounting.`request_for_payment` a SET a.`STATUS` = 'Rejected'  WHERE a.`ID` = '" . $request->processId . "';");
            return response()->json(['message' => 'Payment Request has been Rejected'], 200);
        }

        if ($request->form === 'Reimbursement Request') {
            // DB::update("UPDATE general.`actual_sign` AS a SET a.`webapp` = '1', a.`status` = 'Rejected', a.`UID_SIGN` = '" . $request->loggedUserId . "', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' 
            // WHERE  a.`PROCESSID` = '" . $request->processId . "' AND a.`STATUS` = 'In Progress' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "' ;");
            $this->rejectedRequest($request);
            DB::update("UPDATE accounting.`reimbursement_request` a SET a.`STATUS` = 'Rejected'  WHERE a.`ID` = '" . $request->processId . "';");
            return response()->json(['message' => 'Reimbursement Request has been  Rejected'], 200);
        }

        if ($request->form === 'Petty Cash Request') {
            $this->rejectedRequest($request);
            DB::update("UPDATE accounting.`petty_cash_request` a SET a.`STATUS` = 'Rejected'  WHERE a.`ID` = '" . $request->processId . "'  ");
            return response()->json(['message' => 'Petty Cash Request has been Rejected'], 200);
        }

        if ($request->form === 'Cash Advance Request') {
            $this->rejectedRequest($request);
            CafMain::where('id', $request->processId)->update(['status' => 'Rejected']);
            return response()->json(['message' => 'Cash Advance Request has been Rejected'], 200);
        }




        if ($request->form === 'Overtime Request') {
            $this->rejectedRequest($request);
            OtMain::where('main_id', $request->processId)->where('status', '!=', 'Removed')
                ->update([
                    'status' => 'Rejected',
                ]);

            return response()->json(['message' => 'Overtime Request has been Rejected'], 200);
        }

        if ($request->form === 'Itinerary Request') {
            $this->rejectedRequest($request);
            ItfMain::where('id', $request->processId)
                ->update([
                    'status' => 'Rejected',
                ]);

            return response()->json(['message' => 'Itinerary Request has been Rejected'], 200);
        }

        if ($request->form === 'Leave Request') {
            $this->rejectedRequest($request);
            LafMain::where('main_id', $request->processId)
                ->update([
                    'status' => 'Rejected',
                ]);

            return response()->json(['message' => 'Leave Request has been Rejected'], 200);
        }


        if ($request->frmClass === 'sales_order_frm') {
            // Log::debug($request);

            DB::update("UPDATE sales_order.`sales_orders` a SET a.`Status` =  'Rejected' WHERE a.`id` = '".$request->processId."' AND a.`titleid` = '".$request->companyId."' ");
            DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `status` = 'Rejected', UID_SIGN = '".$request->loggedUserId."', SIGNDATETIME = NOW(), ApprovedRemarks = '" .$request->remarks. "' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'SALES_ORDER_FRM' AND `COMPID` = '".$request->companyId."'  ;");
        
            return response()->json(['message' => 'Sales Order Request has been Rejected'], 200);
            
        }

    }

    public function rejectedRequest($request)
    {
        DB::update("UPDATE general.`actual_sign` AS a SET a.`webapp` = '1', a.`status` = 'Rejected', a.`UID_SIGN` = '" . $request->loggedUserId . "', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' 
        WHERE  a.`PROCESSID` = '" . $request->processId . "' AND a.`STATUS` = 'In Progress' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "' ;");
    }

    // Approved button with remarks
    public function approvedByIDRemarks(Request $request)
    {

        if ($request->form === 'Request for Payment') {

            $forApprove = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a WHERE a.`STATUS` = 'In Progress' AND a.`PROCESSID` = $request->processId AND a.`ORDERS` = 4 AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "'), FALSE) AS forApprove");
            $forApprove = $forApprove[0]->forApprove;

            // Acknowledgement of Accounting - Approval - Done Approving
            if ($forApprove) {
                $this->doneApproving($request);

                RfpMain:: where('ID', $request->processId)
                    ->update([
                        'ISRELEASED' => 1,
                        'STATUS'     => 'Completed',
                    ]);

                return response()->json(['message' => "Done Approving has been Successfully approved"], 200);
            } else {
                $this->approveActualSIgn($request);
                return response()->json(['message' => 'Payment Request has been Successfully approved'], 200);
            }
        }

        if ($request->form === 'Reimbursement Request') {
            if ($request->isInitiator === 'true') {
                $this->doneApproving($request);
                DB::update("UPDATE accounting.`reimbursement_request` a SET a.`STATUS` = 'Completed'  WHERE a.`ID` = '" . $request->processId . "' AND a.`TITLEID` = '" . $request->companyId . "' ");
                return response()->json(['message' => 'Done! Request has been Successfully approved'], 200);
            } else {
                $this->approveActualSIgn($request);
                return response()->json(['message' => 'Reimbursement Request has been Successfully approved'], 200);
            }
        }

        if ($request->form === 'Petty Cash Request') {

            $isReleased = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a 
            WHERE a.`PROCESSID` = $request->processId
            AND a.`USER_GRP_IND` = 'For Approval of Accounting Payable' 
            AND a.`FRM_NAME` = 'Petty Cash Request'
            AND a.`COMPID` = $request->companyId
            AND a.`STATUS` = 'In Progress'), FALSE) AS isReleased");

            $isApproving = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a 
            WHERE a.`PROCESSID` = $request->processId
            AND a.`USER_GRP_IND` = 'Acknowledgement of Accounting' 
            AND a.`FRM_NAME` = 'Petty Cash Request'
            AND a.`COMPID` = $request->companyId
            AND a.`STATUS` = 'In Progress'), FALSE) AS isApproving");


            if (!empty($isApproving[0]->isApproving)) {
                $this->doneApproving($request);
                PcMain::where('id', $request->processId)
                    ->update([
                        'STATUS' => 'Completed',
                    ]);
                return response()->json(['message' => 'Done! Petty Cash Request has been Successfully approved'], 200);
            
            // if liquidation is in progress
            } elseif ($request->isLiquidation === 'true') {
                $data = DB::select("SELECT a.`DEPARTMENT` FROM accounting.`petty_cash_request` a WHERE a.`id` = $request->processId");
                $department = $data[0]->DEPARTMENT;
            DB::beginTransaction();
            try {

                $loggedUserId = DB::table('accounting.petty_cash_request as a')->select('a.UID as loggedUserId','a.GUID as guid')->where('id',$request->processId)->get();
                // $userId = $loggedUserId[0]->loggedUserId;
                $guid = $loggedUserId[0]->guid;


                // $request->merge([
                //     'loggedUserId' => $userId,
                // ]);
                
                $this->deletePcExpense($request);
                $this->deletePcTranspo($request);

                $this->insertPcExpense($request, $department, $guid);
                $this->insertPcTranspo($request, $department, $guid);
                
                $this->removeAttachments($request);
                $this->insertAttachments($request, $request->processId, $request->referenceNumber);
                $this->approveActualSIgn($request);

                DB::commit();
                return response()->json(['message' => 'Petty Cash Liquidation has been Successfully added'], 200);

            } catch (\Exception $e) {
                DB::rollback();
                $success = false;
                Log::debug($e);
                return response()->json(['message' => 'Failed to liquidate. Try again later!'], 202);
            }


            } else {

                if (!empty($isReleased[0]->isReleased)) {
                    DB::update("UPDATE accounting.`petty_cash_request` a SET a.`ISRELEASED` = '1', a.`RELEASEDCASH` = '1' WHERE a.`id` = '" . $request->processId . "' ");
                }

                $this->approveActualSIgn($request);
                return response()->json(['message' => 'Petty Cash Request has been Successfully approved'], 200);
            }
        }

        if ($request->form === 'Cash Advance Request') {

            DB::beginTransaction();
            try{  
                // if this is the first approver then run and insert approved amount
                if (filter_var($request->isFirstApprover, FILTER_VALIDATE_BOOLEAN)) {
                    // log::debug($request);
                    $this->approveActualSIgn($request);
                    CafMain::where('id', $request->processId)
                        ->update([
                            'approved_amount' => floatval(str_replace(',', '', $request->approvedAmount)),
                        ]);
                } elseif (filter_var($request->isForAcknowledgement, FILTER_VALIDATE_BOOLEAN)) {
                    log::debug($request);
                    CafMain::where('id', $request->processId)
                        ->update([
                            'status' => 'Completed',
                            'IsReleased' => 1,
                        ]);
                    $this->doneApproving($request);
                    
                } else {
                    $this->approveActualSIgn($request);
                }
                

                DB::commit();
                return response()->json(['message' => 'Cash Advance Request has been Successfully approved'], 200);

        
            }catch(\Exception $e){
                DB::rollback();
            
                // throw error response
                return response()->json($e, 500);
            }
        }
        


        if ($request->form === 'Overtime Request') {
            $isCompleted = DB::select("SELECT IFNULL((SELECT a.`ID` FROM general.`actual_sign` a WHERE a.`PROCESSID` = '" . $request->processId . "' AND a.`ORDERS` = 3 AND a.`COMPID` = '" . $request->companyId . "' AND a.`STATUS` = 'In Progress' AND a.`FRM_NAME` = '" . $request->form . "'), FALSE) AS tableCheck;");

            if (!empty($isCompleted[0]->tableCheck)) {
                $this->doneApproving($request);
                OtMain::where('main_id', $request->processId)
                    ->update([
                        'status' => 'Completed',
                    ]);

                return response()->json(['message' => 'Done! Overtime Request has been Successfully approved'], 200);
            } else {
                log::debug($request);
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
                }

                $this->approveActualSIgn($request);
                return response()->json(['message' => 'Overtime Request has been Successfully approved'], 200);
            }
        }

        if ($request->form === 'Itinerary Request') {

            $isCompleted = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a 
            WHERE a.`PROCESSID` = $request->processId 
            AND a.`USER_GRP_IND` = 'Acknowledgement of Human Resource' 
            AND a.`FRM_NAME` = 'Itinerary Request'
            AND a.`COMPID` = $request->companyId
            AND a.`STATUS` = 'Completed'), FALSE) AS tableCheck");

            // Done Approving
            if (!empty($isCompleted[0]->tableCheck)) {
                $this->doneApproving($request);
                ItfMain::where('id', $request->processId)
                    ->update([
                        'status' => 'Completed',
                    ]);

                return response()->json(['message' => 'Done! Itinerary Request has been Successfully approved'], 200);
            } else {
                $this->approveActualSIgn($request);
                // return response()->json(['message' => 'Else'], 200);
                return response()->json(['message' => 'Itinerary Request has been Successfully approved'], 200);
            }
        }


        if ($request->form === 'Leave Request') {
            Log::debug('1');
            $isCompleted = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`actual_sign` a 
            WHERE a.`PROCESSID` = '".$request->processId."'
            AND a.`USER_GRP_IND` = 'Acknowledgement of Reporting Manager' 
            AND a.`FRM_NAME` = 'Leave Request'
            AND a.`COMPID` = '".$request->companyId."'
            AND a.`STATUS` = 'Completed'), FALSE) AS tableCheck");

            Log::debug($isCompleted[0]->tableCheck);
            
            if (!empty($isCompleted[0]->tableCheck)) {

            Log::debug('1.5 inside if');

                $this->doneApproving($request);
                LafMain::where('main_id', $request->processId)
                    ->update([
                        'status' => 'Completed',
                    ]);

                return response()->json(['message' => 'Done! Leave Request has been Successfully approved'], 200);

            } else {
                Log::debug('2 else');
            
                $this->approveActualSIgn($request);
                return response()->json(['message' => 'Leave Request has been Successfully approved'], 200);
            }
            
        }

        if($request->frmClass === 'sales_order_frm'){
            $isCoordinatorRequired = $request->isCoordinatorRequired;
            $isCoordinatorRequired = json_decode($isCoordinatorRequired);

            $isDmoPocComplete = $request->isDmoPocComplete;
            $isDmoPocComplete = json_decode($isDmoPocComplete);

            $isSiConfirmation = $request->isSiConfirmation;
            $isSiConfirmation = json_decode($isSiConfirmation);


            if($isCoordinatorRequired){
                // 1
                log::debug('1');
                DB::table('sales_order.projectcoordinator')->insert([
                    'CoordID' => $request->coordinatorID,
                    'CoordinatorName' =>$request->coordinatorName,
                    'SOID' => $request->processId,
                    'SOTYPE' => 'Sales Order - Project'
                ]);
                DB::update("UPDATE general.`setup_project` a SET a.`Coordinator` = '".$request->coordinatorID."' WHERE a.`SOID` = '".$request->processId."' AND a.`title_id` = '".$request->companyId."' ");
                $this->approveSofActualSign($request);
                return response()->json(['message' => 'Sales Order Request has been Successfully approved'], 200);


            } else if($isSiConfirmation) {
                log::debug('2');
                $salesInvoiceReleased = json_decode($request->salesInvoiceReleased);
                $salesInvoiceReleased = intval($salesInvoiceReleased);

                $dateOfInvoice = date_create($request->dateOfInvoice);
                $dateOfInvoice = date_format($dateOfInvoice, 'Y-m-d');
                // log::debug(gettype($dateOfInvoice));
                
                DB::update("UPDATE general.`setup_project` a SET a.`ProjectStatus` = 'Closed' WHERE a.`title_id` = '".$request->companyId."' AND a.`status` LIKE 'Active%' AND a.`SOID` = '".$request->processId."'");
                DB::update("UPDATE sales_order.`sales_orders` a 
                SET
                    a.`Status` = 'Completed',
                    a.`InvoiceNumber` = '".$request->invoiceNumber."',
                    a.`InvoiceDate` = '".$dateOfInvoice."',
                    a.`IsInvoiceReleased` = '".$salesInvoiceReleased."'
                WHERE a.`titleid` = '".$request->companyId."' 
                    AND a.`id` = '".$request->processId."'
                ");
                DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `status` = 'Completed', UID_SIGN = '".$request->loggedUserId."', SIGNDATETIME = NOW(), ApprovedRemarks = '" .$request->remarks. "', `DoneApproving` = '1' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'SALES_ORDER_FRM' AND `COMPID` = '".$request->companyId."'  ;");
                
                return response()->json(['message' => 'Done! Sales Order Request has been Successfully approved'], 200);

            
            } else if($isDmoPocComplete) {
                // 3
                log::debug('3');

                DB::update("UPDATE general.`setup_project` a SET a.`ProjectStatus` = 'Closed' WHERE a.`title_id` = '".$request->companyId."' AND a.`status` LIKE 'Active%' AND a.`SOID` = '".$request->processId."'");
                DB::update("UPDATE sales_order.`sales_orders` a SET a.`Status` = 'Completed',a.`IsInvoiceReleased` = '1' WHERE a.`titleid` = '".$request->companyId."' AND a.`id` = '".$request->processId."'");
                DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `status` = 'Completed', UID_SIGN = '".$request->loggedUserId."', SIGNDATETIME = NOW(), ApprovedRemarks = '" .$request->remarks. "', `DoneApproving` = '1' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'SALES_ORDER_FRM' AND `COMPID` = '".$request->companyId."'  ;");
                return response()->json(['message' => 'Done! Sales Order Request has been Successfully approved'], 200);
            } else {
                // 4
                log::debug('4');
                if(filter_var($request->isAccountingAcknowledgement, FILTER_VALIDATE_BOOLEAN)){
                    DB::update("UPDATE sales_order.`sales_orders` a SET a.`ForwardProcess` = 1 WHERE a.`titleid` = '".$request->companyId."' AND a.`id` = '".$request->processId."'");
                }

                $this->approveSofActualSign($request);
                return response()->json(['message' => 'Sales Order Request has been Successfully approved'], 200);
            }


            // approveSofActualSign
            


     
            
       
            // log::debug(json_decode($request->isCoordinatorRequired));
            // approveSofActualSign
        }
    }

    public function approveSofActualSign($request){
        DB::update("UPDATE general.`actual_sign` SET  `webapp` = '1', `status` = 'Completed', UID_SIGN = '".$request->loggedUserId."', SIGNDATETIME = NOW(), ApprovedRemarks = '" .$request->remarks. "' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'SALES_ORDER_FRM' AND `COMPID` = '".$request->companyId."'  ;");
        DB::update("UPDATE general.`actual_sign` SET `status` = 'In Progress' WHERE `status` = 'Not Started' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'SALES_ORDER_FRM' AND `COMPID` = '".$request->companyId."' LIMIT 1;");
    }

    public function addPCExpenseAndTranpo($request)
    {
        $xdArray = $request->expenseType_Data;
        $xdArray = json_decode($xdArray, true);
        $xd = count($xdArray);


        $tdArray = $request->transpoSetup_Data;
        $tdArray = json_decode($tdArray, true);
        $td = count($tdArray);

        if ($td > 0 || $xd > 0) {
            $this->removeAttachments($request);
            $this->addAttachments($request);


            if ($xd > 0) {
                for ($i = 0; $i < count($xdArray); $i++) {
                    $setXDArray[] = [
                        'PCID' => $request->processId,
                        'payee_id' => '0',
                        'PAYEE' => $request->payeeName,
                        'CLIENT_NAME' => $xdArray[$i]['CLIENT_NAME'],
                        'TITLEID' => $request->companyId,
                        'PRJID' => '0',
                        'PROJECT' => '',
                        'DESCRIPTION' => $xdArray[$i]['DESCRIPTION'],
                        'AMOUNT' => $xdArray[$i]['AMOUNT'],
                        'GUID' => $request->guid,
                        'TS' => now(),
                        'MAINID' => '1',
                        'STATUS' => 'ACTIVE',
                        'CLIENT_ID' => $xdArray[$i]['CLIENT_ID'],
                        'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                        'DEPT' => '',
                        'RELEASEDCASH' => '0',
                        'date_' => date_create($xdArray[$i]['date_']),
                        'ISLIQUIDATED' => '0'
                    ];
                }
                DB::table('accounting.petty_cash_expense_details')->insert($setXDArray);
            }

            if ($td > 0) {
                for ($i = 0; $i < count($tdArray); $i++) {
                    $setTDArray[] = [

                        'PCID' => $request->processId,
                        'PRJID' => '0',
                        'payee_id' => '0',
                        'PAYEE' => $request->payeeName,
                        'CLIENT_NAME' => $tdArray[$i]['CLIENT_NAME'],
                        'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                        'DESTINATION_TO' => $tdArray[$i]['DESTINATION_TO'],
                        'DESCRIPTION' => $tdArray[$i]['DESCRIPTION'],
                        'AMT_SPENT' => $tdArray[$i]['AMT_SPENT'],
                        'TITLEID' => $request->companyId,
                        'MOT' => $tdArray[$i]['MOT'],
                        'PROJECT' => '',
                        'GUID' => $request->guid,
                        'TS' => now(),
                        'MAINID' => '1',
                        'STATUS' => 'ACTIVE',
                        'CLIENT_ID' => $tdArray[$i]['CLIENT_ID'],
                        'DEPT' => $request->loggedUserDepartment,
                        'RELEASEDCASH' => '0',
                        'date_' => date_create($tdArray[$i]['date_']),
                        'ISLIQUIDATED' => '0'
                    ];
                }
                DB::table('accounting.petty_cash_request_details')->insert($setTDArray);
            }
        } else {
            return response()->json(['message' => 'Request Failed, Please complete required records!'], 202);
        }
    }

    public function doneApproving($request)
    {
        DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `DoneApproving` = '1', `status` = 'Completed', UID_SIGN = '" . $request->loggedUserId . "', SIGNDATETIME = NOW(), ApprovedRemarks = '" . $request->remarks . "' WHERE `status` = 'In Progress' AND PROCESSID = '" . $request->processId . "' AND `FRM_NAME` = '" . $request->form . "' AND `COMPID` = '" . $request->companyId . "' ;");
    }


    // status turn to completed then next will be from not started to in progress
    public function approveActualSIgn($request)
    {
        DB::update("UPDATE general.`actual_sign` a SET a.`webapp` ='1', a.`status` = 'Completed', a.`UID_SIGN` = '" . $request->loggedUserId . "', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' WHERE a.`status` = 'In Progress' AND a.`PROCESSID` = '" . $request->processId . "' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "' ;");
        DB::update("UPDATE general.`actual_sign` a SET a.`status` = 'In Progress' WHERE a.`status` = 'Not Started' AND a.`PROCESSID` = '" . $request->processId . "' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`COMPID` = '" . $request->companyId . "' LIMIT 1;");
    }


    public function expenseType()
    {
        $expenseType = DB::select("SELECT type FROM accounting.`expense_type_setup`");
        return response()->json([$expenseType], 200);
    }

    public function currencyType()
    {
        $currencyType = DB::select("SELECT CurrencyName as currencyName FROM accounting.`currencysetup`");
        return response()->json([$currencyType], 200);
    }

    public function transpoSetup()
    {
        $transpoSetup = DB::select("SELECT MODE FROM accounting.`transpo_setup`");
        return response()->json([$transpoSetup], 200);
    }






    // populate recipient
    public function getRecipient($processId, $loggedUserId, $companyId, $formName)
    {
        $recipients = DB::select("SELECT a.uid,(SELECT UserFull_name FROM general.`users` usr WHERE usr.id = a.uid) AS 'name'
        FROM
        (SELECT initid AS 'uid' FROM general.`actual_sign` WHERE processid = $processId AND `FRM_NAME` = '" . $formName . "' AND `COMPID` = '" . $companyId . "' AND initid <> '" . $loggedUserId . "'
        UNION ALL
        SELECT UID_SIGN AS 'uid'  FROM general.`actual_sign` WHERE processid = $processId AND `FRM_NAME` = '" . $formName . "' AND `COMPID` = '" . $companyId . "' AND `status` = 'Completed' AND uid_sign <> '" . $loggedUserId . "')
        a GROUP BY uid;");


        return response()->json($recipients);
    }

    public function sendClarity(Request $request)
    {

        if ($request->form === 'Request for Payment') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            DB::update("UPDATE accounting.`request_for_payment` a SET a.`STATUS` = 'For Clarification'  WHERE a.`ID` = '" . $request->processId . "';");
            return response()->json(['message' => 'Payment Request is now for clarification'], 200);
        }
        if ($request->form === 'Reimbursement Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            DB::update("UPDATE accounting.`reimbursement_request` a SET a.`STATUS` = 'For Clarification'  WHERE a.`ID` = '" . $request->processId . "';");
            return response()->json(['message' => 'Reimbursement Request is now for clarification'], 200);
        }

        if ($request->form === 'Petty Cash Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            PcMain::where('id', $request->processId)->update(['STATUS' => 'For Clarification']);
            return response()->json(['message' => 'Petty Cash Request is now for clarification'], 200);
        }

        if ($request->form === 'Cash Advance Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            CafMain::where('id', $request->processId)->update(['status' => 'For Clarification']);
            return response()->json(['message' => 'Cash Advance Request is now for clarification'], 200);
        }

        if ($request->form === 'Overtime Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            OtMain::where('main_id', $request->processId)->where('status', '!=', 'Removed')->update(['status' => 'For Clarification']);
            return response()->json(['message' => 'Overtime Request is now for clarification'], 200);
        }

        if ($request->form === 'Itinerary Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            ItfMain::where('id', $request->processId)->update(['status' => 'For Clarification']);
            return response()->json(['message' => 'Itinerary Request is now for clarification'], 200);
        }

        if ($request->form === 'Leave Request') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            LafMain::where('main_id', $request->processId)->update(['status' => 'For Clarification']);
            return response()->json(['message' => 'Leave Request is now for clarification'], 200);
        }

        if ($request->frmClass === 'sales_order_frm') {
            $notificationIdClarity = $this->addNotification($request);
            $this->clarifyActualSign($request, $notificationIdClarity);
            DB::update("UPDATE sales_order.`sales_orders` a SET a.`Status` =  'For Clarification' WHERE a.`id` = '".$request->processId."' AND a.`titleid` = '".$request->companyId."' ");           
            return response()->json(['message' => 'Sales Order Request is now for clarification'], 200);
        }


    }

    // use to send a notification when clarity in - general.notification
    public function addNotification($request)
    {
        $notificationIdClarity = DB::table('general.notifications')->insertGetId([
            'ParentID' => '0',
            'levels' => '0',
            'FRM_NAME' => $request->form, // form name
            'PROCESSID' => $request->processId, // processid
            'SENDERID' => $request->loggedUserId, // loggedUserId
            'RECEIVERID' => $request->recipientId, // recipientId
            'MESSAGE' => $request->remarks, // remarks
            'TS' => NOW(),
            'SETTLED' => 'NO',
            'ACTUALID' => $request->inprogressId, // inprogressid
            'SENDTOACTUALID' => '0',
            'UserFullName' => $request->loggedUserFullname,

        ]);
        return $notificationIdClarity;
    }

    public function clarifyActualSign($request, $notificationIdClarity)
    {
        DB::update("UPDATE general.`actual_sign` a SET a.`webapp` = '1', a.`STATUS` = 'For Clarification', a.`CurrentSender` = '" . $request->loggedUserId . "', a.`CurrentReceiver` = '" . $request->recipientId . "' ,
        a.`NOTIFICATIONID` = '" . $notificationIdClarity . "', a.`UID_SIGN` = '" . $request->loggedUserId . "',a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' WHERE
        a.`PROCESSID` = '" . $request->processId . "' AND a.`COMPID` = '" . $request->companyId . "' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`STATUS` = 'In Progress'
        ");
    }



    // Get actual sign in progress id
    public function getInprogressId($id, $companyId, $formName)
    {
        $inpId = DB::select("SELECT IFNULL((SELECT ID AS inpId FROM general.`actual_sign` a 
        WHERE a.`PROCESSID` = $id AND a.`FRM_NAME` = '" . $formName . "' 
        AND a.`COMPID` = '" . $companyId . "' AND a.`STATUS` = 'In progress'), FALSE) AS inpId;");
        return response()->json($inpId);
    }


    // Reply Clarification

    // Reply Button in Clarification - Initiator - Editable
    public function clarifyReplyBtnRemarks(Request $request)
    {

        $notif = DB::select("SELECT * FROM general.`notifications` a WHERE a.`PROCESSID` = '" . $request->processId . "' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`SETTLED` = 'NO' ORDER BY a.`ID` DESC ");

        if ($notif == True) {
            $nParentId = $notif[0]->ID;
            $nReceiverId = $notif[0]->SENDERID;
            $nActualId = $notif[0]->ACTUALID;


            if ($request->class === 'RFP') {
                // RFP
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                // Update RFP main to inprogress
                $this->replyRfpMain($request);
                // Update RFP Detail
                $this->updateRfpDetails($request);
                // delete existing and add new array data
                $this->updateLiquidation($request);
                // update Status
                $this->updateStatus($request);
                // remove attachemnts
                $this->removeAttachments($request);
                // add attachments
                $this->addAttachments($request);
                // update actual sign data
                $this->updateActualSign($request);
                // .RFP
            }

            if ($request->class === 'RE') {
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                // delete existing expense data/ transpo data and insert new data
                $this->updateRETables($request);
                // update re main data
                $this->updateReMain($request);
                // update actual sign data
                $this->updateActualSign($request);
                // update Status
                $this->updateStatus($request);
                // remove attachemnts
                $this->removeAttachments($request);
                // add attachments
                $this->addAttachments($request);
            }


            if ($request->class === 'PC') {
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                // update re main data
                $this->updatePcMain($request);
                // update actual sign data
                $this->updateActualSign($request);
                // update Status
                $this->updateStatus($request);
                // remove attachemnts
                $this->removeAttachments($request);
                // add attachments
                $this->addAttachments($request);

                $loggedUserId = DB::table('accounting.petty_cash_request as a')->select('a.UID as loggedUserId','a.GUID as guid')->where('id',$request->processId)->get();
                $userId = $loggedUserId[0]->loggedUserId;
                $guid = $loggedUserId[0]->guid;


                // update the initid by requestor
                DB::table('general.attachments as a')->where('a.REQID', $request->processId)->where('a.formName', $request->form)->update(['a.INITID' => $userId]);
      

                $data = DB::select("SELECT a.`DEPARTMENT` FROM accounting.`petty_cash_request` a WHERE a.`id` = $request->processId");
                $department = $data[0]->DEPARTMENT;

                $this->deletePcExpense($request);
                $this->deletePcTranspo($request);

                $this->insertPcExpense($request, $department, $guid);
                $this->insertPcTranspo($request, $department, $guid);

            }



            if ($request->class === 'CAF') {
                
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                $this->updateCafMain($request);

                // Update Actual Sign
                ActualSign::where('PROCESSID', $request->processId)
                ->where('FRM_NAME', $request->form)
                ->where('COMPID', $request->companyId)
                ->update([
                    'PODATE' => date_create($request->dateNeeded),
                    'DATE' => date_create($request->dateNeeded),
                    'REMARKS' => $request->purpose,
                    'DUEDATE' => date_create($request->dateNeeded),
                    'RM_ID' => $request->reportingManagerId,
                    'REPORTING_MANAGER' => $request->reportingManagerName,
                    'Amount' => floatval(str_replace(',', '', $request->requestedAmount))
                ]);

                $this->updateStatus($request);


            }



            if ($request->class === 'OT') {
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);


                // if the user is the initiator delete and save new ot data
                if ($request->isInitiator === 'true') {
                    // delete ot main existing data
                    $this->deleteOtMain($request);
                    // saveOT Data
                    $this->saveOtMain($request);
                    // update actual sign data
                    $this->updateActualSign($request);

                    //  if not initiator then just change the status to in progress
                } else {
                    OtMain::where('main_id', $request->processId)
                        ->where('TITLEID', $request->companyId)
                        ->update([
                            'status' => 'In Progress',
                        ]);
                }
                // update Status
                $this->updateStatus($request);
            }

            if ($request->class === 'ITF') {
                // Insert data from general notifications
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                $this->deleteItfDetails($request);
                $this->insertItfDetails($request);

                ItfMain::where('id', $request->processId)
                    ->where('TITLEID', $request->companyId)
                    ->update([
                        'status' => 'In Progress',
                        'reporting_manager' => $request->reportingManagerName,
                    ]);

                $this->updateActualSign($request);
                $this->updateStatus($request);
            }

            if ($request->class === 'LAF') {
                

                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                
                if ($request->isInitiator === 'true') {
                    $this->deleteLafMain($request);
                    $this->insertLafMain($request, $request->processId, $request->referenceNumber, $request->guid);
                    $this->updateActualSign($request);
                }
                $this->updateStatus($request);
                LafMain::where('main_id', $request->processId)
                ->where('TITLEID', $request->companyId)
                ->update([
                    'status' => 'In Progress',
                    'reporting_manager' => $request->reportingManagerName,
                ]);

            return response()->json(['message' => 'Leave Request is now back to In Progress'], 200);

            }

            if ($request->frmClass === 'sales_order_frm') {

                // start transaction
                DB::beginTransaction();
                try{  
                // start transaction
                
                // check if the one who reply was the initiator
                if(filter_var($request->isInitiator, FILTER_VALIDATE_BOOLEAN)) {

                    // if the request is poc or dmo date shall be null
                    if ($request->class === 'POC' || $request->class === 'DMO') {
                        $projectStart = NULL;
                        $projectEnd = NULL;
                        $projectDuration = 0;
                        
                        $currency = null;

                    } else {
                        // convert string date to legit date
                        $projectStart = $this->dateFormatter($request->projectStart);
                        $projectEnd = $this->dateFormatter($request->projectEnd);
                        

                        // get project duration
                        $projectDuration = $this->dateDifference($projectStart, $projectEnd);
                        $currency = $request->currency;

                    }

                    $poDate = $this->dateFormatter($request->poDate);


                    // condition if down payment percentage
                    if(filter_var($request->downpaymentrequired, FILTER_VALIDATE_BOOLEAN)){
                        $downPaymentPercentage = $request->downPaymentPercentage;
                    } else {
                        $downPaymentPercentage = 0;
                    }
                
                    // validate a boolean even if its string
                    if(filter_var($request->invoicerequired, FILTER_VALIDATE_BOOLEAN)){
                        $invoiceDateNeeded = date_create($request->invoiceDateNeeded);
                        $invoiceDateNeeded = date_format($invoiceDateNeeded, 'Y-m-d');
                    } else {
                        $invoiceDateNeeded = null;
                    }

                $projectCost = $this->amountFormatter($request->projectCost);

                // default value that is added to the $request is used to set default value in actual sign
                $request->request->add(['reportingManagerId' => '0']);
                $request->request->add(['reportingManagerName' => 'Chua, Konrad A.']);
                $request->request->add(['payeeName' => 'N/A']);
                $request->request->add(['purpose' => $request->scopeOfWork]);
                $request->request->add(['amount' => $request->projectCost]);


                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);

                DB::table('general.setup_project')
                ->where('SOID', $request->processId)
                ->update([
                'project_name' => $request->projectName,
                'project_shorttext' => $request->projectShortText,
                'project_location' => $request->deliveryAddress,
                'project_remarks' => $request->scopeOfWork,
                'project_no' => $request->projectCode,
                'project_amount' => $projectCost,
                'project_duration' => $projectDuration,
                'project_effectivity' => $projectStart,
                'project_expiry' => $projectEnd,
                'ClientID' => $request->clientId,
                'ProjectStatus' => 'On-Going',
                'last_edit_datetime' => now(),
                ]);

                // done
                

                DB::table('sales_order.sales_orders')
                ->where('id', $request->processId)
                ->update([
                    'pcode' =>  $request->projectCode,
                    'project' =>    $request->projectName,
                    'clientID' =>   $request->clientId,
                    'client' => $request->clientName,
                    'Contactid' =>  $request->contactPerson,
                    'Contact' =>    $request->contactPersonName,
                    'ContactNum' => $request->contactNumber,
                    'podate' => $poDate,
                    'poNum' =>  $request->poNumber,
                    'DeliveryAddress' =>    $request->deliveryAddress,
                    'BillTo' => $request->billingAddress,
                    'currency' =>   $currency,
                    'amount' => $projectCost,
                    'remarks' =>    $request->scopeOfWork,
                    'Remarks2' =>   $request->accountingRemarks,
                    'DateAndTimeNeeded' =>  $projectEnd,
                    'Terms' =>  $request->paymentTerms,
                    'Status' => 'In Progress',  
                    'DeadLineDate' =>   $projectEnd,
                    'IsInvoiceRequired' =>  filter_var($request->invoicerequired, FILTER_VALIDATE_BOOLEAN),
                    'invDate' =>    $invoiceDateNeeded,
                    'dp_required' =>    filter_var($request->downpaymentrequired, FILTER_VALIDATE_BOOLEAN),
                    'dp_percentage' =>  $downPaymentPercentage,
                    'project_shorttext' =>  $request->projectShortText,
                    'warranty' =>   $request->warranty,
                ]);

                // if coordinator is required
                if(filter_var($request->isCoordinatorRequired, FILTER_VALIDATE_BOOLEAN)) {
                    // delete old coordinator
                    DB::table('sales_order.projectcoordinator')->where('SOID', $request->processId)->delete();
                    
                    // insert new coordinator
                    DB::table('sales_order.projectcoordinator')->insert([
                        'CoordID' => $request->coordinatorId,
                        'CoordinatorName' =>$request->coordinatorName,
                        'SOID' => $request->processId,
                        'SOTYPE' => 'Sales Order - Project'
                    ]);

                    DB::update("UPDATE general.`setup_project` a SET a.`Coordinator` = '".$request->coordinatorId."' WHERE a.`SOID` = '".$request->processId."' AND a.`title_id` = '".$request->companyId."' ");
                    
                }


                // delete systems
                DB::table('sales_order.sales_order_system')->where('soid', $request->processId)->delete();
                
                $systemnames =json_decode($request->systemname,true);
                // iterate the request system name
                foreach($systemnames as $systemname) {
                    $systemnameArray[] = [
                        'soid' => $request->processId,
                        'systemType'=> $systemname['type_name'],
                        'sysID' => $systemname['sysID'],
                        'imported_from_excel' => '0'
                    ];
                }
                // insert iterated array to sales_order_system
                DB::table('sales_order.sales_order_system')->insert($systemnameArray);
        
                // delete system docs
                DB::table('sales_order.sales_order_docs')->where('SOID', $request->processId)->delete();

                // decode a parsed document name
                $documentnames =json_decode($request->documentname,true);
                // iterate the request document name
                foreach($documentnames as $documentname) {
                    $documentnameArray[] = [
                        'SOID' => $request->processId,
                        'DocID'=> $documentname['DocID'],
                        'DocName' => $documentname['DocumentName'],
                        'imported_from_excel' => '0'
                    ];
                }
                // insert iterated array to sales_order_docs
                DB::table('sales_order.sales_order_docs')->insert($documentnameArray);

                // update actual sign
                $this->updateActualSign($request);
                // remove attachemnts
                $this->removeAttachments($request);
                // add attachments
                $this->addAttachments($request);
                // update Status of Actual sign turn to inprogress
                $this->updateStatus($request);

                DB::commit();
                return response()->json(['message' => 'Sales Order Request is now back to In Progress'], 200);

                } else {

                Log::debug("Not initiator");
                
                DB::table('sales_order.sales_orders')
                ->where('id',$request->processId)
                ->update(['Status' => 'In Progress']);

                // update insert
                $this->insertNotification($request, $nParentId, $nReceiverId, $nActualId);
                // update Status of Actual sign turn to inprogress
                $this->updateStatus($request);


               DB::commit();
               return response()->json(['message' => 'Sales Order Request is now back to In Progress'], 200);

                }







                // Catch
            }catch(\Exception $e){
                DB::rollback();
                Log::debug($e);
            
                // throw error response
                return response()->json($e, 500);
            }
                // end catch
            }
            // end reply sof


            return response()->json(['message' => 'Request is now back to In Progress'], 200);



      
        } else {
            return response()->json(['message' => 'Please inform the Administrator and Try again later'], 202);
        }
    }


    public function deleteOtMain($request)
    {
        $otMain = OtMain::where('main_id', $request->processId)->where('status', 'For Clarification');
        $otMain->delete();
    }

    public function saveOtMain($request)
    {

        $otData = $request->overtimeData;
        $otData = json_decode($otData, true);

        // insert to hr.ot main table
        for ($i = 0; $i < count($otData); $i++) {

            $ot_date = date_create($otData[$i]['overtime_date']);
            $ot_in = date_create($otData[$i]['ot_in']);
            $ot_out = date_create($otData[$i]['ot_out']);

            $ot_in_actual = null;
            $ot_out_actual = null;

            $ot_totalhrs_actual = null;

            if ($request->isActual === 'true') {
                $ot_in_actual = date_create($otData[$i]['ot_in_actual']);
                $ot_out_actual = date_create($otData[$i]['ot_out_actual']);

                $ot_in_actual = date_format($ot_in_actual, 'Y-m-d H:i:s');
                $ot_out_actual = date_format($ot_out_actual, 'Y-m-d H:i:s');

                $ot_totalhrs_actual = $otData[$i]['ot_totalhrs_actual'];
            }

            $setOTData[] = [
                'reference' => $request->referenceNumber,
                'request_date' => $request->requestedDate,
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
                'GUID' => $request->guid,
                // 'comments' => , 
                'ot_in_actual' => $ot_in_actual,
                'ot_out_actual' => $ot_out_actual,
                'ot_totalhrs_actual' => $ot_totalhrs_actual,
                'main_id' => $request->processId,
                'remarks' => $otData[$i]['purpose'],
                'cust_id' => $otData[$i]['cust_id'],
                'cust_name' => $otData[$i]['cust_name'],
                'TITLEID' => $request->companyId,
                'PRJID' => $otData[$i]['PRJID']
            ];
        }

        DB::table('humanresource.overtime_request')->insert($setOTData);
    }


    


    // update records that belongs to the request not to the user
    public function updatePcMain($request)
    {
        PcMain::where('id', $request->processId)
            ->update([
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'REQUESTED_AMT' => floatval(str_replace(',', '', $request->amount)),
                'DEADLINE' => date_create($request->dateNeeded),
                'DESCRIPTION' => $request->purpose,
                'STATUS' => 'In Progress',
                'PROJECT' => $request->projectName,
                'PAYEE' => $request->payeeName,
                'PRJID' => $request->projectId,
                'CLIENT_NAME' => $request->clientName,
                'CLIENT_ID' => $request->clientId,
                'TITLEID' => $request->companyId,
                'TS' => now(),
            ]);
    }

    public function updateCafMain($request)
    {
        CafMain::where('id', $request->processId)
            ->update([
                'date_needed'        => $request->dateNeeded,
                'requested_amount'   => floatval(str_replace(',', '', $request->requestedAmount)),
                'date_from'          => $request->payableDateFrom,
                'date_to'            => $request->payableDateTo,
                'employee_id'        => $request->employeeId,
                'employee_name'      => $request->employeeName,
                'installment_amount' => floatval(str_replace(',', '', $request->installmentAmount)),
                'purpose'            => $request->purpose,
                'status'             => 'In Progress',
                'reporting_manager'  => $request->reportingManagerName,
            ]);
    }



    public function updateRETables($request)
    {
        $xdArray = $request->expenseType_Data;
        $xdArray = json_decode($xdArray, true);
        $xd = count($xdArray);

        $tdArray = $request->transpoSetup_Data;
        $tdArray = json_decode($tdArray, true);
        $td = count($tdArray);

        $department = DB::table('accounting.reimbursement_request as re_main' )
        ->select('re_main.DEPARTMENT as department')
        ->where('id', $request->processId)
        ->get();


        $department = $department[0]->department;



        // log::debug($department[0]->department);

        DB::table('accounting.reimbursement_expense_details')->where('REID', $request->processId)->delete();
        DB::table('accounting.reimbursement_request_details')->where('REID', $request->processId)->delete();



        if ($td > 0 || $xd > 0) {
            if ($xd > 0) {
                for ($i = 0; $i < count($xdArray); $i++) {
                    $setXDArray[] = [
                        'REID' => $request->processId,
                        'payee_id' => '0',
                        'PAYEE' => $request->payeeName,
                        'CLIENT_NAME' => $xdArray[$i]['CLIENT_NAME'],
                        'TITLEID' => $request->companyId,
                        'PRJID' => $request->projectId,
                        'PROJECT' => $request->projectName,
                        'DESCRIPTION' => $xdArray[$i]['DESCRIPTION'],
                        // 'AMOUNT' => $xdArray[$i]['AMOUNT'],
                        'AMOUNT' => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                        
                        'GUID' => $request->guid,
                        'TS' => now(),
                        'MAINID' => $request->mainId,
                        'STATUS' => 'ACTIVE',
                        'CLIENT_ID' => $xdArray[$i]['CLIENT_ID'],
                        'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                        'DEPT' => $department,
                        'RELEASEDCASH' => '0',
                        'date_' => date_create($xdArray[$i]['date_']),

                    ];
                }

                DB::table('accounting.reimbursement_expense_details')->insert($setXDArray);
            }

            if ($td > 0) {
                for ($i = 0; $i < count($tdArray); $i++) {
                    $setTDArray[] = [

                        'REID' => $request->processId,
                        'PRJID' => $request->projectId,
                        'payee_id' => '0',
                        'PAYEE' => $request->payeeName,
                        'CLIENT_NAME' => $tdArray[$i]['CLIENT_NAME'],
                        'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                        'DESTINATION_TO' => $tdArray[$i]['DESTINATION_TO'],
                        'DESCRIPTION' => $tdArray[$i]['DESCRIPTION'],
                        'AMT_SPENT' => floatval(str_replace(',', '', $tdArray[$i]['AMT_SPENT'])),
                        'TITLEID' => $request->companyId,
                        'MOT' => $tdArray[$i]['MOT'],
                        'PROJECT' => $request->projectName,
                        'GUID' => $request->guid,
                        'TS' => now(),
                        'MAINID' => $request->mainId,
                        'STATUS' => 'ACTIVE',
                        'CLIENT_ID' => $tdArray[$i]['CLIENT_ID'],
                        'DEPT' => $department,
                        'RELEASEDCASH' => '0',
                        'date_' => date_create($tdArray[$i]['date_']),

                    ];
                }
                DB::table('accounting.reimbursement_request_details')->insert($setTDArray);
            }
        } else {
            return response()->json(['message' => 'Request Failed, Please complete required records!'], 202);
        }
    }




    public function updateReMain($request)
    {
        ReMain::where('id', $request->processId)
            ->update([
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'PAYEE' => $request->payeeName,
                'TRANS_DATE' => date_create($request->dateNeeded),
                'AMT_DUE_FRM_EMP' => floatval(str_replace(',', '', $request->amount)),
                'TOTAL_AMT_SPENT' => floatval(str_replace(',', '', $request->amount)),
                'DEADLINE' => date_create($request->dateNeeded),
                'DESCRIPTION' => $request->purpose,
                'STATUS' => 'In Progress',
                'PROJECT' => $request->projectName, // name
                'PRJID' => $request->projectId,
                'CLIENT_NAME' => $request->clientName,
                'MAINID' => $request->mainId,
                'CLIENTID' => $request->clientId,
            ]);
    }








    // reply in clarification - general actual sign
    public function insertNotification($request, $nParentId, $nReceiverId, $nActualId)
    {
        // Update General Norifications to Settled
        DB::table('general.notifications')->insert([
            'ParentID' => $nParentId,
            'levels' => '0',
            'FRM_NAME' => $request->form,
            'PROCESSID' => $request->processId,
            'SENDERID' => $request->loggedUserId,
            'RECEIVERID' => $nReceiverId,
            'MESSAGE' => $request->remarks,
            'TS' => NOW(),
            'SETTLED' => 'YES',
            'ACTUALID' => $nActualId,
            'SENDTOACTUALID' => '0',
            'UserFullName' => $request->loggedUserFullName,
        ]);
    }



    // for reply set status to in progress RFP
    public function replyRfpMain($request)
    {
        RfpMain::where('ID', $request->processId)
            ->update([
                'Deadline' => date_create($request->dateNeeded),
                'AMOUNT' => floatval(str_replace(',', '', $request->amount)),
                'STATUS' => 'In Progress',
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'TITLEID' => $request->companyId,
            ]);
    }


    public function updateRfpDetails($request)
    {
        RfpDetail::where('RFPID', $request->processId)
            ->update([
                'PROJECTID' => $request->projectId,
                'ClientID' => $request->clientId,
                'CLIENTNAME' => $request->clientName,
                'TITLEID' => $request->companyId,
                'MAINID' => $request->mainId,
                'PROJECT' => $request->projectName,
                'DATENEEDED' => date_create($request->dateNeeded),
                'PAYEE' => $request->payeeName,
                'MOP' => $request->modeOfPayment,
                'PURPOSED' => $request->purpose,
                'DESCRIPTION' => $request->purpose,
                'CURRENCY' => $request->currency,
                'AMOUNT' => floatval(str_replace(',', '', $request->amount)),
            ]);
    }

    // this will delete the existing liquidation and add the new ones
    public function updateLiquidation($request)
    {
        // Insert Liquidation
        $liquidationDataTable = $request->liquidation;
        $liquidationDataTable = json_decode($liquidationDataTable, true);

        if (count($liquidationDataTable) > 0) {
            // delete existing data in liquidation
            // RfpLiquidation::where('RFPID' , $request->processId)->delete();
            DB::table('accounting.rfp_liquidation')->where('RFPID', $request->processId)->delete();

            for ($i = 0; $i < count($liquidationDataTable); $i++) {
                $liqdata[] = [
                    'RFPID' => $request->processId,
                    'trans_date' => $liquidationDataTable[$i]['trans_date'],
                    'client_id' => $liquidationDataTable[$i]['client_id'],
                    'client_name' => $liquidationDataTable[$i]['client_name'],
                    'description' => $liquidationDataTable[$i]['description'],
                    'date_' => $liquidationDataTable[$i]['trans_date'],
                    'Amount' => floatval(str_replace(',', '', $liquidationDataTable[$i]['Amount'])),
                    'currency' => $liquidationDataTable[$i]['currency'],
                    'expense_type' => $liquidationDataTable[$i]['expense_type'],
                ];
            }
            DB::table('accounting.rfp_liquidation')->insert($liqdata);
        }
    }



    // update status for clarification to in progress
    // for clarification
    public function updateStatus($request)
    {
        // Turn requests to In Progress
        ActualSign::where('PROCESSID', $request->processId)
            ->where('FRM_NAME', $request->form)
            ->where('COMPID', $request->companyId)
            ->where('STATUS', 'For Clarification')
            ->update([
                'STATUS' => 'In Progress',
                'UID_SIGN' => $request->loggedUserId,
                'SIGNDATETIME' => now(),
                'CurrentSender' => 0,
                'CurrentReceiver' => 0,
                'NOTIFICATIONID' => 0,
                'ApprovedRemarks' => $request->remarks,
            ]);
    }

    public function updateActualSign($request)
    {
        // Update Actual Sign
        ActualSign::where('PROCESSID', $request->processId)
            ->where('FRM_NAME', $request->form)
            ->where('COMPID', $request->companyId)
            ->update([
                'REMARKS' => $request->purpose,
                'DUEDATE' => date_create($request->dateNeeded),
                // 'SIGNDATETIME' => now(),
                'RM_ID' => $request->reportingManagerId,
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'PROJECTID' => $request->projectId,
                'PROJECT' => $request->projectName,
                'COMPID' => $request->companyId,
                'COMPANY' => $request->companyName,
                'CLIENTID' => $request->clientId,
                'CLIENTNAME' => $request->clientName,
                // 'ApprovedRemarks' => $request->remarks,
                'Payee' => $request->payeeName,
                'Amount' => floatval(str_replace(',', '', $request->amount))
            ]);
    }






    // Clarity Button in Inputs - Approver
    public function clarifyBtnInputs(Request $request)
    {

        DB::update("UPDATE general.`actual_sign` a SET a.`webapp` = '1', a.`STATUS` = 'Not Started' WHERE a.`PROCESSID` = '" . $request->processId . "' AND a.`COMPID` = '" . $request->companyId . "'
                AND a.`FRM_CLASS` = 'requestforpayment' AND a.`STATUS` = 'In Progress'  AND a.`ORDERS` = '2' ");

        DB::update("UPDATE general.`actual_sign` a SET a.`webapp` = '1', a.`STATUS` = 'In Progress' WHERE a.`PROCESSID` = '" . $request->processId . "' AND a.`COMPID` = '" . $request->companyId . "'
                AND a.`FRM_CLASS` = 'requestforpayment' AND a.`ORDERS` = '1'");


        $notifIdClarityInp = DB::table('general.notifications')->insertGetId([
            'ParentID'       => '0',
            'levels'         => '0',
            'FRM_NAME'       => $request->form,                 // form
            'PROCESSID'      => $request->processId,            // processid
            'SENDERID'       => $request->loggedUserId,         // loggedUserId
            'RECEIVERID'     => $request->recipientId,          // recipientId
            'MESSAGE'        => $request->remarks,              // remarks
            'TS'             => NOW(),
            'SETTLED'        => 'NO',
            'ACTUALID'       => $request->inprogressId,         // inprogressid
            'SENDTOACTUALID' => '0',
            'UserFullName'   => $request->loggedUserFullname,

        ]);

        DB::update("UPDATE accounting.`request_for_payment` a SET a.`STATUS` = 'For Clarification'  WHERE a.`ID` = '" . $request->processId . "' AND a.`REQREF` = '" . $request->reference . "';");

        DB::update("UPDATE general.`actual_sign` a SET a.`STATUS` = 'For Clarification', a.`CurrentSender` = '" . $request->loggedUserId . "', a.`CurrentReceiver` = '" . $request->recipientId . "' , 
                a.`NOTIFICATIONID` = '" . $notifIdClarityInp . "' , a.`UID_SIGN` = '" . $request->loggedUserId . "',a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" . $request->remarks . "' WHERE
                a.`PROCESSID` = '" . $request->processId . "' AND a.`COMPID` = '" . $request->companyId . "' AND a.`FRM_NAME` = '" . $request->form . "' AND a.`STATUS` = 'In Progress'
                ");

        return response()->json(['message' => 'The request is now for clarification'], 200);
    }


        // View Message
        public function getNotification($id,$frmname){
            $comments = DB::select("
            SELECT 
                *,
                DATE_FORMAT(a.`TS`, '%h:%i %p - %b %d, %Y') AS DTLogs,
                (SELECT 
                UserFull_name 
                FROM
                general.`users` b 
                WHERE b.id = a.`RECEIVERID`) AS 'RECEIVERNAME',
                (SELECT 
                UserFull_name 
              FROM
                general.`users` b 
                WHERE b.id = a.`SENDERID`) AS 'SENDERNAME',
                (SELECT 
                c.`USER_GRP_IND` 
                FROM
                general.`actual_sign` c 
                WHERE c.`ID` = a.`ACTUALID`) AS USERLEVEL,
                (SELECT 
                c.`INITID` 
                FROM
                general.`actual_sign` c 
                WHERE c.`ID` = a.`ACTUALID`) AS INITID 
            FROM
                general.`notifications` a 
            WHERE
                 a.`PROCESSID` = '".$id."'
                AND a.`FRM_NAME` = '".$frmname."'
            ");
            return response()->json($comments,200);
        }

        public function getStatus($id, $frmname, $companyId){
            $status = DB::select("
                SELECT 
                  a.`ID`,a.`USER_GRP_IND`, a.`FRM_NAME`,a.`STATUS`, a.`ORDERS`, a.`ApprovedRemarks`,DATE_FORMAT(a.`SIGNDATETIME`, '%h:%i %p - %b %d, %Y') AS signDateTime,
                  (SELECT 
                    UserFull_name 
                  FROM
                    general.`users` usr 
                  WHERE usr.id = a.`UID_SIGN`) AS 'Approved_By' 
                FROM
                  general.`actual_sign` a 
                WHERE a.`PROCESSID` = '".$id."'
                  AND a.`FRM_NAME` = '".$frmname."' 
                  AND a.`COMPID` = '".$companyId."'
                ORDER BY a.`ORDERS`
            ");
            return response()->json($status,200);
        
        }



        public function createFolder() {
            mkdir('C:\Users\Iverson\Desktop\Cylix\test');
        }

        public function getRequest() {
          $request = DB::select("SELECT *, 0 AS 'selected' FROM humanresource_copy.`dummy` a ");
          return response()->json($request,200);
        }


        
}
