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
use Illuminate\Support\Facades\Log;


class ReController extends ApiController
{
    public function saveREF(Request $request){

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

    public function saveRE(Request $request)
    {
        DB::beginTransaction();
        try
        {  

        $reference = $this->getReRef($request->companyId); // RE-2022-0001
        $dateNeeded = strtotime($request->dateNeeded) ? date_create($request->dateNeeded) : null;
        $data = [
            'REQREF' => $reference,
            'UID' => $request->loggedUserId,
            'DRAFT_IDEN' => 0,
            'LNAME' => $request->loggedUserLastName,
            'FNAME' => $request->loggedUserFirstName,
            'DEPARTMENT' => $request->loggedUserDepartment,
            'REPORTING_MANAGER' => $request->reportingManagerName,
            'PAYEE' => $request->payeeName,
            'TRANS_DATE' => $dateNeeded,
            'REQUESTED_DATE' => now(),
            'AMT_DUE_FRM_EMP' => $request->amount,
            'TOTAL_AMT_SPENT' => $request->amount,
            'DEADLINE' => $dateNeeded,
            'DESCRIPTION' => $request->purpose,
            'STATUS' => 'In Progress',
            'PROJECT' => $request->projectName,
            'PRJID' => $request->projectId,
            'CLIENT_NAME' => $request->clientName,
            'TITLEID' => $request->companyId,
            'MAINID' => $request->mainId,
            'CLIENTID' => $request->clientId,
            'webapp' => 1
        ];
        ReMain::where('id', $request->processId)->update($data);

        DB::table('accounting.reimbursement_expense_details')->where('REID', $request->processId)->delete();
        DB::table('accounting.reimbursement_request_details')->where('REID', $request->processId)->delete();

        $xdArray = $request->expenseType_Data;
        $xdArray = json_decode($xdArray, true);
        $xd = count($xdArray);

        $tdArray = $request->transpoSetup_Data;
        $tdArray = json_decode($tdArray, true);
        $td = count($tdArray);

        if ($xd > 0) {
            for ($i = 0; $i < count($xdArray); $i++) {
                $setXDArray[] = [
                    'REID'         => $request->processId,
                    'payee_id'     => '0',
                    'PAYEE'        => $request->payeeName,
                    'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                    'TITLEID'      => $request->companyId,
                    'PRJID'        => $request->projectId,
                    'PROJECT'      => $request->projectName,
                    'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                    'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                    'GUID'            => $request->guid,
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
                    'REID'            => $request->processId,
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
                    'GUID'            => $request->guid,
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


            DB::table('general.actual_sign')
            ->where('PROCESSID', $request->processId)
            ->where('FRM_NAME',$request->form)
            ->where('STATUS','Draft')
            ->where('COMPID', $request->companyId)
            ->delete();

            for ($x = 0; $x < 6; $x++) {
                $actualSignData[] =
                    [
                        'PROCESSID'         => $request->processId,
                        'USER_GRP_IND'      => '0',
                        'FRM_NAME'          => 'Reimbursement Request',
                        'TaskTitle'         => '',
                        'NS'                => '',
                        'FRM_CLASS'         => 'REIMBURSEMENT_REQUEST',
                        'REMARKS'           => $request->purpose,
                        'STATUS'            => 'Not Started',
                        'DUEDATE'           => $dateNeeded,
                        'ORDERS'            => $x,
                        'REFERENCE'         => $reference,
                        'PODATE'            => $dateNeeded,
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
            $request->request->add(['referenceNumber' => $reference]);
            $this->addAttachments($request);

            $idArray = $request->idOfAttachmentsToDelete;
            $idArray = json_decode($idArray, true);

            if(count($idArray) >= 1) {

                foreach ($idArray as $id) {
                    $result = Attachments::where('id', $id)->get(); 
                    
                    if ($result->isNotEmpty()) {
                        $attachmentData = $result[0];
                        $public_path = $attachmentData->filepath . '/' . $attachmentData->filename;
    
                        if(File::exists($public_path)) {
                            unlink($public_path);
                            Attachments::where('id', $id)->delete();
                        }
    
                    }
                }                
            }


        $reqRef = str_replace('-', '_', $request->referenceNumber); // 'RE_2023_0002'
        $reqRefDR = str_replace('-', '_', $request->referenceNumberDR); // 'REDR_2023_0002'



        $result2 = Attachments::where('REQID', $request->processId)->where('INITID', $request->loggedUserId)->where('formName', 'Reimbursement Request')->get();
        if ($result2->isNotEmpty()) {
            foreach($result2 as $res) {
                $public_path = $res->filepath . '/' . $res->filename;
                $new_public_path = "storage/Attachments/$request->companyId/$request->class/$reqRef/$res->filename";   // For moving the file


                $destinationPath = "public/Attachments/{$request->companyId}/{$request->class}/" . $reqRef;   // For moving the file
                $storagePath     = "storage/Attachments/{$request->companyId}/{$request->class}/" . $reqRef;  // For preview


                if(File::exists($public_path)) {
                    File::move(public_path($public_path), public_path($new_public_path));
                    // log::debug(public_path($public_path));
                    // log::debug(public_path($new_public_path));

                    $row = Attachments::find($res->id);
                    $row->filepath = $storagePath;
                    $row->fileDestination = $destinationPath;
                    $row->save();
                }

            }
        }

        DB:: commit();
        return response()->json(['message' => 'Your Reimbursement request was successfully submitted.'], 200);

        }
        catch(\Exception $e)
        {
            DB:: rollback();
            return response()->json($e, 500);
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

    public function saveNewREDrafts(Request $request)
    {
        $xdArray = $request->expenseType_Data;
        $xdArray = json_decode($xdArray, true);
        $xd      = count($xdArray);

        $tdArray = $request->transpoSetup_Data;
        $tdArray = json_decode($tdArray, true);
        $td      = count($tdArray);

        DB::beginTransaction();
        try
        {  
            $reference      = $this->getReRef($request->companyId);
            $draftReference = $this->getDraftReRef($request->companyId);
            $guid           = $this->getGuid();

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
                $request->request->add(['referenceNumber' => $draftReference]);

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
                return response()->json(['message' => 'Draft saved successfully!'], 200);

        }
        catch(\Exception $e)
        {
            DB:: rollback();
            return response()->json($e, 500);
        }
    }
                
    

    public function saveREDrafts(Request $request)
    {
        DB::beginTransaction();
        try{  

        $dateNeeded = strtotime($request->dateNeeded) ? date_create($request->dateNeeded) : null ;
        $data = [
            'UID' => $request->loggedUserId,
            'LNAME' => $request->loggedUserLastName,
            'FNAME' => $request->loggedUserFirstName,
            'DEPARTMENT' => $request->loggedUserDepartment,
            'REPORTING_MANAGER' => $request->reportingManagerName,
            'PAYEE' => $request->payeeName,
            'TRANS_DATE' => $dateNeeded,
            'AMT_DUE_FRM_EMP' => $request->amount,
            'TOTAL_AMT_SPENT' => $request->amount,
            'DEADLINE' => $dateNeeded,
            'DESCRIPTION' => $request->purpose,
            'PROJECT' => $request->projectName,
            'PRJID' => $request->projectId,
            'CLIENT_NAME' => $request->clientName,
            'TITLEID' => $request->companyId,
            'MAINID' => $request->mainId,
            'CLIENTID' => $request->clientId
        ];
        
        ReMain::where('id', $request->processId)->update($data);

        DB::table('accounting.reimbursement_expense_details')->where('REID', $request->processId)->delete();
        DB::table('accounting.reimbursement_request_details')->where('REID', $request->processId)->delete();


        $xdArray = $request->expenseType_Data;
        $xdArray = json_decode($xdArray, true);
        $xd = count($xdArray);

        $tdArray = $request->transpoSetup_Data;
        $tdArray = json_decode($tdArray, true);
        $td = count($tdArray);

        if ($xd > 0) {
            for ($i = 0; $i < count($xdArray); $i++) {
                $setXDArray[] = [
                    'REID'         => $request->processId,
                    'payee_id'     => '0',
                    'PAYEE'        => $request->payeeName,
                    'CLIENT_NAME'  => $xdArray[$i]['CLIENT_NAME'],
                    'TITLEID'      => $request->companyId,
                    'PRJID'        => $request->projectId,
                    'PROJECT'      => $request->projectName,
                    'DESCRIPTION'  => $xdArray[$i]['DESCRIPTION'],
                    'AMOUNT'       => floatval(str_replace(',', '', $xdArray[$i]['AMOUNT'])),
                    'GUID'            => $request->guid,
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
                    'REID'            => $request->processId,
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
                    'GUID'            => $request->guid,
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

        $ActualSignREDR = DB::table('general.actual_sign')->where('PROCESSID', $request->processId)->where('FRM_NAME',$request->form)->where('STATUS','Draft')->where('COMPID', $request->companyId)->exists();
        if($ActualSignREDR) {
            
            DB::table('general.actual_sign')
            ->where('PROCESSID', $request->processId)
            ->where('FRM_NAME',$request->form)
            ->where('STATUS','Draft')
            ->where('COMPID', $request->companyId)
            ->update([
                'REMARKS'           => $request->purpose,
                'DUEDATE'           => $dateNeeded,
                'PODATE'            => $dateNeeded,
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
                'CLIENTID'          => $request->clientId,
                'CLIENTNAME'        => $request->clientName,
                'Payee'             => $request->payeeName,
                'Amount'            => floatval(str_replace(',', '', $request->amount))
            ]);
        }
        
        $this->addAttachments($request);

        $idArray = $request->idOfAttachmentsToDelete;
        $idArray = json_decode($idArray, true);

        log::debug($idArray);
        
        if(count($idArray) >= 1) {

            foreach ($idArray as $id) {
                $result = Attachments::where('id', $id)->get(); 
                
                if ($result->isNotEmpty()) {
                    $attachmentData = $result[0];
                    $public_path = $attachmentData->filepath . '/' . $attachmentData->filename;

                    if(File::exists($public_path)) {
                        unlink($public_path);
                        Attachments::where('id', $id)->delete();
                    }

                }
            }                
        }
            DB:: commit();
            return response()->json(['message' => 'Your data has been draft'], 200);
    
        }catch(\Exception $e){
            DB:: rollback();
            return response()->json($e, 500);
        }

    }


    
    public function getReDrafts($id,$frmName,$companyId,$loggedUserId)
    {
        $reMain = DB::table('accounting.reimbursement_request as r')
                ->join('general.users as g', 'r.REPORTING_MANAGER', '=', 'g.UserFull_name')
                ->where('r.DRAFT_NUM', '!=', '')
                ->where('r.DRAFT_IDEN', 1)
                ->where('r.UID', $loggedUserId)
                ->where('r.id', $id)
                ->where('r.TITLEID', $companyId)
                ->select('r.*', 'g.id as reportingManagerID')
                ->get();

        $attachmentsData = Attachments::where(['REQID' => $id, 'INITID' => $loggedUserId, 'formName' => $frmName])->get();

        return response()->json([
            'draftDetails' => $reMain[0],
            'draftAttachments' => $attachmentsData
        ], 200);
    }





}
