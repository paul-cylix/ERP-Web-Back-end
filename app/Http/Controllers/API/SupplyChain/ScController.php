<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ScController extends ApiController
{

    public function getMaterials(Request $request){
        $posts = DB::select("call procurement.llard_load_item_request_web_api('%', '".$request->companyId."')");

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($posts);
        $perPage = 10;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->values();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());

        return response()->json($paginatedItems, 200);

    }

    // Filters
    public function getCategory(){
        $categories = DB::table('procurement.setup_group_type')
                ->where('status', '=', 'Active')
                ->select('id', 'type as name')
                ->get();
        return response()->json($categories);
    }

    public function getSubCategory(){
        $subCategories = DB::table('procurement.setup_group')
            ->where('status', '=', 'Active')
            ->select('group_id as id', 'group_description as name', 'group_type as category_id')
            ->get();
        return response()->json($subCategories);
    }

    public function getBrand(){
        $brands = DB::table('procurement.setup_brand')
            ->whereIn('status',  ['Active','ACTIVE'])
            ->select('id', 'description as name')
            ->get();
        return response()->json($brands);
    }
}
