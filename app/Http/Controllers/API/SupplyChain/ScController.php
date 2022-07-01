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

    public function purchase(Request $request) {

        // insert in requisition_main table
        $m_id = DB::table('procurement.requisition_main')->insertGetId([
            "requisition_no" => $request->requisition_no,
            "draft_num" => '',
            "deadline_date" => $request->planned_delivery_date,
            "trans_date" =>   $request->requested_date,
            "trans_type" =>   $request->trans_type,
            "delivery_date" => $request->delivery_date,
            "remarks" => $request->remarks,
            "status" => 'In Progress',
            "userid" => $request->userid,
            "costid" => $request->costid,
            "costname" => $request->costname,
            "clientid" => $request->clientid,
            "clientname" => $request->clientname,
            "short_text" => $request->short_text
        ]);

        $cartData = DB::table('carts AS c')
        ->join('procurement.setup_group_detail AS s', 'c.cart_group_detail_id', '=', 's.group_detail_id')
        ->join('procurement.setup_brand AS b', 'b.id', '=', 's.brand_id')
        ->join('procurement.setup_group_type AS cat', 'cat.id', '=', 's.group_id')
        ->join('procurement.setup_group AS subcat', 'subcat.group_id', '=', 's.category_id')
        ->where('c.cart_userId', $request->userid)
        ->where('cart_companyId', $request->companyId)
        ->where('cart_status', 2)->get();

        $arrayCart = [];

        foreach ($cartData as $key => $value) {
            $cartData = [
                'requisition_id' => $m_id,
                'itemid' => $value->cart_id,
                'item_name' => $value->specification,
                'item_description' => $value->description,
                'uom' => $value->cart_uom_name,
                'uomid' => $value->cart_uom_id,
                'req_qty' => $value->cart_quantity,
                'costID' => $request->costid,
                'cost_name' => $request->costname,
                'client_id' => $request->clientid,
                'client_name' => $request->clientname,
                'date_needed' => $request->planned_delivery_date,
                'date_delivered' => $request->delivery_date,
                'notes' => '',
                "status" => 'In Progress',
            ];

            array_push($arrayCart, $cartData);
        }

        // insert in requisition_details 
        $result = DB::table('procurement.requisition_details')->insert($arrayCart);

        return response()->json('Your item has been purchased!' , 200);

    }
}
