<?php

namespace App\Http\Controllers\API\Workflow;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InProgressController extends ApiController
{
    public function getInProgress($loggedUserId, $companyId) { 
        $posts = DB::select("call general.Display_Inprogress_Company_web_api('%', '".$loggedUserId."','', '".$companyId."', '2020-01-01', '2020-12-31', 'True')");
        return response()->json(['data' => $posts], 200);
    }

}
