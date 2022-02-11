<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\BusinessList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessListController extends ApiController
{

    public function index()
    {
        $data = BusinessList::all();
        return $this->showAll($data);
    }

    public function showByCompId($compId){
       $data = DB::select("SELECT a.`Business_Number` AS businessNumber, a.`business_fullname` AS businessName FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = $compId AND a.`Type` = 'CLIENT' ORDER BY a.`business_fullname` ASC");
       return response()->json($data);
    }
}
