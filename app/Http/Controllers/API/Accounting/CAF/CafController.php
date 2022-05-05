<?php

namespace App\Http\Controllers\API\Accounting\CAF;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Accounting\Caf\CafMain;
use Illuminate\Support\Facades\DB;
use App\Models\General\ActualSign;

class CafController extends ApiController
{
    public function saveCaf(Request $request) {
        DB::beginTransaction();
        try{  
            $guid                        = $this->getGuid();
            $reqRef                      = $this->getCafRef($request->companyId);
            $cafMain                     = new CafMain();
            $cafMain->reference          = $reqRef;
            $cafMain->requested_date     = now();
            $cafMain->date_needed        = $request->dateNeeded;
            $cafMain->requested_amount   = floatval(str_replace(',', '', $request->requestedAmount));
            $cafMain->date_from          = $request->payableDateFrom;
            $cafMain->date_to            = $request->payableDateTo;
            $cafMain->employee_id        = $request->employeeId;
            $cafMain->employee_name      = $request->employeeName;
            $cafMain->installment_amount = floatval(str_replace(',', '', $request->installmentAmount));
            $cafMain->purpose            = $request->purpose;
            $cafMain->status             = 'In Progress';
            $cafMain->IsReleased         = 0;
            $cafMain->GUID               = $guid;
            $cafMain->ts                 = now();
            $cafMain->UID                = $request->loggedUserId;
            $cafMain->fname              = $request->loggedUserFirstName;
            $cafMain->lname              = $request->loggedUserLastName;
            $cafMain->position           = $request->loggedUserPosition;
            $cafMain->reporting_manager  = $request->reportingManagerName;
            $cafMain->department         = $request->loggedUserDepartment;
            $cafMain->TITLEID            = $request->companyId;
            $cafMain->webapp             = 1;
            $cafMain->save();

            
            for ($x = 0; $x < 5; $x++) {
                $actualSignData[] =
                    [
                        'PROCESSID' => $cafMain->id,
                        'USER_GRP_IND' => 'Approval of Management',
                        'FRM_NAME' => 'Cash Advance Request',
                        // 'TaskTitle' => '',
                        // 'NS' => '',
                        'FRM_CLASS' => 'frmCashAdvance_Request',
                        'REMARKS' => $request->purpose,
                        'STATUS' => 'Not Started',
                        // 'UID_SIGN' => '0',
                        'TS' => now(),
                        'DUEDATE' => date_create($request->dateNeeded),
                        // 'SIGNDATETIME' => '',
                        'ORDERS' => $x,
                        'REFERENCE' => $reqRef,
                        'PODATE' => date_create($request->dateNeeded),
                        // 'PONUM' => '',
                        'DATE' => date_create($request->dateNeeded),
                        'INITID' => $request->loggedUserId,
                        'FNAME' => $request->loggedUserFirstName,
                        'LNAME' => $request->loggedUserLastName,
                        // 'MI' => '',
                        'DEPARTMENT' => $request->loggedUserDepartment,
                        'RM_ID' => $request->reportingManagerId,
                        'REPORTING_MANAGER' => $request->reportingManagerName,
                        'PROJECTID' => '0',
                        'PROJECT' => $request->loggedUserDepartment,
                        'COMPID' => $request->companyId,
                        'COMPANY' => $request->companyName,
                        'TYPE' => 'Cash Advance Request',
                        'CLIENTID' => '0',
                        'CLIENTNAME' => $request->companyName,
                        // 'VENDORID' => '0',
                        // 'VENDORNAME' => '',
                        'Max_approverCount' => '5',
                        // 'GUID_GROUPS' => '',
                        'DoneApproving' => '0',
                        'WebpageLink' => 'ca_approve.php',
                        'ApprovedRemarks' => '',
                        // 'Payee' => $request->payeeName,
                        // 'CurrentSender' => '0',
                        // 'CurrentReceiver' => '0',
                        // 'NOTIFICATIONID' => '0',
                        // 'SENDTOID' => '0',
                        // 'NRN' => 'Imported',
                        // 'imported_from_excel' => '0',
                        'Amount' => floatval(str_replace(',', '', $request->requestedAmount)),
                        // 'webapp' => '1'
                    ];
            }
            if ($actualSignData[0]['ORDERS'] == 0) {
                $actualSignData[0]['USER_GRP_IND'] = 'Approval of Management';
                $actualSignData[0]['STATUS'] = 'In Progress';
            }
    
            if ($actualSignData[1]['ORDERS'] == 1) {
                $actualSignData[1]['USER_GRP_IND'] = 'Acknowledgement of Human Resource';
            }
    
            if ($actualSignData[2]['ORDERS'] == 2) {
                $actualSignData[2]['USER_GRP_IND'] = 'Approval of Accounting';
            }
    
            if ($actualSignData[3]['ORDERS'] == 3) {
                $actualSignData[3]['USER_GRP_IND'] = 'Releasing of Cash';
            }
    
            if ($actualSignData[4]['ORDERS'] == 4) {
                $actualSignData[4]['USER_GRP_IND'] = 'Acknowledgement of Initiator';
            }
    
            ActualSign::insert($actualSignData);
        

        DB::commit();
        // return response()->json($request, 201);
        return response()->json(['message' => 'Cash Advance has been Successfully submitted'], 201);

    }catch(\Exception $e){
        DB::rollback();
    
        // throw error response
        return response()->json($e, 500);
    }

    }


    public function getCaf($id){
        $cafData = CafMain::find($id);
        return response()->json($cafData, 200);
    }


    public function approveCafInput(Request $request){
        DB::beginTransaction();
        try{  

        $res = DB::select("SELECT IFNULL((SELECT MAX(SUBSTRING(a.`CVS` ,10)) FROM accounting.`lastcash` a WHERE a.`YR` = YEAR(NOW())), FALSE) +1 AS 'cvs'");
        $cvs = $res[0]->cvs;
        $cvs = str_pad($cvs, 4, "0", STR_PAD_LEFT); 
        $cvs = "CSH#" . date('Y') . "-" . $cvs;


        $res2 = DB::select("SELECT IFNULL((SELECT MAX(SUBSTRING(a.`control_no`, 9)) FROM humanresource.`amortized_deductions` a WHERE YEAR(a.`ts`) = YEAR(NOW())), FALSE) +1 AS 'control_no'");
        $ctrlNo = $res2[0]->control_no;
        $ctrlNo = str_pad($ctrlNo, 4, "0", STR_PAD_LEFT);
        $ctrlNo = "CA-" . date('Y') . "-" . $ctrlNo;

        $guid = $this->getGuid();

   
        $departmentId = DB::select("SELECT IFNULL((SELECT a.`DepartmentID` FROM humanresource.`employees` a WHERE a.`SysPK_Empl` = '".$request->employeeId."' ), FALSE) AS departmentId");
        $departmentId = $departmentId[0]->departmentId;

        
       
        

       $jid = DB::table('accounting.journal_main')->insertGetId([
            'SERIESNUMBER' => $cvs,                      // accounting.lastcash.cvs +1
            'trans_date'   => $request->requestedDate,   // kung kailan na create yung request
            'memo'         => $ctrlNo,                   // CA-2022-n -> amortized
            'Type'         => 'frmCreate_CASD',
            'trans_type'   => 'Disbursement',            // Disbursement
            'STATUS'       => 'ACTIVE',                  // ACTIVE
            'userid'       => $request->loggedUserId,    // logged user id
            'po_id'        => '0',                       // 0
            'save_date'    => now(),                     // now
            'name_id'      => $request->employeeId,      // selected employee id
            'Name_type'    => 'EMP_ERP',                 // EMP_ERP
            // 'MOP'                       =>, // blank
            'GroupEntry'    => $guid,   //GUID
            // 'VENDORID'                  => 0,  // 0
            // 'vc_reason'                 => '', // null
            // 'CancelledDate'             => '', // null
            // 'imported_from_excel'       =>, // 0
            // 'AttachmentTBL'             =>, // blank
            // 'CV'                        =>, // blank
            // 'CHKNUM'                    =>, // blank
            // 'CHKDATE'                   => '0001-01-01', // default - 0001-01-01
            'Payee'       => $request->employeeName,         // selected employee name
            'userid_name' => $request->loggedUserFullname,   // loggeduser name
        ]);


  

        $journal_details = 
        [
            [
                'journal_id'      => $jid,
                'account_id'      => 15,
                'subsidiary'      => $request->employeeName,
                'subsidiary_type' => 'EMPLOYEES',
                'bank'            => 'False',
                'SUBACCOUNTID'    => $request->employeeId,
                'GroupEntryD'     => $guid,
                '_CMP'            => 1,
                '_BRN'            => 1,
                '_CHR'            => $departmentId,                          // wala pa
                'INV_REF'         => $ctrlNo,
                'DEBITS'          => floatval(str_replace(',', '', $request->approvedAmount)),
                'CREDITS'          => 0,

            ],

            [
                'journal_id'      => $jid,
                'account_id'      => 1,
                'subsidiary'      => $request->employeeName,
                'subsidiary_type' => 'EMPLOYEES',
                'bank'            => 'False',
                'SUBACCOUNTID'    => $request->employeeId,
                'GroupEntryD'     => $guid,
                '_CMP'            => 1,
                '_BRN'            => 1,
                '_CHR'            => $departmentId,                          // wala pa
                'INV_REF'         => $ctrlNo,
                'DEBITS'          => 0,
                'CREDITS'         => floatval(str_replace(',', '', $request->approvedAmount)),
                
            ]
        ];

        DB::table('accounting.journal_detail')->insert($journal_details); 


        DB::table('accounting.lastcash')->insert([
            'ACID'     => 1,                      // accounting.lastcash.cvs +1
            'CVS'      => $cvs,   // request date
            'YR'       => date("Y"),                 // CA-2022-n -> amortized
            // 'CheckId'  => 'frmCreate_CASD',
            'Location' => 1,                         // Disbursement
            'JID'      => $jid,                      // ACTIVE
            'TITLEID'  => $request->companyId,       // logged user id
            'GRP'      => $guid,                     // 0
        ]);


        $dateNeededCreated   = date_create($request->requestedDate);
        $dateNeededConverted = date_format($dateNeededCreated, 'Y-m-d');

        $payableDateFromCreated   = date_create($request->payableDateFrom);
        $payableDateFromConverted = date_format($payableDateFromCreated, 'Y-m-d');

        $paymentTerms = floatval(str_replace(',', '', $request->approvedAmount)) / floatval(str_replace(',', '', $request->installmentAmount));

        DB::table('humanresource.amortized_deductions')->insert([
            'control_no'          => $ctrlNo,                       
            'transaction_date'    => $dateNeededConverted,          
            'deduction_start'     => $payableDateFromConverted,     
            'employee_id'         => $request->employeeId,
            'employee_name'       => $request->employeeName,        
            'deduction_id'        => 3,                             
            'deduction_name'      => 'Cash Advance',                
            'principal_amount'    => floatval(str_replace(',', '', $request->approvedAmount)),  
            'loan_amount'         => floatval(str_replace(',', '', $request->approvedAmount)),
            'payment_terms'       => $paymentTerms,
            'amortization_amount' => floatval(str_replace(',', '', $request->installmentAmount)),
            'remarks'             => $cvs,
            'GUID'                => $guid,
            'JID'                 => $jid,
            'REQID'               => $request->processId,
            'ts'                  => now(),
            'company_id'          => $request->companyId,
        ]);

        $this->approveActualSIgnApi($request);

        // check if department is properly get sometimes it returns null thats why i add this condition
        if ($departmentId) {
            DB::commit();
            return response()->json(['message' => 'Cash Advance has been Successfully submitted'], 200);
        } else {
            return response()->json(['message' => 'Cash Advance is Unprocessable! Please inform the Administrator'], 422);
        }
        


    }catch(\Exception $e){
        DB:: rollback();
    
        // throw error response
        log::debug($e);
        return response()->json($e, 500);
    }

        
    }




}
