<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScController extends ApiController
{
    public function getMaterials($companyId) {
        $listItems = DB::select("call procurement.llard_load_item_request('%', '".$companyId."')");
        return response()->json(['data' => $listItems], 200);
    }
}
