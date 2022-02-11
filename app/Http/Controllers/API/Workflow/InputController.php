<?php

namespace App\Http\Controllers\API\Workflow;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputController extends ApiController
{
    public function getInputs($loggedUserId, $companyId) { 
        $posts = DB::select("call general.Display_Input_Company_web_api('%', '".$loggedUserId."', '".$companyId."', '2020-01-01', '2020-12-31', 'True')");
        return response()->json(['data' => $posts], 200);
        
    }
}
