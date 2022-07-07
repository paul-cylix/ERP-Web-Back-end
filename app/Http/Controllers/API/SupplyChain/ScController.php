<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\SupplyChain\Cart;
use Illuminate\Support\Facades\Log;

class ScController extends ApiController
{

    public function getMaterials(Request $request){
        $filtered_data = json_decode($request->filtered_data, true);
        // log::debug($filtered_data);
        // $filtered_data['actual_search'];
        // {is_SearchSubmitted: true, actual_search: 'asdasd', is_filtered: false, filtered_data: ''}

        if (filter_var($filtered_data['is_SearchSubmitted'], FILTER_VALIDATE_BOOLEAN) && filter_var($filtered_data['is_filtered'], FILTER_VALIDATE_BOOLEAN)) {
            // $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData('%', '".$request->companyId."', '".$filtered_data['actual_search']."')");
          
            if ($filtered_data['filtered_data']['type'] === 'category'){
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_category('%', '".$request->companyId."', '".$filtered_data['actual_search']."','".$filtered_data['filtered_data']['category_id']."')");
            } else if ($filtered_data['filtered_data']['type'] === 'subcategory') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_subcategory('%', '".$request->companyId."', '".$filtered_data['actual_search']."','".$filtered_data['filtered_data']['scategory_id']."')");
            } else {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_brand('%', '".$request->companyId."', '".$filtered_data['actual_search']."','".$filtered_data['filtered_data']['brand_id']."')");
            }
            log::debug('1');
        
        } elseif (filter_var($filtered_data['is_SearchSubmitted'], FILTER_VALIDATE_BOOLEAN)){
            $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData('%', '".$request->companyId."', '".$filtered_data['actual_search']."')");
            log::debug('2');
        } elseif (filter_var($filtered_data['is_filtered'], FILTER_VALIDATE_BOOLEAN)){
            // $posts = DB::select("call procurement.llard_load_item_request_web_api('%', '".$request->companyId."')");
            if ($filtered_data['filtered_data']['type'] === 'category'){
                $posts = DB::select("call procurement.llard_load_item_request_web_api_category('%', '".$request->companyId."', '".$filtered_data['filtered_data']['category_id']."')");
            } else if ($filtered_data['filtered_data']['type'] === 'subcategory') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_subcategory('%', '".$request->companyId."', '".$filtered_data['filtered_data']['scategory_id']."')");
            } else {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_brand_filter('%', '".$request->companyId."', '".$filtered_data['filtered_data']['brand_id']."')");
            }
            log::debug('3');
        } else {
            $posts = DB::select("call procurement.llard_load_item_request_web_api('%', '".$request->companyId."')");
            log::debug('4');
        }





        // Log::debug($request);
        // log::debug($filtered_data['is_SearchSubmitted']);
        // log::debug($filtered_data->actual_search);
        
        // is_SearchSubmitted
        // actual_search
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($posts);
        $perPage = 10;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->values();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());

        return response()->json($paginatedItems, 200);

    }

    public function searchMaterials(Request $request){
        $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_brand('%', '".$request->companyId."', '".$request->keyword."','".$request->brand."')");

        return response()->json($posts);
    }



    // Filters
    public function getCategory(){
        $categories = DB::table('procurement.setup_group_type')
                ->where('status', '=', 'Active')
                ->select('id', 'type as name')
                ->get();
        return response()->json($categories);
    }

    public function getUom(){
        $uom = DB::select("SELECT a.`base1_uomid` AS 'uom_id', a.`base1_uom` AS 'uom_name' FROM procurement.`setup_group_detail` a WHERE a.`base1_uomid` != 0 GROUP BY a.`base1_uomid`");
        return response()->json($uom);

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

            Cart::where('cart_id', $value->cart_id)->update(['cart_status' => 3]);
        }

        // insert in requisition_details 
        $result = DB::table('procurement.requisition_details')->insert($arrayCart);

        return response()->json('Your item has been purchased!' , 200);

    }
}
