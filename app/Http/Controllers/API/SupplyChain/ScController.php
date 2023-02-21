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

    public function getMaterials(Request $request)
    {
        $filtered_data = json_decode($request->filtered_data, true);
        // log::debug($filtered_data);
        // $filtered_data['actual_search'];
        // {is_SearchSubmitted: true, actual_search: 'asdasd', is_filtered: false, filtered_data: ''}

        if (filter_var($filtered_data['is_SearchSubmitted'], FILTER_VALIDATE_BOOLEAN) && filter_var($filtered_data['is_filtered'], FILTER_VALIDATE_BOOLEAN)) {
            // $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData('%', '".$request->companyId."', '".$filtered_data['actual_search']."')");

            if ($filtered_data['filtered_data']['type'] === 'category') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_category('%', '" . $request->companyId . "', '" . $filtered_data['actual_search'] . "','" . $filtered_data['filtered_data']['category_id'] . "')");
            } else if ($filtered_data['filtered_data']['type'] === 'subcategory') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_subcategory('%', '" . $request->companyId . "', '" . $filtered_data['actual_search'] . "','" . $filtered_data['filtered_data']['scategory_id'] . "')");
            } else {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_brand('%', '" . $request->companyId . "', '" . $filtered_data['actual_search'] . "','" . $filtered_data['filtered_data']['brand_id'] . "')");
            }
            log::debug('1');
        } elseif (filter_var($filtered_data['is_SearchSubmitted'], FILTER_VALIDATE_BOOLEAN)) {
            $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData('%', '" . $request->companyId . "', '" . $filtered_data['actual_search'] . "')");
            log::debug('2');
        } elseif (filter_var($filtered_data['is_filtered'], FILTER_VALIDATE_BOOLEAN)) {
            // $posts = DB::select("call procurement.llard_load_item_request_web_api('%', '".$request->companyId."')");
            if ($filtered_data['filtered_data']['type'] === 'category') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_category('%', '" . $request->companyId . "', '" . $filtered_data['filtered_data']['category_id'] . "')");
            } else if ($filtered_data['filtered_data']['type'] === 'subcategory') {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_subcategory('%', '" . $request->companyId . "', '" . $filtered_data['filtered_data']['scategory_id'] . "')");
            } else {
                $posts = DB::select("call procurement.llard_load_item_request_web_api_brand_filter('%', '" . $request->companyId . "', '" . $filtered_data['filtered_data']['brand_id'] . "')");
            }
            log::debug('3');
        } else {
            $posts = DB::select("call procurement.llard_load_item_request_web_api('%', '" . $request->companyId . "')");
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
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());

        return response()->json($paginatedItems, 200);
    }

    public function searchMaterials(Request $request)
    {
        $posts = DB::select("call procurement.llard_load_item_request_web_api_searchData_brand('%', '" . $request->companyId . "', '" . $request->keyword . "','" . $request->brand . "')");
        return response()->json($posts);
    }



    // Filters
    public function getCategory()
    {
        $categories = DB::table('procurement.setup_group_type')
            ->where('status', '=', 'Active')
            ->select('id', 'type as name')
            ->get();
        return response()->json($categories);
    }

    public function getUom()
    {
        $uom = DB::select("SELECT a.`base1_uomid` AS 'uom_id', a.`base1_uom` AS 'uom_name' FROM procurement.`setup_group_detail` a WHERE a.`base1_uomid` != 0 GROUP BY a.`base1_uomid`");
        return response()->json($uom);
    }

    public function getSubCategory()
    {
        $subCategories = DB::table('procurement.setup_group')
            ->where('status', '=', 'Active')
            ->select('group_id as id', 'group_description as name', 'group_type as category_id')
            ->get();
        return response()->json($subCategories);
    }

    public function getBrand()
    {
        $brands = DB::table('procurement.setup_brand')
            ->whereIn('status',  ['Active', 'ACTIVE'])
            ->select('id', 'description as name')
            ->get();
        return response()->json($brands);
    }

    public function purchase(Request $request)
    {
        DB::beginTransaction();
        try {

            log::debug($request);
            $guid = $this->getGuid();
            $mainRef = $this->getMainRef($request->trans_type);
            $soid = DB::table('general.setup_project as sp')->select('sp.SOID')->where('sp.project_id', $request->costid)->get();

            $SOID = 0;
            $SONUM = 0;
            $actualSignData = [];

            if (count($soid) == 0) {
                $SOID = 0;
                $SONUM = 0;
            } else {
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
                "project_id" => $request->costid,
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
                "webapp" => 1,
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
                    'itemid' => $value->cart_group_detail_id,
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
            // if ($request->type_category == "Material Request - Project") {
            //     for ($x = 0; $x < 6; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'FrmMRF',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'mrf_approve.php',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'For Project Manager Approval';
            //         $actualSignData[1]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
            //         $actualSignData[5]['Max_approverCount'] = '6';
            //     }
            // } else if ($request->type_category == "Material Request - Delivery") {
            //     for ($x = 0; $x < 6; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'FrmMRF',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'mrf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'For Sales Head Approval';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[1]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
            //         $actualSignData[5]['Max_approverCount'] = '6';
            //     }
            // } else if ($request->type_category == "Material Request - Demo" || $request->type_category == "Material Request - POC") {
            //     for ($x = 0; $x < 5; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'FrmMRF',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'mrf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
            //         $actualSignData[1]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '5';
            //     }
            // } else if ($request->type_category == "Asset Request - Delivery") {
            //     for ($x = 0; $x < 7; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'frmARF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'arf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
            //         $actualSignData[1]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
            //         $actualSignData[5]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[6]['ORDERS'] == 6) {
            //         $actualSignData[6]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
            //         $actualSignData[6]['Max_approverCount'] = '7';
            //     }
            // } else if ($request->type_category == "Asset Request - Demo" || $request->type_category == "Asset Request - POC") {
            //     for ($x = 0; $x < 6; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'frmARF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'arf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'Sales Head';
            //         $actualSignData[1]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
            //         $actualSignData[5]['Max_approverCount'] = '6';
            //     }
            // } else if ($request->type_category == "Asset Request - Internal") {
            //     for ($x = 0; $x < 6; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'frmARF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'arf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Management';
            //         $actualSignData[1]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
            //         $actualSignData[5]['Max_approverCount'] = '6';
            //     }
            // } else if ($request->type_category == "Asset Request - Project") {
            //     for ($x = 0; $x < 7; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'frmARF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'arf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'For Project Manager Approval';
            //         $actualSignData[1]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Asset Return Acknowledgement';
            //         $actualSignData[5]['Max_approverCount'] = '7';
            //     }
            //     if ($actualSignData[6]['ORDERS'] == 6) {
            //         $actualSignData[6]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
            //         $actualSignData[6]['Max_approverCount'] = '7';
            //     }
            // } else if ($request->type_category == "Supplies Request - Internal") {
            //     for ($x = 0; $x < 5; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'FrmSURF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'surf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Management';
            //         $actualSignData[1]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '5';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '5';
            //     }
            // } else if ($request->type_category == "Supplies Request - Project") {
            //     for ($x = 0; $x < 6; $x++) {
            //         $actualSignData[] =
            //             [
            //                 'PROCESSID'         => $m_id,
            //                 'USER_GRP_IND'      => 'Reporting Manager',
            //                 'FRM_NAME'          => $request->type_category,
            //                 'FRM_CLASS'         => 'FrmSURF1',
            //                 'REMARKS'           => $request->remarks,
            //                 'STATUS'            => 'Not Started',
            //                 'TS'                => now(),
            //                 'DUEDATE'           => date_create($request->planned_delivery_date),
            //                 'ORDERS'            => $x,
            //                 'REFERENCE'         => $mainRef,
            //                 'PODATE'            => date_create($request->requested_date),
            //                 'DATE'              => date_create($request->requested_date),
            //                 'INITID'            => $request->userid,
            //                 'FNAME'             => $request->user_fname,
            //                 'LNAME'             => $request->user_lname,
            //                 'DEPARTMENT'        => $request->user_department,
            //                 'RM_ID'             => $request->rmid,
            //                 'REPORTING_MANAGER' => $request->reporting_manager,
            //                 'PROJECTID'         => $request->costid,
            //                 'PROJECT'           => $request->costname,
            //                 'COMPID'            => $request->companyId,
            //                 'COMPANY'           => $request->user_company,
            //                 'TYPE'              => $request->type_category,
            //                 'CLIENTID'          => $request->clientid,
            //                 'CLIENTNAME'        => $request->clientname,
            //                 'Max_approverCount' => '5',
            //                 'DoneApproving'     => '0',
            //                 'WebpageLink'       => 'surf_approve.php',
            //                 'ApprovedRemarks'   => '',
            //                 'Payee'             => '',
            //                 'Amount'            => '',
            //             ];
            //     }
            //     if ($actualSignData[0]['ORDERS'] == 0) {
            //         $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            //         $actualSignData[0]['STATUS']       = 'In Progress';
            //         $actualSignData[0]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[1]['ORDERS'] == 1) {
            //         $actualSignData[1]['USER_GRP_IND'] = 'Project Manager';
            //         $actualSignData[1]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[2]['ORDERS'] == 2) {
            //         $actualSignData[2]['USER_GRP_IND'] = 'Acknowledge by Material Management';
            //         $actualSignData[2]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[3]['ORDERS'] == 3) {
            //         $actualSignData[3]['USER_GRP_IND'] = 'For Input of Material Management';
            //         $actualSignData[3]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[4]['ORDERS'] == 4) {
            //         $actualSignData[4]['USER_GRP_IND'] = 'Initiator';
            //         $actualSignData[4]['Max_approverCount'] = '6';
            //     }
            //     if ($actualSignData[5]['ORDERS'] == 5) {
            //         $actualSignData[5]['USER_GRP_IND'] = 'Acknowledge by Accounting Department';
            //         $actualSignData[5]['Max_approverCount'] = '6';
            //     }
            // }

            // ActualSign::insert($actualSignData);

            $request->request->add(['purpose' => $request->remarks]);
            $request->request->add(['dateNeeded' => $request->planned_delivery_date]);
            $request->request->add(['loggedUserId' => $request->userid]);
            $request->request->add(['loggedUserFirstName' => $request->user_fname]);
            $request->request->add(['loggedUserLastName' => $request->user_lname]);
            $request->request->add(['loggedUserDepartment' => $request->user_department]);
            $request->request->add(['reportingManagerId' => $request->rmid]);
            $request->request->add(['reportingManagerName' => $request->reporting_manager]);
            $request->request->add(['projectId' => $request->costid]);
            $request->request->add(['projectName' => $request->costname]);
            $request->request->add(['companyName' => $request->user_company]);
            $request->request->add(['clientId' => $request->clientid]);
            $request->request->add(['clientName' => $request->clientname]);
            $request->request->add(['amount' => 0]);




            $isInserted = $this->insertActualSign($request, $m_id, $request->type_category, $mainRef);
            if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');

            // insert in general.attachments
            $request->request->add(['processId' => $m_id]);
            $request->request->add(['referenceNumber' => $mainRef]);
            $request->request->add(['class' => $request->trans_type]);
            $request->request->add(['form' => $request->type_category]);



            $this->addAttachments($request);

            DB::commit();

            return response()->json('Your item has been purchased!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            log::debug($e);
            // throw error response
            return response()->json($e, 500);
        }
    }

    public function getAttachmentsBySoid($soid)
    {
        $res = DB::select("SELECT * FROM general.`attachments` a WHERE a.`REQID` = '" . $soid . "' AND a.`formName` = (SELECT b.`purpose` FROM sales_order.`sales_orders` b WHERE b.`id` = a.`REQID`)");
        return response()->json($res);
    }

    public function getMrf($req_id, $companyid, $frmname)
    {
        try {
            // get requisition main
            $req_main = DB::table('procurement.requisition_main as a')
                // ->join('sales_order.sales_orders as b', 'a.so_id', '=', 'b.id')
                ->where('a.requisition_id', $req_id)
                ->where('a.title_id', $companyid)
                // ->select('a.*', 'b.purpose')
                ->get();

            $soid           = $req_main[0]->so_id;
            // $frmname        = $req_main[0]->purpose;
            $userid         = $req_main[0]->userid;
            $rm_id          = $req_main[0]->rmid;
            $short_text     = $req_main[0]->short_text;
            $date_requested = $req_main[0]->tstamp;
            $project_id     = $req_main[0]->costid;
            $project_name   = $req_main[0]->costname;
            $client_id      = $req_main[0]->clientid;
            $client_name    = $req_main[0]->clientname;
            $remarks        = $req_main[0]->remarks;
            $deadline_date  = $req_main[0]->deadline_date;
            $requisition_id = $req_main[0]->requisition_id;
            $requisition_no = $req_main[0]->requisition_no;
            $title_id       = $req_main[0]->title_id;

            $user = DB::table('general.users as a')
            ->where('a.id', $userid)
            ->select('a.id', 'a.UserFull_name as fullname')
            ->get();

            $user_fullname = $user[0]->fullname;

            // get requisition details
            $req_details = DB::table('procurement.requisition_details as a')->where('a.requisition_id', $req_id)->get();

            // Get general.acutal_sign
            $general_actualsign = DB::table('general.actual_sign as a')
            ->where('a.PROCESSID', $requisition_id)
            ->where('a.REFERENCE', $requisition_no)
            ->where('a.COMPID', $title_id)
            ->get();
            $reference          = $general_actualsign[0]->REFERENCE;
            $department         = $general_actualsign[0]->DEPARTMENT;
            $rm_name            = $general_actualsign[0]->REPORTING_MANAGER;
            $frm_name           = $general_actualsign[0]->FRM_NAME;

            // is Done Approving

            $response = DB::table('general.actual_sign as a')->select('a.STATUS')
            ->where('a.PROCESSID', $requisition_id)
            ->where('a.REFERENCE', $requisition_no)
            ->where('a.COMPID', $title_id)
            ->orderBy('a.ID', 'desc')
            ->limit(1)
            ->get();
            $actual_status = $response[0]->STATUS;
            $done_approving = ($actual_status === 'In Progress' ? true : false);

            // Get sales order form name
            $res = DB::table('sales_order.sales_orders')->where('id', $soid)->select('purpose')->limit(1)->get();
            $frmnme = empty($res[0]->purpose)? null : $res[0]->purpose;
            

            // Get Attachments of link sales order
            $attachmentsSOF = DB::table('general.attachments as a')
            ->select('a.id', 'a.INITID', 'a.REQID', 'a.filename', 'a.filepath', 'a.fileExtension', 'a.originalFilename', 'a.newFilename', 'a.formName', 'a.fileDestination', 'a.mimeType', 'a.created_at', 'a.updated_at')
            ->where('a.REQID', $soid)
            ->where('a.formName', $frmnme)
            ->get();
            // log::debug($frmnme);

            // Get Attachments of this MRF
            $attachmentsMRF = DB::table('general.attachments as a')
            ->select('a.id', 'a.INITID', 'a.REQID', 'a.filename', 'a.filepath', 'a.fileExtension', 'a.originalFilename', 'a.newFilename', 'a.formName', 'a.fileDestination', 'a.mimeType', 'a.created_at', 'a.updated_at')
            ->where('a.REQID', $requisition_id)
            ->where('a.formName', $frm_name)
            ->get();
            

            $mrf  = array('Material Request - Project', 'Material Request - Delivery', 'Material Request - Demo', 'Material Request - POC',);
            $arf  = array('Asset Request - Project', 'Asset Request - Delivery', 'Asset Request - Demo', 'Asset Request - POC', 'Asset Request - Internal',);
            $surf = array('Supplies Request - Project', 'Supplies Request - Internal',);

            $main_class = null;
            if (in_array($frm_name, $mrf, TRUE)) {
                $main_class = 'Material Request';
            } else if (in_array($frm_name, $arf, TRUE)) {
                $main_class = 'Asset Request';
            } else if (in_array($frm_name, $surf, TRUE)) {
                $main_class = 'Supplies Request';
            }



            $requisition_details = DB::table('procurement.requisition_details AS c')
                ->join('procurement.setup_group_detail AS s', 'c.itemid', '=', 's.group_detail_id')
                ->join('procurement.setup_brand AS b', 'b.id', '=', 's.brand_id')
                ->join('procurement.setup_group_type AS cat', 'cat.id', '=', 's.group_id')
                ->join('procurement.setup_group AS subcat', 'subcat.group_id', '=', 's.category_id')
                ->where('c.requisition_id', $req_id)

                ->select('c.req_qty as order_qty', 's.description as description', 's.item_code as item_code', 's.specification as specification', 's.SKU as sku', 'cat.id as category_id', 'cat.type as category_name', 'subcat.group_id as sub_category_id', 'subcat.group_description as sub_category_name', 'b.id as brand_id', 'b.description as brand_name', 'c.notes', 'c.date_delivered', 'c.req_dtls_id')
                ->get();


            $isAcknowledgeByMM = DB::table('general.actual_sign as a')
                ->where('a.USER_GRP_IND', 'Acknowledge by Material Management')
                ->where('a.STATUS', 'In Progress')
                ->where('a.COMPID', $companyid)
                ->where('a.PROCESSID', $req_id)
                ->where('a.FRM_NAME', $frmname)
                ->select('a.ID')
                ->exists() ? true : false;




            return response()->json([
                'status' => true,
                'user'   =>
                [
                    'id'         => $userid,
                    'fullname'   => $user_fullname,
                    'department' => $department,
                    'rm_id'      => $rm_id,
                    'rm_name'    => $rm_name,
                ],

                'request' => [
                    'mrf_number'              => $reference,
                    'mrf_short_text'          => $short_text,
                    'date_requested'          => $date_requested,
                    'planned_delivery_date'   => $deadline_date,
                    'actual_delivery_date'    => $deadline_date,
                    'project_id'              => $project_id,
                    'project_name'            => $project_name,
                    'client_id'               => $client_id,
                    'client_name'             => $client_name,
                    'materials_request_class' => $main_class,
                    'materials_request_type'  => $frm_name,
                    'remarks'                 => $remarks,
                    'isAcknowledgeByMM'       => $isAcknowledgeByMM,
                    'done_approving'          => $done_approving,
                    'requisition_details'     => $requisition_details
                ],


                'req_main'       => $req_main,
                'req_details'    => $req_details,
                'attachmentsSOF'  => $attachmentsSOF,
                'attachmentsMRF' => $attachmentsMRF,
                'gen_actualsign' => $general_actualsign

            ], 200);
        } catch (\Throwable $th) {
            throw $th;

            log::debug("error throw");
            log::debug($th);

            return response()->json([
                'status'  => false,
                'error'   => $th,
                'message' => 'Server error! Please report to administrator!'
            ], 500);
        }
    }

    public function isAcnowledgeByMM($companyid, $req_id, $frmname) {

    // if (DB::table('general.actual_sign as a')
    // ->where('a.USER_GRP_IND', 'Acknowledge by Material Management')
    // ->where('a.STATUS', 'In Progress')
    // ->where('a.COMPID', $companyid)
    // ->where('a.PROCESSID', $req_id)
    // ->where('a.FRM_NAME', $frmname)
    // ->select('a.ID')
    // ->exists()
    // ) 
    // {
    //     $isAcknowledgeByMM = true;
    // } 
    // else {
    //     $isAcknowledgeByMM = false;
    // }


    $isAcknowledgeByMM = DB::table('general.actual_sign as a')
    ->where('a.USER_GRP_IND', 'Acknowledge by Material Management')
    ->where('a.STATUS', 'In Progress')
    ->where('a.COMPID', $companyid)
    ->where('a.PROCESSID', $req_id)
    ->where('a.FRM_NAME', $frmname)
    ->select('a.ID')
    ->exists() ? true : false;

    return response()->json([
        'status'  => true,
        'data'   => $isAcknowledgeByMM,
        'message' => 'Query Success'
    ], 200);

    }

    public function mrfChangeStatus(Request $request)
    {

        log::debug($request);
        DB::beginTransaction();
        try {
            log::debug('try');

            $status = null;
            if ($request->frmstatus === 'withdrawn') {
                $status = 'Withdrawn';
            } else if ($request->frmstatus === 'rejected') {
                $status = 'Rejected';
            } else if ($request->frmstatus === 'clarify') {
                $status = 'For Clarification';
            } else if ($request->frmstatus === 'approved') {
                $status = 'Completed';
            }

            $request_status = array('withdrawn', 'rejected', 'clarify');


            // Change Status of withdrawn, Rejected and clarify
            if (in_array($request->frmstatus, $request_status, TRUE)) {
                log::debug('if');
                DB::table('procurement.requisition_main')
                    ->where('requisition_id', $request->processId)
                    ->update(['status' => $status]);

                DB::table('procurement.requisition_details')
                    ->where('requisition_id', $request->processId)
                    ->update(['status' => $status]);

                DB::table('general.actual_sign')
                    ->where('STATUS', 'In Progress')
                    ->where('PROCESSID', $request->processId)
                    ->where('FRM_NAME', $request->frmName)
                    ->where('COMPID', $request->companyId)
                    ->update(['STATUS' => $status, 'SIGNDATETIME' => now(), 'UID_SIGN' => $request->loggedUserId, 'ApprovedRemarks' => $request->withdrawRemarks, 'webapp' => 1]);

            // Change status of approve
            } else {
                log::debug('else');

                // Change status to done approving
                if (filter_var($request->done_approving, FILTER_VALIDATE_BOOLEAN)) {
                    // Update delivery date and notes of requested item when in - For Input of Material Management
                    if(filter_var($request->input, FILTER_VALIDATE_BOOLEAN)){
                        log::debug('else if- Done Approving');

                        $requested_items = json_decode($request->requested_items, true);
                        foreach($requested_items as $item){

                            if(empty($item['date_delivered']) || is_null($item['date_delivered'])){
                                $date_delivered = null;
                            } else {
                                $date_delivered = date_create($item['date_delivered']);
                                $date_delivered = date_format($date_delivered, 'Y-m-d');
                            }
                            DB:: table('procurement.requisition_details')
                                ->where('req_dtls_id', $item['req_dtls_id'])
                                ->update(['date_delivered' => $date_delivered,'notes' => $item['notes']]);
                        }
                    }

                    DB::table('procurement.requisition_main')
                        ->where('requisition_id', $request->processId)
                        ->update(['status' => $status]);

                    DB::table('procurement.requisition_details')
                        ->where('requisition_id', $request->processId)
                        ->update(['status' => $status]);

                    DB::table('general.actual_sign')
                        ->where('STATUS', 'In Progress')
                        ->where('PROCESSID', $request->processId)
                        ->where('FRM_NAME', $request->frmName)
                        ->where('COMPID', $request->companyId)
                        ->update(['DoneApproving' => 1, 'STATUS' => $status, 'SIGNDATETIME' => now(), 'UID_SIGN' => $request->loggedUserId, 'ApprovedRemarks' => $request->withdrawRemarks, 'webapp' => 1]);

                    // change to completed
                } else {
                    log::debug('else else- Done Approving');


                    // acknowledgement of MM true if status in general.actual_sign is in progress
                    if(filter_var($request->isAcknowledgeByMM, FILTER_VALIDATE_BOOLEAN)){
                        DB::table('procurement.requisition_main')
                            ->where('requisition_id', $request->processId)
                            ->update(['acknowledge' => 1]);
                    }

                    // Update delivery date and notes of requested item when in - For Input of Material Management
                    if(filter_var($request->input, FILTER_VALIDATE_BOOLEAN)){
                        $requested_items = json_decode($request->requested_items, true);
                        foreach($requested_items as $item){

                            if(empty($item['date_delivered']) || is_null($item['date_delivered'])){
                                $date_delivered = null;
                            } else {
                                $date_delivered = date_create($item['date_delivered']);
                                $date_delivered = date_format($date_delivered, 'Y-m-d');
                            }
                            DB:: table('procurement.requisition_details')
                                ->where('req_dtls_id', $item['req_dtls_id'])
                                ->update(['date_delivered' => $date_delivered,'notes' => $item['notes']]);
                        }
                    }

                    DB::table('general.actual_sign')
                        ->where('STATUS', 'In Progress')
                        ->where('PROCESSID', $request->processId)
                        ->where('FRM_NAME', $request->frmName)
                        ->where('COMPID', $request->companyId)
                        ->update(['STATUS' => $status, 'SIGNDATETIME' => now(), 'UID_SIGN' => $request->loggedUserId, 'ApprovedRemarks' => $request->withdrawRemarks, 'webapp' => 1]);

                    DB::table('general.actual_sign')
                        ->where('STATUS', 'Not Started')
                        ->where('PROCESSID', $request->processId)
                        ->where('FRM_NAME', $request->frmName)
                        ->where('COMPID', $request->companyId)
                        ->limit(1)
                        ->update(['STATUS' => 'In Progress']);
                }
            }


            DB::commit();
            return response()->json(['message' => 'Materials request has been' . ' ' . $status], 200);
        } catch (\Exception $e) {
            DB::rollback();
            log::debug('catch mrfWithdraw ' . $e);
            return response()->json($e, 500);
        }
    }
}
