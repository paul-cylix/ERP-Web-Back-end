<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\SupplyChain\Cart;
use Illuminate\Support\Facades\Log;
use App\Models\General\ActualSign;

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
        DB::beginTransaction();
        try {
            $guid = $this->getGuid();
            $mainRef = $this->getMainRef($request->trans_type);
            $soid = DB::table('general.setup_project as sp')->select('sp.SOID')->where('sp.project_id', $request->costid)->get();

            $SOID = 0;
            $SONUM = 0;
            $actualSignData = [];
            
            if(count($soid) == 0) {
                $SOID = 0;
                $SONUM = 0;
            }
            else {
                $soNum = DB::table('sales_order.sales_orders as so')->select('so.soNum')->where('so.id', $soid[0]->SOID)->get();
                $SOID = $soid[0]->SOID;
                $SONUM = $soNum[0]->soNum;
            }

            // insert in requisition_main table
            $m_id = DB::table('procurement.requisition_main')->insertGetId([
                "requisition_no" => $mainRef,
                "draft_num" => '',
                "deadline_date" => $request->planned_delivery_date,
                "trans_date" =>   $request->requested_date,
                "trans_type" =>   $request->trans_type,
                "remarks" => $request->remarks,
                "status" => 'In Progress',
                "userid" => $request->userid,
                "costid" => $request->costid,
                "costname" => $request->costname,
                "clientid" => $request->clientid,
                "clientname" => $request->clientname,
                "short_text" => $request->short_text,
                "GUID" => $guid,
                "req_person_id" => $request->req_person_id,
                "rmid" => $request->rmid,
                "type" => $request->type,
                "title_id" => $request->companyId,
                "procss_type" => $request->procss_type,
                "so_id" => $SOID,
                "so_num" => $SONUM,
            ]);

            $cartData = DB::table('procurement.carts AS c')
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
                    'notes' => '',
                    "status" => 'In Progress',
                    "factor" => '',
                    "factorqty" => 0,
                    "PR_qty" => 0,
                    "resrve_qty" => 0,
                    "ActualQty" => 0,
                    "duomid" => $value->cart_uom_id,
                    "reservuomname" => '',
                ];

                array_push($arrayCart, $cartData);

                Cart::where('cart_id', $value->cart_id)->update(['cart_status' => 3]);
            }

            // insert in requisition_details 
            DB::table('procurement.requisition_details')->insert($arrayCart);

            // insert in general.actual_sign 
            if($request->type_category == "Material Request Project") {
                for ($x = 0; $x < 6; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '6';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'For Project Manager Approval';
                    $actualSignData[1]['Max_approverCount'] = '6';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '6';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '6';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '6';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
                    $actualSignData[5]['Max_approverCount'] = '6';
                }
            }
            else if($request->type_category == "Material Request Delivery") {
                for ($x = 0; $x < 6; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'For Sales Head Approval';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '6';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[1]['Max_approverCount'] = '6';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '6';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '6';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '6';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
                    $actualSignData[5]['Max_approverCount'] = '6';
                }
            }
            else if($request->type_category == "Material Request Demo" || $request->type_category == "Material Request POC") {
                for ($x = 0; $x < 5; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '5';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
                    $actualSignData[1]['Max_approverCount'] = '5';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '5';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '5';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '5';
                }
            }
            else if($request->type_category == "Asset Request Delivery") {
                for ($x = 0; $x < 7; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '7';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
                    $actualSignData[1]['Max_approverCount'] = '7';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '7';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '7';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '7';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
                    $actualSignData[5]['Max_approverCount'] = '7';
                }
                if ($actualSignData[6]['ORDERS'] == 6) {
                    $actualSignData[6]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
                    $actualSignData[6]['Max_approverCount'] = '7';
                }
            }
            else if($request->type_category == "Asset Request Demo" || $request->type_category == "Asset Request POC") {
                for ($x = 0; $x < 6; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '6';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
                    $actualSignData[1]['Max_approverCount'] = '6';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '6';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '6';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '6';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
                    $actualSignData[5]['Max_approverCount'] = '6';
                }
            }
            else if($request->type_category == "Asset Request Internal") {
                for ($x = 0; $x < 6; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '6';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Management';
                    $actualSignData[1]['Max_approverCount'] = '6';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '6';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '6';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '6';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
                    $actualSignData[5]['Max_approverCount'] = '6';
                }
            }
            else if($request->type_category == "Asset Request Project") {
                for ($x = 0; $x < 7; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '7';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'For Project Manager Approval';
                    $actualSignData[1]['Max_approverCount'] = '7';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '7';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '7';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '7';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement'; 
                    $actualSignData[5]['Max_approverCount'] = '7';
                }
                if ($actualSignData[6]['ORDERS'] == 6) {
                    $actualSignData[6]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
                    $actualSignData[6]['Max_approverCount'] = '7';
                }
            }
            else if($request->type_category == "Supplies Request Internal") {
                for ($x = 0; $x < 5; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '5';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Management';
                    $actualSignData[1]['Max_approverCount'] = '5';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '5';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '5';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '5';
                }
            }
            else if($request->type_category == "Supplies Request Project") {
                for ($x = 0; $x < 6; $x++) {
                    $actualSignData[] = 
                        [
                            'PROCESSID'         => $m_id,
                            'USER_GRP_IND'      => 'Reporting Manager',
                            'FRM_NAME'          => $request->type_category,
                            'FRM_CLASS'         => 'SUPPLYCHAINMRF',
                            'REMARKS'           => $request->remarks,
                            'STATUS'            => 'Not Started',
                            'TS'                => now(),
                            'DUEDATE'           => date_create($request->planned_delivery_date),
                            'ORDERS'            => $x,
                            'REFERENCE'         => $mainRef,
                            'PODATE'            => date_create($request->requested_date),
                            'DATE'              => date_create($request->requested_date),
                            'INITID'            => $request->userid,
                            'FNAME'             => $request->user_fname,
                            'LNAME'             => $request->user_lname,
                            'DEPARTMENT'        => $request->user_department,
                            'RM_ID'             => $request->rmid,
                            'REPORTING_MANAGER' => $request->reporting_manager,
                            'PROJECTID'         => $request->costid,
                            'PROJECT'           => $request->costname,
                            'COMPID'            => $request->companyId,
                            'COMPANY'           => $request->user_company,
                            'TYPE'              => $request->type_category,
                            'CLIENTID'          => $request->clientid,
                            'CLIENTNAME'        => $request->clientname,
                            'Max_approverCount' => '5',
                            'DoneApproving'     => '0',
                            'WebpageLink'       => 'mrf_approve.php',
                            'ApprovedRemarks'   => '',
                            'Payee'             => '',
                            'Amount'            => '',
                        ];
                }
                if ($actualSignData[0]['ORDERS'] == 0) {
                    $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
                    $actualSignData[0]['STATUS']       = 'In Progress';
                    $actualSignData[0]['Max_approverCount'] = '6';
                }
                if ($actualSignData[1]['ORDERS'] == 1) {
                    $actualSignData[1]['USER_GRP_IND'] = 'Project Manager';
                    $actualSignData[1]['Max_approverCount'] = '6';
                }
                if ($actualSignData[2]['ORDERS'] == 2) {
                    $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
                    $actualSignData[2]['Max_approverCount'] = '6';
                }
                if ($actualSignData[3]['ORDERS'] == 3) {
                    $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
                    $actualSignData[3]['Max_approverCount'] = '6';
                }
                if ($actualSignData[4]['ORDERS'] == 4) {
                    $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
                    $actualSignData[4]['Max_approverCount'] = '6';
                }
                if ($actualSignData[5]['ORDERS'] == 5) {
                    $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
                    $actualSignData[5]['Max_approverCount'] = '6';
                }
            }

            ActualSign:: insert($actualSignData);

            // insert in general.attachments
            $request->request->add(['processId' => $m_id]);
            $request->request->add(['referenceNumber' => $mainRef]);
            $request->request->add(['loggedUserId' => $request->userid]);
            $request->request->add(['class' => $request->trans_type]);
            $request->request->add(['form' => $request->type_category]);

            $this->addAttachments($request);
            
            DB::commit();

            return response()->json('Your item has been purchased!' , 200);

        } catch (\Exception $e) {
            DB::rollback();
        
            // throw error response
            return response()->json($e, 500);
        }

    }

    public function getAttachmentsBySoid($soid) {
        $res = DB::select("SELECT * FROM general.`attachments` a WHERE a.`REQID` = '".$soid."' AND a.`formName` = (SELECT b.`purpose` FROM sales_order.`sales_orders` b WHERE b.`id` = a.`REQID`)");
        return response()->json($res);
    }
}
