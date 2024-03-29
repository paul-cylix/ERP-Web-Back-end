<?php

namespace App\Http\Controllers\API\Workflow;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\General\Attachments;
use Illuminate\Support\Facades\Log;

class ApprovalController extends ApiController
{
    public function getApprovals($loggedUserId, $companyId) { 
        $posts = DB::select("call general.Display_Approver_Company_web_api('%', '".$loggedUserId."', '".$companyId."', '2020-01-01', '2020-12-31', 'True')");
        return response()->json(['data'=>$posts],200);
    }

    // RFP Liquidation 
    public function rfpLiquidation(Request $request){

        Log::debug($request);
        DB::beginTransaction();
        try{  

            DB::table('general.actual_sign as a')
                ->where('a.status', 'In Progress')
                ->where('a.PROCESSID', $request->processId)
                ->where('a.FRM_NAME', $request->form)
                ->where('a.COMPID', $request->companyId)
                ->update(['a.webapp' => 1, 'a.status' => 'Completed', 'a.UID_SIGN' => $request->loggedUserId, 'a.SIGNDATETIME' => now(), 'a.ApprovedRemarks' => $request->remarks]);
        
            DB::table('general.actual_sign as a')
                ->where('a.status', 'Not Started')
                ->where('a.PROCESSID', $request->processId)
                ->where('a.FRM_CLASS', 'REQUESTFORPAYMENT')
                ->where('a.COMPID', $request->companyId)
                ->take(1)
                ->update(['a.status' => 'In Progress']);

                

        // $this->approveActualSIgnApi($request);

        // DB::update("UPDATE general.`actual_sign` a SET a.`webapp` = '1', a.`status` = 'Completed', a.`UID_SIGN` = '".$request->loggedUserId."', a.`SIGNDATETIME` = NOW(), a.`ApprovedRemarks` = '" .$request->remarks. "' WHERE a.`status` = 'In Progress' AND a.`PROCESSID` = '".$request->processId."' AND a.`FRM_NAME` = '".$request->form."' AND a.`COMPID` = '".$request->companyId."' ;");
        // DB::update("UPDATE general.`actual_sign` a SET a.`status` = 'In Progress' WHERE a.`status` = 'Not Started' AND a.`PROCESSID` = '".$request->processId."' AND a.`FRM_CLASS` = 'REQUESTFORPAYMENT' AND a.`COMPID` = '".$request->companyId."' LIMIT 1;");
        // DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `status` = 'Completed', `UID_SIGN` = '".$request->loggedUserId."', `SIGNDATETIME` = NOW(), `ApprovedRemarks` = '" .$request->remarks. "' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->processId."' AND `FRM_NAME` = '".$request->form."' AND `COMPID` = '".$request->companyId."' ;");
        // DB::update("UPDATE general.`actual_sign` SET `status` = 'In Progress' WHERE `status` = 'Not Started' AND PROCESSID = '".$request->processId."' AND `FRM_CLASS` = 'REQUESTFORPAYMENT' AND `COMPID` = '".$request->companyId."' LIMIT 1;");

        // Insert Liquidation
        $liquidationDataTable = $request->liquidation;
        $liquidationDataTable = json_decode($liquidationDataTable,true);

        $liqdata = [];
        for($i = 0; $i <count($liquidationDataTable); $i++) {
            $liqdata[] = [
                'RFPID'       => $request->processId,
                'trans_date'  => $liquidationDataTable[$i]['trans_date'],
                'client_id'   => $liquidationDataTable[$i]['client_id'],
                'client_name' => $liquidationDataTable[$i]['client_name'],
                'description' => $liquidationDataTable[$i]['description'],
                'date_'       => $liquidationDataTable[$i]['trans_date'],
                'Amount'      => floatval(str_replace(',', '', $liquidationDataTable[$i]['Amount'])),
                
                'currency'     => $liquidationDataTable[$i]['currency'],
                'expense_type' => $liquidationDataTable[$i]['expense_type'],
            ];
        }

        DB:: table('accounting.rfp_liquidation')->insert($liqdata);

        


        // remove existing attachments
        $removedFiles = $request->removedFiles;
        $removedFiles = json_decode($removedFiles,true);

        if(count($removedFiles) > 0){
            for($i = 0; $i <count($removedFiles); $i++) {
                DB:: table('general.attachments')->where('id', $removedFiles[0]['id'])->delete();
    
                $public_path = public_path($removedFiles[0]['filepath'].'/'.$removedFiles[0]['filename']);
                unlink($public_path);
            }
        }


        // Additional attachments

        $this->insertAttachments($request,$request->processId, $request->reference);
    // if($request->hasFile('file')){

    //     foreach($request->file as $file) {
    //         $completeFileName = $file->getClientOriginalName();
    //         $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
    //         $extension = $file->getClientOriginalExtension();
    //         $randomized = rand();
    //         $newFileName = str_replace(' ', '', $fileNameOnly).'-'.$randomized.''.time().'.'.$extension;
    //         $reqRef = str_replace('-', '_', $request->reference);
    //         $mimeType = $file->getMimeType();
    //         // $myPath = "C:/Users/Iverson/Desktop/Attachments/".session('LoggedUser_CompanyID')."/RFP/".$rfpCode;

    //         // For moving the file
    //         $destinationPath = "public/Attachments/{$request->companyId}/RFP/" . $reqRef;
    //         // For preview
    //         $storagePath = "storage/Attachments/{$request->companyId}/RFP/" . $reqRef;
    //         $symPath ="public/Attachments/RFP";
    //         $file->storeAs($destinationPath, $completeFileName);
    //         $fileDestination = $storagePath.'/'.$completeFileName;
    //         $image = base64_encode(file_get_contents($file));

    //         DB::table('repository.rfp')->insert([
    //             'REFID' => $request->rfpid,
    //             'FileName' => $completeFileName,
    //             'IMG' => $image,
    //             'UID' => $request->loggedUserId,
    //             'Ext' => $extension
    //         ]);

    //         $attachmentsData = [
    //             'INITID' => 136,
    //             'REQID' => $request->rfpid,
    //             'filename' => $completeFileName,
    //             'filepath' => $storagePath, 
    //             'fileExtension' => $extension,
    //             'newFilename' => $newFileName,
    //             'fileDestination'=>$destinationPath,
    //             'mimeType'=>$mimeType,
    //             'imageBytes'=>$image,
    //             'formName' => $request->form,
    //             'created_at' => date('Y-m-d H:i:s')
    //         ];

    //         Attachments::insert($attachmentsData); 
    //     }

    // } 


        DB::commit();
        // return response()->json($request, 201);
        return response()->json(['message'=>'Request has been successfully submitted!']);
        // return response()->json(['message' => 'Cash Advance has been Successfully submitted'], 201);

    }catch(\Exception $e){
        DB::rollback();
        Log::debug($e);
    
        // throw error response
        return response()->json($e, 500);
    }

    }

}
