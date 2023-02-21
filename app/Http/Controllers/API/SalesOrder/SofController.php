<?php

namespace App\Http\Controllers\API\SalesOrder;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SofController extends ApiController
{
    // get all businesslist by company id in form its company name
    public function customerName($companyId){
        $businesslist = DB::select("SELECT a.`Business_Number`, a.`business_fullname`, a.`CLIENTCODE`, a.`term_type`, a.`PMName` FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = '".$companyId."' AND a.`Type` = 'CLIENT' ORDER BY a.`business_fullname` ASC");
        return response()->json($businesslist, 200);
    }

    // get customer by id
    public function customerData($companyId, $business_id){
        $businesslist = DB::select("SELECT a.`Business_Number`, a.`business_fullname`, a.`CLIENTCODE`, a.`term_type`, a.`PMName` FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = '".$companyId."' AND a.`Type` = 'CLIENT' AND a.`Business_Number` = '".$business_id."' ORDER BY a.`business_fullname` ASC");
        return response()->json($businesslist, 200);
    }
    
    // billing address
    public function getCustomerAddress($customerId){
        return response()->json(DB::select("call general.GetAddress_Client_New($customerId)"));
    }

    // contact number and contact name
    public function getContacts($customerId){
        return response()->json(DB::select("SELECT a.`ID`, a.`ContactName`, a.`Number` FROM general.`businesscontacts` a WHERE a.`BusinessNumber` = '".$customerId."' "));
    }
    
    // company code
    public function getSetupProject($customerId){
        return response()->json(DB::select("SELECT a.`project_no`, a.`project_id` FROM general.`setup_project` a WHERE a.`ClientID` = '".$customerId."' "));
    }

    public function getDelegates($customerId){
        return response()->json(DB::select("SELECT * FROM general.`delegates` a WHERE a.`ClientID` = '".$customerId."' "));
    }

    public function getSystemDetails(){
        return response()->json(DB::select("SELECT * FROM sales_order.`systems_type` a ORDER BY a.`id` DESC"));
    }

    public function getSelectedSystemDetails($id){
      
        return response()->json(DB::select("SELECT 
        IFNULL(
          (SELECT 
            'True' 
          FROM
            sales_order.`sales_order_system` b 
          WHERE b.sysID = a.id 
            AND b.soid = '".$id."'),
          'False'
        ) AS 'selected',
        type_name,
        a.`id` AS 'sysID' 
      FROM
        sales_order.`systems_type` a ORDER BY a.`id` DESC"));
    }


    public function getDocumentDetails(){
        return response()->json(DB::select("SELECT * FROM sales_order.`documentlist` a ORDER BY a.`ID` DESC" ));
    }

    public function getSelectedDocumentDetails($id){
        return response()->json(DB::select("SELECT 
        IFNULL(
          (SELECT 
            'True' 
          FROM
            sales_order.`sales_order_docs` b 
          WHERE b.DocID = a.ID 
            AND b.soid = '".$id."'),
          'False'
        ) AS 'selected',
        DocumentName,
        a.`ID` AS 'DocID' 
        FROM
        sales_order.`documentlist` a ORDER BY a.`ID` DESC
        
        "));
    }




    // Insert name of System Details or Document Details
    public function insertSofModalDetails(Request $request){

        if($request->modalTitle == 'System'){
            // check if System Document exist
            $response = DB::select("SELECT IFNULL((SELECT TRUE FROM sales_order.`systems_type` a WHERE a.`type_name` = '".$request->modalInputform."' LIMIT 1), FALSE) AS isExist");
            // Index the response
            $isSystemDetailsExist = $response[0]->isExist;
            //if record exist return
            if ($isSystemDetailsExist) {
                return response()->json('Record Already Exist!', 422);
            } else {
                DB::table('sales_order.systems_type')->insert([
                    'type_name' => $request->modalInputform,
                ]);
                return response()->json('Added Successfully!', 201);
            }
            
        }

        if($request->modalTitle == 'Document'){
            // check if System Document exist
            $response = DB::select("SELECT IFNULL((SELECT TRUE FROM sales_order.documentlist a WHERE a.`DocumentName` = '".$request->modalInputform."' LIMIT 1), FALSE) AS isExist");
            // Index the response
            $isDocumentDetailsExist = $response[0]->isExist;
            //if record exist return
            if ($isDocumentDetailsExist) {
                return response()->json('Record Already Exist!', 422);

            } else {
                DB::table('sales_order.documentlist')->insert([
                    'DocumentName' => $request->modalInputform,
                    'UID' => $request->loggedUserId,
                ]);
                return response()->json('Added Successfully!', 201);
            }

        }

        
    }

    // Check if project code exist
    public function checkIfProjectCodeExist(Request $request){
        return response()->json(DB::select("SELECT IFNULL((SELECT TRUE FROM general.`setup_project` a WHERE a.`project_no` = '".$request->projectCode."'), FALSE) AS isExist"));
    }

    // Check if project code exist
    public function checkIfProjectCodeExistSoid(Request $request){
        return response()->json(DB::select("SELECT IFNULL((SELECT TRUE FROM general.`setup_project` a WHERE a.`project_no` = '".$request->projectCode."' AND a.`SOID` != '".$request->processId."'), FALSE) AS isExist"));
    }

    public function saveSOF(Request $request){

        DB::beginTransaction();
        try{  

        log::debug($request);
                
        $guid   = $this->getGuid();
        $reqRef = $this->getSofRef($request->companyId);
        $request->request->add(['referenceNumber' => $reqRef]);
        $request->request->add(['class' => $request->softype]);


        // convert string date to legit date
        $projectStart = $this->dateFormatter($request->projectStart);
        $projectEnd   = $this->dateFormatter($request->projectEnd);
        $poDate       = $this->dateFormatter($request->poDate);

        // get project duation
        $projectDuration = $this->dateDifference($projectStart, $projectEnd);

        // condition if down payment percentage
        if(filter_var($request->downpaymentrequired, FILTER_VALIDATE_BOOLEAN)){
            $downPaymentPercentage = $request->downPaymentPercentage;
        } else {
            $downPaymentPercentage = 0;
        }

        // validate a boolean even if its string
        if(filter_var($request->invoicerequired, FILTER_VALIDATE_BOOLEAN)){
            $invoiceDateNeeded = date_create($request->invoiceDateNeeded);
            $invoiceDateNeeded = date_format($invoiceDateNeeded, 'Y-m-d');
        } else {
            $invoiceDateNeeded = null;
        }

        $projectCost = $this->amountFormatter($request->projectCost);
        
        // convert 'DLV' to 'Sales Order - Delivery' & etc.
        $softype     = $this->sofTypeConvert($request->softype);
        $softypeName = $softype[0];
        $sofCount    = $softype[1];

        // insert sof type to the request
        $request->request->add(['form' => $softypeName]);

        // insert date to setup_projet
        $setupProject_ID = DB::table('general.setup_project')->insertGetId([
            'project'             => 'Project Site',
            'project_name'        => $request->projectName,
            'project_shorttext'   => $request->projectShortText,
            'project_type'        => 'Project Site',
            'project_location'    => $request->deliveryAddress,
            'project_remarks'     => $request->scopeOfWork,
            'date_saved'          => now(),
            'title_id'            => $request->companyId,
            'project_no'          => $request->projectCode,
            'project_amount'      => $projectCost,
            'project_duration'    => $projectDuration,
            'project_effectivity' => $projectStart,
            'project_expiry'      => $projectEnd,
            'status'              => 'ACTIVE',
            'Main_office_id'      => $request->companyId,
            'OfficeAlias_Code'    => '',
            'OfficeAlias'         => '',
            'fax'                 => '225',
            'PROJECT_TYPES'       => '',
            'DeptHead'            => '0',
            'Coordinator'         => '0',
            'ClientID'            => $request->clientID,
            'total_cost'          => '0',
            'GID'                 => '0',
            'ProjectStatus'       => 'On-Going',
            'imported_from_excel' => '0',
            'SOID'                => null,                         //wala pa
            'last_edit_by'        => '0',
            'branch_name'         => '',
            'IncludeEmail'        => '0'
        ]);

        // insert data to sales_orders
        $salesOrders_ID = DB::table('sales_order.sales_orders')->insertGetId([
            'titleid'             => $request->companyId,
            'DraftNum'            => '',
            'MAINID'              => '1',                                                                  // wala pa
            'projID'              => $setupProject_ID,
            'pcode'               => $request->projectCode,
            'project'             => $request->projectName,
            'clientID'            => $request->clientID,
            'client'              => $request->client,
            'DraftStat'           => '0',
            'Contactid'           => $request->contactPerson,
            'Contact'             => $request->contactPersonName,
            'ContactNum'          => $request->contactNumber,
            'SubConID'            => '0',                                                                  //wala pa
            'SubConName'          => '',                                                                   // wala pa
            'sodate'              => now(),                                                                // wala pa
            'soNum'               => $reqRef,                                                              // wala pa
            'podate'              => $poDate,
            'poNum'               => $request->poNumber,
            'DeliveryAddress'     => $request->deliveryAddress,
            'BillTo'              => $request->billingAddress,
            'currency'            => $request->currency,
            'amount'              => $projectCost,
            'UID'                 => $request->loggedUserId,
            'fname'               => $request->loggedUserFirstName,
            'lname'               => $request->loggedUserLastName,
            'department'          => $request->loggedUserDepartment,
            'reportmanager'       => 'Chua, Konrad A.',
            'position'            => $request->loggedUserPosition,
            'remarks'             => $request->scopeOfWork,
            'Remarks2'            => $request->accountingRemarks,
            'purpose'             => $softypeName,
            'DateAndTimeNeeded'   => $projectEnd,
            'Terms'               => $request->paymentTerms,
            'GUID'                => $guid,
            'DeadLineDate'        => $projectEnd,
            'Status'              => 'In Progress',
            'TS'                  => now(),
            'DeliveryStatus'      => 'On-Going',                                                           //wala pa
            'Coordinator'         => '0',                                                                  //wala pa
            'IsInvoiceRequired'   => filter_var($request->invoicerequired, FILTER_VALIDATE_BOOLEAN),
            'invDate'             => $invoiceDateNeeded,
            'IsInvoiceReleased'   => '0',                                                                  //wala pa
            'IsBeginning'         => 'No',                                                                 //wala pa
            'InvoiceNumber'       => '',                                                                   // wala pa
            'imported_from_excel' => '0',                                                                  //wala pa
            'dp_required'         => filter_var($request->downpaymentrequired, FILTER_VALIDATE_BOOLEAN),
            'dp_percentage'       => $downPaymentPercentage,
            'project_shorttext'   => $request->projectShortText,
            'warranty'            => $request->warranty,
            'webapp'              => '1'
        ]);

        // insert sales order id to general.attachmets as processID
        $request->request->add(['processId' => $salesOrders_ID]);
        // update sales order id in setup_project
        DB:: update("UPDATE general.`setup_project` a SET a.`SOID` = '".$salesOrders_ID."' WHERE a.`project_id` = '".$setupProject_ID."' ;");
        // decode a parsed system name
        $systemnames = json_decode($request->systemname,true);
        // iterate the request system name
        foreach($systemnames as $systemname) {
            $systemnameArray[] = [
                'soid'                => $salesOrders_ID,
                'systemType'          => $systemname['type_name'],
                'sysID'               => $systemname['id'],
                'imported_from_excel' => '0'
            ];
        }
        // insert iterated array to sales_order_system
        DB:: table('sales_order.sales_order_system')->insert($systemnameArray);

        // decode a parsed document name
        $documentnames = json_decode($request->documentname,true);
        // iterate the request document name
        foreach($documentnames as $documentname) {
            $documentnameArray[] = [
                'SOID'                => $salesOrders_ID,
                'DocID'               => $documentname['ID'],
                'DocName'             => $documentname['DocumentName'],
                'imported_from_excel' => '0'
            ];
        }
        // insert iterated array to sales_order_docs
        DB:: table('sales_order.sales_order_docs')->insert($documentnameArray);
        
        
//         // Actual Sign
//         for ($x = 0; $x < $sofCount; $x++) {
//             $array[] = array(
// 'PROCESSID'         => $salesOrders_ID,
// 'USER_GRP_IND'      => '0',
// 'FRM_NAME'          => $softypeName,
// 'FRM_CLASS'         => 'SALES_ORDER_FRM',
// 'REMARKS'           => $request->scopeOfWork,
// 'STATUS'            => 'Not Started',
// 'DUEDATE'           => $projectEnd,
// 'ORDERS'            => $x,
// 'REFERENCE'         => $reqRef,
// 'PODATE'            => $poDate,
// 'PONUM'             => $request->poNumber,
// 'DATE'              => $projectEnd,
// 'INITID'            => $request->loggedUserId,
// 'FNAME'             => $request->loggedUserFirstName,
// 'LNAME'             => $request->loggedUserLastName,
// 'DEPARTMENT'        => $request->loggedUserDepartment,
// 'RM_ID'             => '0',
// 'REPORTING_MANAGER' => 'Chua, Konrad A.',
// 'PROJECTID'         => $setupProject_ID,
// 'PROJECT'           => $request->projectName,
// 'COMPID'            => $request->companyId,
// 'COMPANY'           => $request->companyName,
// 'TYPE'              => $softypeName,
// 'CLIENTID'          => $request->clientID,
// 'CLIENTNAME'        => $request->client,
// 'Max_approverCount' => $sofCount,
// 'DoneApproving'     => '0',
// 'WebpageLink'       => 'so_approve.php',
// 'Payee'             => 'N/A',
// 'Amount'            => $projectCost,
//             );
//             }

            $request->request->add(['purpose' => $request->scopeOfWork]);
            $request->request->add(['dateNeeded' => $request->projectEnd]);
            $request->request->add(['projectId' => $setupProject_ID]);
            $request->request->add(['clientId' => $request->clientID]);
            $request->request->add(['clientName' => $request->client]);
            $request->request->add(['amount' => $request->projectCost]);


            $isInserted = $this->insertActualSign($request, $salesOrders_ID, $softypeName, $reqRef);
            if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');

            $request->request->add(['processId' => $salesOrders_ID]);
            $request->request->add(['referenceNumber' => $reqRef]);
            $this->addAttachments($request);

            DB::commit();
            return response()->json($request, 201);
        }catch(\Exception $e){
            DB::rollback();
            log::debug($e);
        
            // throw error response
            return response()->json($e, 500);
        }

    }


    public function getSalesOrder($id){
        $result = DB::table('sales_order.sales_orders')->where('id', $id)->get();
        return response()->json($result, 200);
    }

    public function getSalesOrderSystem($id){
        $result = DB::table('sales_order.sales_order_system')->where('soid', $id)->get();
        return response()->json($result, 200);
    }

    public function getSalesOrderDocument($id){
        $result = DB::table('sales_order.sales_order_docs')->where('SOID', $id)->get();
        return response()->json($result, 200);
    }

    public function getCoordinators(){
        $result = DB::table('general.users')->select('id','UserName_User','UserFull_name','Employee_id')->where('id','!=','1')->where('status','ACTIVE')->orderBy('UserFull_name', 'asc')->get();
        return response()->json($result, 200);
    }

    public function getSelectedCoordinator($id){
        $result = DB::table('sales_order.projectcoordinator')->select('CoordID','CoordinatorName')->where('SOID',$id)->get();
        return response()->json($result, 200);
    }

    

   

    








    
   

    

    
}


