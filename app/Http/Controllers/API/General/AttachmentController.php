<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\General\Attachments;

class AttachmentController extends ApiController
{
    public function getAttachments($id,$form){
        // $attachment = Attachments::firstWhere('REQID',$id)->where('formName','Request for Payment');

        $attachments = Attachments::where('REQID', $id)->where('formName',$form)->get();

        // $localFileName  = public_path().'/storage/Attachments/136/RFP/RFP_2021_0393/cylix.jpg';
        // $fileData = file_get_contents($localFileName);
        // $ImgfileEncode = base64_encode($fileData);

        // return response()->json(['data'=>$ImgfileEncode],200);
        return response()->json(['data'=>$attachments],200);


    }
}
