<?php

namespace App\Http\Controllers\API\Accounting\RE;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\RE\ReExpenseSetup;
use App\Models\Accounting\RE\ReMain;
use App\Models\Accounting\RE\ReTranspoSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\General\ActualSign;
use App\Models\General\Attachments;


class ReController extends ApiController
{
    public function saveRE(Request $request)
    {

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

            // if ($request->hasFile('file')) {

            //     foreach ($request->file as $file) {
            //         $completeFileName = $file->getClientOriginalName();
            //         $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            //         $extension = $file->getClientOriginalExtension();
            //         $randomized = rand();
            //         $newFileName = str_replace(' ', '', $fileNameOnly) . '-' . $randomized . '' . time() . '.' . $extension;
            //         $reqRef = str_replace('-', '_', $reference);
            //         $mimeType = $file->getMimeType();
            //         // $myPath = "C:/Users/Iverson/Desktop/Attachments/".session('LoggedUser_CompanyID')."/RFP/".$rfpCode;

            //         // For moving the file
            //         $destinationPath = "public/Attachments/{$request->companyId}/RE/" . $reqRef;
            //         // For preview
            //         $storagePath = "storage/Attachments/{$request->companyId}/RE/" . $reqRef;
            //         $symPath = "public/Attachments/RE";
            //         $file->storeAs($destinationPath, $completeFileName);
            //         $fileDestination = $storagePath . '/' . $completeFileName;
            //         $image = base64_encode(file_get_contents($file));

            //         DB::table('repository.reimbursement')->insert([
            //             'REFID' => $reMain->id,
            //             'FileName' => $completeFileName,
            //             'IMG' => $image,
            //             'UID' => $request->loggedUserId,
            //             'Ext' => $extension
            //         ]);

            //         $attachmentsData = [
            //             'INITID' => $request->loggedUserId,
            //             'REQID' => $reMain->id,
            //             'filename' => $completeFileName,
            //             'filepath' => $storagePath,
            //             'fileExtension' => $extension,
            //             'newFilename' => $newFileName,
            //             'fileDestination' => $destinationPath,
            //             'mimeType' => $mimeType,
            //             'imageBytes' => $image,
            //             'formName' => 'Reimbursement Request',
            //             'created_at' => date('Y-m-d H:i:s')
            //         ];

            //         Attachments::insert($attachmentsData);
            //     }
            // }


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

        } else {
            return response()->json(['message' => 'Request Failed, Please complete required records!'], 202);
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
}
