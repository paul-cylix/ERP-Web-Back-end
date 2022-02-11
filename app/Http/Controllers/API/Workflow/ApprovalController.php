<?php

namespace App\Http\Controllers\API\Workflow;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\General\Attachments;

class ApprovalController extends ApiController
{
    public function getApprovals($loggedUserId, $companyId) { 
        $posts = DB::select("call general.Display_Approver_Company_web_api('%', '".$loggedUserId."', '".$companyId."', '2020-01-01', '2020-12-31', 'True')");
        return response()->json(['data'=>$posts],200);
    }

    // RFP Liquidation 
    public function rfpLiquidation(Request $request){


        DB::update("UPDATE general.`actual_sign` SET `webapp` = '1', `status` = 'Completed', `UID_SIGN` = '".$request->loggedUserId."', `SIGNDATETIME` = NOW(), `ApprovedRemarks` = '" .$request->remarks. "' WHERE `status` = 'In Progress' AND PROCESSID = '".$request->rfpid."' AND `FRM_NAME` = '".$request->form."' AND `COMPID` = '".$request->companyId."' ;");
        DB::update("UPDATE general.`actual_sign` SET `status` = 'In Progress' WHERE `status` = 'Not Started' AND PROCESSID = '".$request->rfpid."' AND `FRM_CLASS` = 'REQUESTFORPAYMENT' AND `COMPID` = '".$request->companyId."' LIMIT 1;");

        // Insert Liquidation
        $liquidationDataTable = $request->liquidation;
        $liquidationDataTable = json_decode($liquidationDataTable,true);

        for($i = 0; $i <count($liquidationDataTable); $i++) {
            $liqdata[] = [
                'RFPID' =>$request->rfpid,
                'trans_date'=>$liquidationDataTable[$i]['trans_date'],
                'client_id' =>$liquidationDataTable[$i]['client_id'],
                'client_name' =>$liquidationDataTable[$i]['client_name'],
                'description'=>$liquidationDataTable[$i]['description'],
                'date_' =>$liquidationDataTable[$i]['trans_date'],
                'Amount' =>floatval(str_replace(',', '', $liquidationDataTable[$i]['Amount'])),
                
                'currency' =>$liquidationDataTable[$i]['currency'],
                'expense_type'=>$liquidationDataTable[$i]['expense_type'],
            ];
        }

        DB::table('accounting.rfp_liquidation')->insert($liqdata);

        


        // remove existing attachments
        $removedFiles = $request->removedFiles;
        $removedFiles = json_decode($removedFiles,true);

        if(count($removedFiles) > 0){
            for($i = 0; $i <count($removedFiles); $i++) {
                DB::table('general.attachments')->where('id', $removedFiles[0]['id'])->delete();
    
                $public_path = public_path($removedFiles[0]['filepath'].'/'.$removedFiles[0]['filename']);
                unlink($public_path);
            }
        }


        // Additional attachments
    if($request->hasFile('file')){

        foreach($request->file as $file) {
            $completeFileName = $file->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $randomized = rand();
            $newFileName = str_replace(' ', '', $fileNameOnly).'-'.$randomized.''.time().'.'.$extension;
            $reqRef = str_replace('-', '_', $request->reference);
            $mimeType = $file->getMimeType();
            // $myPath = "C:/Users/Iverson/Desktop/Attachments/".session('LoggedUser_CompanyID')."/RFP/".$rfpCode;

            // For moving the file
            $destinationPath = "public/Attachments/{$request->companyId}/RFP/" . $reqRef;
            // For preview
            $storagePath = "storage/Attachments/{$request->companyId}/RFP/" . $reqRef;
            $symPath ="public/Attachments/RFP";
            $file->storeAs($destinationPath, $completeFileName);
            $fileDestination = $storagePath.'/'.$completeFileName;
            $image = base64_encode(file_get_contents($file));

            DB::table('repository.rfp')->insert([
                'REFID' => $request->rfpid,
                'FileName' => $completeFileName,
                'IMG' => $image,
                'UID' => $request->loggedUserId,
                'Ext' => $extension
            ]);

            $attachmentsData = [
                'INITID' => 136,
                'REQID' => $request->rfpid,
                'filename' => $completeFileName,
                'filepath' => $storagePath, 
                'fileExtension' => $extension,
                'newFilename' => $newFileName,
                'fileDestination'=>$destinationPath,
                'mimeType'=>$mimeType,
                'imageBytes'=>$image,
                'formName' => $request->form,
                'created_at' => date('Y-m-d H:i:s')
            ];

            Attachments::insert($attachmentsData); 
        }

    } 

        return response()->json(['message'=>'Request has been successfully submitted!']);

    }

}
