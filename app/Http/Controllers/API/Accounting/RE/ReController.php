<?php

namespace App\Http\Controllers\API\Accounting\RE;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\RE\ReExpenseSetup;
use App\Models\Accounting\RE\ReMain;
use App\Models\Accounting\RE\ReTranspoSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\General\ActualSign;
use App\Models\General\Attachments;


class ReController extends ApiController
{
    public function saveRE(Request $request)
    {
        // true
        // check re if user has draft num return first
        $data = DB::select(" SELECT COUNT(*) AS countDraftNum FROM accounting.`reimbursement_request` WHERE UID = '".$request->loggedUserId."' AND draft_num != '' AND DRAFT_IDEN = 1 ");
        // return the pk of acc.re  base on above query
        $getDraftNumID = DB::select(" SELECT id FROM accounting.`reimbursement_request` WHERE UID = '".$request->loggedUserId."' AND draft_num != '' AND DRAFT_IDEN = 1 ");

        // idOfAttachmentsToDelete - array of id that needs to be delete
        $idArray = $request->idOfAttachmentsToDelete;
        $idArray = json_decode($idArray, true);

        // if idOfAttachmentsToDelete exist / true = buragin sa laravel actual file
        if(count($idArray) >= 1) {
            if(count($getDraftNumID) >= 1) {
                $getAllAttachments = Attachments::whereIn('id', $idArray)->get(); 
                if(count($getAllAttachments) >= 1) {
                    for ($i = 0; $i < count($getAllAttachments); $i++) {
                        $public_path = $getAllAttachments[$i]->filepath . '/' . $getAllAttachments[$i]->filename;
                        if(File::exists($public_path)) {
                            unlink($public_path);
                        }
                    }
                }
            }
        }

        // populate data from acc.re
        if($data[0]->countDraftNum >= 1) {

            // delete old data then insert new draft
            DB::table('accounting.reimbursement_expense_details')->where('REID', $getDraftNumID[0]->id)->delete();
            DB::table('accounting.reimbursement_request_details')->where('REID', $getDraftNumID[0]->id)->delete();

            // delete kasi meron insert na status draft
            DB::table('general.actual_sign')
            ->where('PROCESSID', $getDraftNumID[0]->id)
            ->where('FRM_NAME', $request->form)
            ->where('FRM_CLASS', 'REIMBURSEMENT_REQUEST')
            ->where('COMPID', $request->companyId)->delete();

            // delete file data gen.at 
            for ($i=0; $i < count($idArray); $i++) { 
                DB::table('general.attachments')->where('id', $idArray[$i])->delete();
            }

            $xdArray = $request->expenseType_Data;
            $xdArray = json_decode($xdArray, true);
            $xd = count($xdArray);

            $tdArray = $request->transpoSetup_Data;
            $tdArray = json_decode($tdArray, true);
            $td = count($tdArray);

            if ($td > 0 || $xd > 0) {
                DB::beginTransaction();
                try{  
                    $reference = $this->getReRef($request->companyId);
                    $guid      = $this->getGuid();

                    // update accounting.reimbursement_request
                    Remain::where("id", $getDraftNumID[0]->id)->update([
                        'REQREF' => $reference,
                        'DRAFT_IDEN' => 0,
                        'UID' => $request->loggedUserId,
                        'LNAME' => $request->loggedUserLastName,
                        'FNAME' => $request->loggedUserFirstName,
                        'DEPARTMENT' => $request->loggedUserDepartment,
                        'REPORTING_MANAGER' => $request->reportingManagerName,
                        'PAYEE' => $request->payeeName,
                        'TRANS_DATE' => date_create($request->dateNeeded),
                        'REQUESTED_DATE' => now(),
                        'AMT_DUE_FRM_EMP' => floatval(str_replace(',', '', $request->amount)),
                        'TOTAL_AMT_SPENT' => floatval(str_replace(',', '', $request->amount)),
                        'DEADLINE' => date_create($request->dateNeeded),
                        'DESCRIPTION' => $request->purpose,
                        'STATUS' => 'In Progress',
                        'GUID' => $guid,
                        'PROJECT' => $request->projectName,
                        'PRJID' => $request->projectId,
                        'CLIENT_NAME' => $request->clientName,
                        'TITLEID' => $request->companyId,
                        'MAINID' => $request->mainId,
                        'CLIENTID' => $request->clientId,
                        'webapp' => 1,
                    ]);

                    for ($x = 0; $x < 6; $x++) {
                        $actualSignData[] =
                            [
                                'PROCESSID'         => $getDraftNumID[0]->id,
                                'USER_GRP_IND'      => '0',
                                'FRM_NAME'          => 'Reimbursement Request',
                                'TaskTitle'         => '',
                                'NS'                => '',
                                'FRM_CLASS'         => 'REIMBURSEMENT_REQUEST',
                                'REMARKS'           => $request->purpose,
                                'STATUS'            => 'Not Started',
                                'DUEDATE'           => date_create($request->dateNeeded),
                                'ORDERS'            => $x,
                                'REFERENCE'         => $reference,
                                'PODATE'            => date_create($request->dateNeeded),
                                'DATE'              => now(),
                                'INITID'            => $request->loggedUserId,
                                'FNAME'             => $request->loggedUserFirstName,
                                'LNAME'             => $request->loggedUserLastName,
                                'DEPARTMENT'        => $request->loggedUserDepartment,
                                'RM_ID'             => $request->reportingManagerId,
                                'REPORTING_MANAGER' => $request->reportingManagerName,
                                'PROJECTID'         => $request->projectId,
                                'PROJECT'           => $request->projectName,
                                'COMPID'            => $request->companyId,
                                'COMPANY'           => $request->companyName,
                                'TYPE'              => 'Reimbursement Request',
                                'CLIENTID'          => $request->clientId,
                                'CLIENTNAME'        => $request->clientName,
                                'Max_approverCount' => '6',
                                'DoneApproving'     => '0',
                                'WebpageLink'       => 're_approve.php',
                                'Payee'             => $request->payeeName,
                                'Amount'            => floatval(str_replace(',', '', $request->amount)),
                                'webapp'            => NULL,
                            ];
                    }

                    if ($actualSignData[0]['ORDERS'] == 0) {
                        $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                        $actualSignData[0]['STATUS']       = 'In Progress';
                    }

                    if ($actualSignData[1]['ORDERS'] == 1) {
                        $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Accounting';
                    }

                    if ($actualSignData[2]['ORDERS'] == 2) {
                        $actualSignData[2]['USER_GRP_IND'] = 'For Approval of Management';
                    }

                    if ($actualSignData[3]['ORDERS'] == 3) {
                        $actualSignData[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
                    }

                    if ($actualSignData[4]['ORDERS'] == 4) {
                        $actualSignData[4]['USER_GRP_IND'] = 'Releasing of Cash';
                    }

                    if ($actualSignData[5]['ORDERS'] == 5) {
                        $actualSignData[5]['USER_GRP_IND'] = 'Initiator';
                    }
                    
                    ActualSign::insert($actualSignData);
                    $request->request->add(['processId' => $getDraftNumID[0]->id]);
                    $request->request->add(['referenceNumber' => $reference]);

                    $this->addAttachments($request);

                    if ($xd > 0) {
                        for ($i = 0; $i < count($xdArray); $i++) {
                            $setXDArray[] = [
                                'REID'         => $getDraftNumID[0]->id,
                                'payee_id'     => '0',
                                'PAYEE'        => $request->payeeName,
                                'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                                'TITLEID'      => $request->companyId,
                                'PRJID'        => $request->projectId,
                                'PROJECT'      => $request->projectName,
                                'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                                'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                                'GUID'         => $guid,
                                'TS'           => now(),
                                'MAINID'       => $request->mainId,
                                'STATUS'       => 'ACTIVE',
                                'CLIENT_ID'    => $xdArray[$i]['CLIENT_ID'],
                                'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                                'DEPT'         => $request->loggedUserDepartment,
                                'RELEASEDCASH' => '0',
                                'date_'        => date_create($xdArray[$i]['date_']),
                                
                            ];
                        }

                        DB:: table('accounting.reimbursement_expense_details')->insert($setXDArray);
                    }

                    if ($td > 0) {
                        for ($i = 0; $i < count($tdArray); $i++) {
                            $setTDArray[] = [
                                'REID'            => $getDraftNumID[0]->id,
                                'PRJID'           => $request->projectId,
                                'payee_id'        => '0',
                                'PAYEE'           => $request->payeeName,
                                'CLIENT_NAME'     => $tdArray[$i]['CLIENT_NAME'],
                                'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                                'DESTINATION_TO'  => $tdArray[$i]['DESTINATION_TO'],
                                'DESCRIPTION'     => $tdArray[$i]['DESCRIPTION'],
                                'AMT_SPENT'       => floatval(str_replace(',', '', $tdArray[$i]['AMT_SPENT'])),
                                'TITLEID'         => $request->companyId,
                                'MOT'             => $tdArray[$i]['MOT'],
                                'PROJECT'         => $request->projectName,
                                'GUID'            => $guid,
                                'TS'              => now(),
                                'MAINID'          => $request->mainId,
                                'STATUS'          => 'ACTIVE',
                                'CLIENT_ID'       => $tdArray[$i]['CLIENT_ID'],
                                'DEPT'            => $request->loggedUserDepartment,
                                'RELEASEDCASH'    => '0',
                                'date_'           => date_create($tdArray[$i]['date_']),
                            ];
                        }
                        DB:: table('accounting.reimbursement_request_details')->insert($setTDArray);
                    }

                    DB:: commit();
                    // return response()->json($request, 201);
                    return response()->json(['message' => 'Your Reimbursement request was successfully submitted.'], 200);
                    // return response()->json(['message' => 'Cash Advance has been Successfully submitted'], 201);
                }catch(\Exception $e){
                    DB:: rollback();
                    // throw error response
                    return response()->json($e, 500);
                }
            } 
            else {
                return response()->json(['message' => 'Request Failed, Please complete required records!'], 202);
            }
        }
        else {
            $xdArray = $request->expenseType_Data;
            $xdArray = json_decode($xdArray, true);
            $xd = count($xdArray);

            $tdArray = $request->transpoSetup_Data;
            $tdArray = json_decode($tdArray, true);
            $td = count($tdArray);

            if ($td > 0 || $xd > 0) {
                DB::beginTransaction();
                try{  
                    $reference = $this->getReRef($request->companyId);
                    $guid      = $this->getGuid();

                    $reMain                    = new ReMain();
                    $reMain->REQREF            = $reference;
                    $reMain->UID               = $request->loggedUserId;
                    $reMain->LNAME             = $request->loggedUserLastName;
                    $reMain->FNAME             = $request->loggedUserFirstName;
                    $reMain->DEPARTMENT        = $request->loggedUserDepartment;
                    $reMain->REPORTING_MANAGER = $request->reportingManagerName;
                    $reMain->PAYEE             = $request->payeeName;
                    $reMain->TRANS_DATE        = date_create($request->dateNeeded);
                    $reMain->REQUESTED_DATE    = now();
                    $reMain->AMT_DUE_FRM_EMP   = floatval(str_replace(',', '', $request->amount));
                    $reMain->TOTAL_AMT_SPENT   = floatval(str_replace(',', '', $request->amount));
                    $reMain->DEADLINE          = date_create($request->dateNeeded);
                    $reMain->DESCRIPTION       = $request->purpose;
                    $reMain->STATUS            = 'In Progress';
                    $reMain->GUID              = $guid;
                    $reMain->PROJECT           = $request->projectName;
                    $reMain->PRJID             = $request->projectId;
                    $reMain->CLIENT_NAME       = $request->clientName;
                    $reMain->TITLEID           = $request->companyId;
                    $reMain->MAINID            = $request->mainId;
                    $reMain->CLIENTID          = $request->clientId;
                    $reMain->webapp            = '1';
                    $reMain->save();

                    for ($x = 0; $x < 6; $x++) {
                        $actualSignData[] =
                            [
                                'PROCESSID'         => $reMain->id,
                                'USER_GRP_IND'      => '0',
                                'FRM_NAME'          => 'Reimbursement Request',
                                'TaskTitle'         => '',
                                'NS'                => '',
                                'FRM_CLASS'         => 'REIMBURSEMENT_REQUEST',
                                'REMARKS'           => $request->purpose,
                                'STATUS'            => 'Not Started',
                                'DUEDATE'           => date_create($request->dateNeeded),
                                'ORDERS'            => $x,
                                'REFERENCE'         => $reference,
                                'PODATE'            => date_create($request->dateNeeded),
                                'DATE'              => now(),
                                'INITID'            => $request->loggedUserId,
                                'FNAME'             => $request->loggedUserFirstName,
                                'LNAME'             => $request->loggedUserLastName,
                                'DEPARTMENT'        => $request->loggedUserDepartment,
                                'RM_ID'             => $request->reportingManagerId,
                                'REPORTING_MANAGER' => $request->reportingManagerName,
                                'PROJECTID'         => $request->projectId,
                                'PROJECT'           => $request->projectName,
                                'COMPID'            => $request->companyId,
                                'COMPANY'           => $request->companyName,
                                'TYPE'              => 'Reimbursement Request',
                                'CLIENTID'          => $request->clientId,
                                'CLIENTNAME'        => $request->clientName,
                                'Max_approverCount' => '6',
                                'DoneApproving'     => '0',
                                'WebpageLink'       => 're_approve.php',
                                'Payee'             => $request->payeeName,
                                'Amount'            => floatval(str_replace(',', '', $request->amount)),
                                'webapp'            => NULL,
                            ];
                    }

                    if ($actualSignData[0]['ORDERS'] == 0) {
                        $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                        $actualSignData[0]['STATUS']       = 'In Progress';
                    }

                    if ($actualSignData[1]['ORDERS'] == 1) {
                        $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Accounting';
                    }

                    if ($actualSignData[2]['ORDERS'] == 2) {
                        $actualSignData[2]['USER_GRP_IND'] = 'For Approval of Management';
                    }

                    if ($actualSignData[3]['ORDERS'] == 3) {
                        $actualSignData[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
                    }

                    if ($actualSignData[4]['ORDERS'] == 4) {
                        $actualSignData[4]['USER_GRP_IND'] = 'Releasing of Cash';
                    }

                    if ($actualSignData[5]['ORDERS'] == 5) {
                        $actualSignData[5]['USER_GRP_IND'] = 'Initiator';
                    }
                    
                    ActualSign::insert($actualSignData);
                    $request->request->add(['processId' => $reMain->id]);
                    $request->request->add(['referenceNumber' => $reference]);

                    $this->addAttachments($request);

                    if ($xd > 0) {
                        for ($i = 0; $i < count($xdArray); $i++) {
                            $setXDArray[] = [
                                'REID'         => $reMain->id,
                                'payee_id'     => '0',
                                'PAYEE'        => $request->payeeName,
                                'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                                'TITLEID'      => $request->companyId,
                                'PRJID'        => $request->projectId,
                                'PROJECT'      => $request->projectName,
                                'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                                'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                                'GUID'         => $guid,
                                'TS'           => now(),
                                'MAINID'       => $request->mainId,
                                'STATUS'       => 'ACTIVE',
                                'CLIENT_ID'    => $xdArray[$i]['CLIENT_ID'],
                                'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                                'DEPT'         => $request->loggedUserDepartment,
                                'RELEASEDCASH' => '0',
                                'date_'        => date_create($xdArray[$i]['date_']),
                                
                            ];
                        }

                        DB:: table('accounting.reimbursement_expense_details')->insert($setXDArray);
                    }

                    if ($td > 0) {
                        for ($i = 0; $i < count($tdArray); $i++) {
                            $setTDArray[] = [
                                'REID'            => $reMain->id,
                                'PRJID'           => $request->projectId,
                                'payee_id'        => '0',
                                'PAYEE'           => $request->payeeName,
                                'CLIENT_NAME'     => $tdArray[$i]['CLIENT_NAME'],
                                'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                                'DESTINATION_TO'  => $tdArray[$i]['DESTINATION_TO'],
                                'DESCRIPTION'     => $tdArray[$i]['DESCRIPTION'],
                                'AMT_SPENT'       => floatval(str_replace(',', '', $tdArray[$i]['AMT_SPENT'])),
                                'TITLEID'         => $request->companyId,
                                'MOT'             => $tdArray[$i]['MOT'],
                                'PROJECT'         => $request->projectName,
                                'GUID'            => $guid,
                                'TS'              => now(),
                                'MAINID'          => $request->mainId,
                                'STATUS'          => 'ACTIVE',
                                'CLIENT_ID'       => $tdArray[$i]['CLIENT_ID'],
                                'DEPT'            => $request->loggedUserDepartment,
                                'RELEASEDCASH'    => '0',
                                'date_'           => date_create($tdArray[$i]['date_']),
                            ];
                        }
                        DB:: table('accounting.reimbursement_request_details')->insert($setTDArray);
                    }

                    DB:: commit();
                    // return response()->json($request, 201);
                    return response()->json(['message' => 'Your Reimbursement request was successfully submitted.'], 200);
                    // return response()->json(['message' => 'Cash Advance has been Successfully submitted'], 201);
                }catch(\Exception $e){
                    DB:: rollback();
                    // throw error response
                    return response()->json($e, 500);
                }
            } 
            else {
                return response()->json(['message' => 'Request Failed, Please complete required records!'], 202);
            }
        }
    }

    public function getRE($id)
    {
        $reMain = ReMain::findOrFail($id);
        return $this->showOne($reMain);
    }

    public function getExpense($id)
    {
        $expenseData = ReExpenseSetup::where('REID', $id)->get();
        return response()->json($expenseData, 200);
    }
    public function getTranspo($id)
    {
        $transpoData = ReTranspoSetup::where('REID', $id)->get();
        return response()->json($transpoData, 200);
    }

    public function saveDraftRE(Request $request)
    {
        $data = DB::select(" SELECT COUNT(*) AS countDraftNum FROM accounting.`reimbursement_request` WHERE UID = '".$request->loggedUserId."' AND draft_num != '' AND DRAFT_IDEN = 1 ");
        $getDraftNumID = DB::select(" SELECT id FROM accounting.`reimbursement_request` WHERE UID = '".$request->loggedUserId."' AND draft_num != '' AND DRAFT_IDEN = 1 ");

        $idArray = $request->idOfAttachmentsToDelete;
        $idArray = json_decode($idArray, true);

        // remove attachment from the project folder
        if(count($idArray) >= 1) {
            if(count($getDraftNumID) >= 1) {
                $getAllAttachments = Attachments::whereIn('id', $idArray)->get(); 
                if(count($getAllAttachments) >= 1) {
                    for ($i = 0; $i < count($getAllAttachments); $i++) {
                        $public_path = $getAllAttachments[$i]->filepath . '/' . $getAllAttachments[$i]->filename;
                        if(File::exists($public_path)) {
                            unlink($public_path);
                        }
                    }
                }
            }
        }

        if($data[0]->countDraftNum >= 1) {
            DB::table('accounting.reimbursement_expense_details')->where('REID', $getDraftNumID[0]->id)->delete();
            DB::table('accounting.reimbursement_request_details')->where('REID', $getDraftNumID[0]->id)->delete();
            DB::table('general.actual_sign')->where('PROCESSID', $getDraftNumID[0]->id)->where('FRM_NAME', $request->form)->where('FRM_CLASS', 'REIMBURSEMENT_REQUEST')->where('COMPID', $request->companyId)->delete();
            
            // delete selected remove attachment from frontend
            for ($i=0; $i < count($idArray); $i++) { 
                DB::table('general.attachments')->where('id', $idArray[$i])->delete();
            }

            $xdArray = $request->expenseType_Data;
            $xdArray = json_decode($xdArray, true);
            $xd = count($xdArray);

            $tdArray = $request->transpoSetup_Data;
            $tdArray = json_decode($tdArray, true);
            $td = count($tdArray);

            DB::beginTransaction();
            try{  
                $reference = $this->getReRef($request->companyId);
                $draftReference = $this->getDraftReRef($request->companyId);
                $guid      = $this->getGuid();

                // update accounting.reimbursement_request
                Remain::where("id", $getDraftNumID[0]->id)->update([
                    'REQREF' => "",
                    'DRAFT_IDEN' => 1,
                    'UID' => $request->loggedUserId,
                    'LNAME' => $request->loggedUserLastName,
                    'FNAME' => $request->loggedUserFirstName,
                    'DEPARTMENT' => $request->loggedUserDepartment,
                    'REPORTING_MANAGER' => $request->reportingManagerName,
                    'PAYEE' => $request->payeeName,
                    'TRANS_DATE' => date_create($request->dateNeeded),
                    'REQUESTED_DATE' => now(),
                    'AMT_DUE_FRM_EMP' => floatval(str_replace(',', '', $request->amount)),
                    'TOTAL_AMT_SPENT' => floatval(str_replace(',', '', $request->amount)),
                    'DEADLINE' => date_create($request->dateNeeded),
                    'DESCRIPTION' => $request->purpose,
                    'STATUS' => 'Draft',
                    'GUID' => $guid,
                    'PROJECT' => $request->projectName,
                    'PRJID' => $request->projectId,
                    'CLIENT_NAME' => $request->clientName,
                    'TITLEID' => $request->companyId,
                    'MAINID' => $request->mainId,
                    'CLIENTID' => $request->clientId,
                    'webapp' => 1,
                ]);

                $actualSignData = array(
                    'PROCESSID'         => $getDraftNumID[0]->id,
                    'USER_GRP_IND'      => 'Reporting Manager',
                    'FRM_NAME'          => 'Reimbursement Request',
                    'TaskTitle'         => '',
                    'NS'                => '',
                    'FRM_CLASS'         => 'REIMBURSEMENT_REQUEST',
                    'REMARKS'           => $request->purpose,
                    'STATUS'            => 'Draft',
                    'DUEDATE'           => date_create($request->dateNeeded),
                    'ORDERS'            => 0,
                    'REFERENCE'         => $draftReference,
                    'PODATE'            => date_create($request->dateNeeded),
                    'DATE'              => now(),
                    'INITID'            => $request->loggedUserId,
                    'FNAME'             => $request->loggedUserFirstName,
                    'LNAME'             => $request->loggedUserLastName,
                    'DEPARTMENT'        => $request->loggedUserDepartment,
                    'RM_ID'             => $request->reportingManagerId,
                    'REPORTING_MANAGER' => $request->reportingManagerName,
                    'PROJECTID'         => $request->projectId,
                    'PROJECT'           => $request->projectName,
                    'COMPID'            => $request->companyId,
                    'COMPANY'           => $request->companyName,
                    'TYPE'              => 'Reimbursement Request',
                    'CLIENTID'          => $request->clientId,
                    'CLIENTNAME'        => $request->clientName,
                    'Max_approverCount' => '6',
                    'DoneApproving'     => '0',
                    'WebpageLink'       => 're_approve.php',
                    'Payee'             => $request->payeeName,
                    'Amount'            => floatval(str_replace(',', '', $request->amount)),
                    'webapp'            => 1,
                );
                ActualSign::insert($actualSignData);
                
                $request->request->add(['processId' => $getDraftNumID[0]->id]);
                $request->request->add(['referenceNumber' => $reference]);

                $this->addAttachments($request);

                if ($xd > 0) {
                    for ($i = 0; $i < count($xdArray); $i++) {
                        $setXDArray[] = [
                            'REID'         => $getDraftNumID[0]->id,
                            'payee_id'     => '0',
                            'PAYEE'        => $request->payeeName,
                            'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                            'TITLEID'      => $request->companyId,
                            'PRJID'        => $request->projectId,
                            'PROJECT'      => $request->projectName,
                            'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                            'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                            'GUID'         => $guid,
                            'TS'           => now(),
                            'MAINID'       => $request->mainId,
                            'STATUS'       => 'INACTIVE',
                            'CLIENT_ID'    => $xdArray[$i]['CLIENT_ID'],
                            'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                            'DEPT'         => $request->loggedUserDepartment,
                            'RELEASEDCASH' => '0',
                            'date_'        => date_create($xdArray[$i]['date_']),
                            
                        ];
                    }

                    DB:: table('accounting.reimbursement_expense_details')->insert($setXDArray);
                }

                if ($td > 0) {
                    for ($i = 0; $i < count($tdArray); $i++) {
                        $setTDArray[] = [
                            'REID'            => $getDraftNumID[0]->id,
                            'PRJID'           => $request->projectId,
                            'payee_id'        => '0',
                            'PAYEE'           => $request->payeeName,
                            'CLIENT_NAME'     => $tdArray[$i]['CLIENT_NAME'],
                            'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                            'DESTINATION_TO'  => $tdArray[$i]['DESTINATION_TO'],
                            'DESCRIPTION'     => $tdArray[$i]['DESCRIPTION'],
                            'AMT_SPENT'       => floatval(str_replace(',', '', $tdArray[$i]['AMT_SPENT'])),
                            'TITLEID'         => $request->companyId,
                            'MOT'             => $tdArray[$i]['MOT'],
                            'PROJECT'         => $request->projectName,
                            'GUID'            => $guid,
                            'TS'              => now(),
                            'MAINID'          => $request->mainId,
                            'STATUS'          => 'INACTIVE',
                            'CLIENT_ID'       => $tdArray[$i]['CLIENT_ID'],
                            'DEPT'            => $request->loggedUserDepartment,
                            'RELEASEDCASH'    => '0',
                            'date_'           => date_create($tdArray[$i]['date_']),
                        ];
                    }
                    DB:: table('accounting.reimbursement_request_details')->insert($setTDArray);
                }

                DB:: commit();
                return response()->json(['message' => 'Your data has been draft'], 200);
        
            }catch(\Exception $e){
                DB:: rollback();
                return response()->json($e, 500);
            }
        }
        else {
            // insert new draft
            $xdArray = $request->expenseType_Data;
            $xdArray = json_decode($xdArray, true);
            $xd = count($xdArray);

            $tdArray = $request->transpoSetup_Data;
            $tdArray = json_decode($tdArray, true);
            $td = count($tdArray);

            DB::beginTransaction();
            try{  
                $reference = $this->getReRef($request->companyId);
                $draftReference = $this->getDraftReRef($request->companyId);
                $guid      = $this->getGuid();

                $reMain                    = new ReMain();
                $reMain->REQREF            = "";
                $reMain->DRAFT_IDEN        = 1;
                $reMain->DRAFT_NUM         = $draftReference;
                $reMain->UID               = $request->loggedUserId;
                $reMain->LNAME             = $request->loggedUserLastName;
                $reMain->FNAME             = $request->loggedUserFirstName;
                $reMain->DEPARTMENT        = $request->loggedUserDepartment;
                $reMain->REPORTING_MANAGER = $request->reportingManagerName;
                $reMain->PAYEE             = $request->payeeName;
                $reMain->TRANS_DATE        = date_create($request->dateNeeded);
                $reMain->REQUESTED_DATE    = now();
                $reMain->AMT_DUE_FRM_EMP   = floatval(str_replace(',', '', $request->amount));
                $reMain->TOTAL_AMT_SPENT   = floatval(str_replace(',', '', $request->amount));
                $reMain->DEADLINE          = date_create($request->dateNeeded);
                $reMain->DESCRIPTION       = $request->purpose;
                $reMain->STATUS            = 'Draft';
                $reMain->GUID              = $guid;
                $reMain->PROJECT           = $request->projectName;
                $reMain->PRJID             = $request->projectId;
                $reMain->CLIENT_NAME       = $request->clientName;
                $reMain->TITLEID           = $request->companyId;
                $reMain->MAINID            = $request->mainId;
                $reMain->CLIENTID          = $request->clientId;
                $reMain->webapp            = '1';
                $reMain->save();

                $actualSignData = array(
                    'PROCESSID'         => $reMain->id,
                    'USER_GRP_IND'      => 'Reporting Manager',
                    'FRM_NAME'          => 'Reimbursement Request',
                    'TaskTitle'         => '',
                    'NS'                => '',
                    'FRM_CLASS'         => 'REIMBURSEMENT_REQUEST',
                    'REMARKS'           => $request->purpose,
                    'STATUS'            => 'Draft',
                    'DUEDATE'           => date_create($request->dateNeeded),
                    'ORDERS'            => 0,
                    'REFERENCE'         => $draftReference,
                    'PODATE'            => date_create($request->dateNeeded),
                    'DATE'              => now(),
                    'INITID'            => $request->loggedUserId,
                    'FNAME'             => $request->loggedUserFirstName,
                    'LNAME'             => $request->loggedUserLastName,
                    'DEPARTMENT'        => $request->loggedUserDepartment,
                    'RM_ID'             => $request->reportingManagerId,
                    'REPORTING_MANAGER' => $request->reportingManagerName,
                    'PROJECTID'         => $request->projectId,
                    'PROJECT'           => $request->projectName,
                    'COMPID'            => $request->companyId,
                    'COMPANY'           => $request->companyName,
                    'TYPE'              => 'Reimbursement Request',
                    'CLIENTID'          => $request->clientId,
                    'CLIENTNAME'        => $request->clientName,
                    'Max_approverCount' => '6',
                    'DoneApproving'     => '0',
                    'WebpageLink'       => 're_approve.php',
                    'Payee'             => $request->payeeName,
                    'Amount'            => floatval(str_replace(',', '', $request->amount)),
                    'webapp'            => 1,
                );
                ActualSign::insert($actualSignData);
                
                $request->request->add(['processId' => $reMain->id]);
                $request->request->add(['referenceNumber' => $reference]);

                $this->addAttachments($request);

                if ($xd > 0) {
                    for ($i = 0; $i < count($xdArray); $i++) {
                        $setXDArray[] = [
                            'REID'         => $reMain->id,
                            'payee_id'     => '0',
                            'PAYEE'        => $request->payeeName,
                            'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                            'TITLEID'      => $request->companyId,
                            'PRJID'        => $request->projectId,
                            'PROJECT'      => $request->projectName,
                            'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                            'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                            'GUID'         => $guid,
                            'TS'           => now(),
                            'MAINID'       => $request->mainId,
                            'STATUS'       => 'INACTIVE',
                            'CLIENT_ID'    => $xdArray[$i]['CLIENT_ID'],
                            'EXPENSE_TYPE' => $xdArray[$i]['EXPENSE_TYPE'],
                            'DEPT'         => $request->loggedUserDepartment,
                            'RELEASEDCASH' => '0',
                            'date_'        => date_create($xdArray[$i]['date_']),
                            
                        ];
                    }

                    DB:: table('accounting.reimbursement_expense_details')->insert($setXDArray);
                }

                if ($td > 0) {
                    for ($i = 0; $i < count($tdArray); $i++) {
                        $setTDArray[] = [
                            'REID'            => $reMain->id,
                            'PRJID'           => $request->projectId,
                            'payee_id'        => '0',
                            'PAYEE'           => $request->payeeName,
                            'CLIENT_NAME'     => $tdArray[$i]['CLIENT_NAME'],
                            'DESTINATION_FRM' => $tdArray[$i]['DESTINATION_FRM'],
                            'DESTINATION_TO'  => $tdArray[$i]['DESTINATION_TO'],
                            'DESCRIPTION'     => $tdArray[$i]['DESCRIPTION'],
                            'AMT_SPENT'       => floatval(str_replace(',', '', $tdArray[$i]['AMT_SPENT'])),
                            'TITLEID'         => $request->companyId,
                            'MOT'             => $tdArray[$i]['MOT'],
                            'PROJECT'         => $request->projectName,
                            'GUID'            => $guid,
                            'TS'              => now(),
                            'MAINID'          => $request->mainId,
                            'STATUS'          => 'INACTIVE',
                            'CLIENT_ID'       => $tdArray[$i]['CLIENT_ID'],
                            'DEPT'            => $request->loggedUserDepartment,
                            'RELEASEDCASH'    => '0',
                            'date_'           => date_create($tdArray[$i]['date_']),
                        ];
                    }
                    DB:: table('accounting.reimbursement_request_details')->insert($setTDArray);
                }

                DB:: commit();
                return response()->json(['message' => 'Your data has been draft'], 200);
        
            }catch(\Exception $e){
                DB:: rollback();
                return response()->json($e, 500);
            }
        }
    }

    public function getREbyUserID($userid)
    {
        $reMain = DB::table('accounting.reimbursement_request as r')
                ->join('general.users as g', 'r.REPORTING_MANAGER', '=', 'g.UserFull_name')
                ->where('r.DRAFT_NUM', '!=', '')
                ->where('r.DRAFT_IDEN', 1)
                ->where('r.UID', $userid)
                ->select('r.*', 'g.id as reportingManagerID')
                ->get();

        return response()->json($reMain, 200);
    }

    public function getReGeneralAttachmentsByReqid($reqid, $loggeduserID)
    {
        // $attachmentsData = Attachments::where('REQID', $reqid)->where('INITID', 136)->get();
        $attachmentsData = Attachments::where(['REQID' => $reqid, 'INITID' => $loggeduserID, 'formName' => 'Reimbursement Request'])->get();
        return response()->json($attachmentsData, 200);
    }



}
