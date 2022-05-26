<?php

namespace App\Http\Controllers\API\Workflow;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListController extends ApiController
{
    public function getLists($companyId) { 
        $posts = DB::select("call general.Display_SOF_api('".$companyId."')");
        return response()->json(['data'=>$posts],200);
    }

}
