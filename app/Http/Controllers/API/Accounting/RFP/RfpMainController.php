<?php

namespace App\Http\Controllers\API\Accounting\RFP;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\RFP\RfpDetail;
use App\Models\Accounting\RFP\RfpMain;
use App\Models\General\ActualSign;
use App\Models\User;
use Illuminate\Http\Request;

class RfpMainController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = RfpMain::all();
        return $this->showAll($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rfpMain = new RfpMain();
        // $rfpMain->ID =   
        $rfpMain->DRAFT_IDEN = 0;
        $rfpMain->DRAFTNUM = '';
        $rfpMain->DATE = '2017-09-07';
        $rfpMain->REQREF = 'RFP-2018-0001';
        $rfpMain->PROJECT = '';
        $rfpMain->Deadline = '2021-09-14 00:00:00';
        $rfpMain->AMOUNT = '10231.000000';
        $rfpMain->STATUS = 'In Progress';
        $rfpMain->UID = '136';
        $rfpMain->FNAME = 'Rosevir';
        $rfpMain->LNAME = 'Ceballos';
        $rfpMain->DEPARTMENT = 'Information Technology';
        $rfpMain->REPORTING_MANAGER = 'Chua, Konrad A.';
        $rfpMain->POSITION = 'Senior Developer';
        $rfpMain->TS = '2021-08-26 07:31:43';
        $rfpMain->GUID = $this->getGuid();
        $rfpMain->COMMENTS = null;
        $rfpMain->ISRELEASED = '0';
        $rfpMain->TITLEID = '1';
        $rfpMain->webapp = '1';


        $rfpDetail = new RfpDetail();
        // $rfpDetail->ID = 
        // $rfpDetail->RFPID = 
        $rfpDetail->PROJECTID = '298';
        $rfpDetail->ClientID = '0';
        $rfpDetail->CLIENTNAME = '3rd Space';
        $rfpDetail->TITLEID = '1';
        $rfpDetail->PAYEEID = '0';
        $rfpDetail->MAINID = '1';
        $rfpDetail->PROJECT = '3rd Space.IP CCTV.OP.3RD01.01.001';
        $rfpDetail->DATENEEDED = '2021-09-14';
        $rfpDetail->PAYEE = 'Ceballos, Rosevir Jr. M. ';
        $rfpDetail->MOP = 'Cash';
        $rfpDetail->PURPOSED = 'test';
        $rfpDetail->DESCRIPTION = 'test';
        $rfpDetail->CURRENCY = 'PHP';
        $rfpDetail->currency_id = '0';
        $rfpDetail->AMOUNT = '241237.000000';
        $rfpDetail->STATUS = 'ACTIVE';
        $rfpDetail->GUID = '0C6A37A7-1D25-A050-D8B2-BD7F0EE778AA';
        $rfpDetail->RELEASEDCASH = '0';
        $rfpDetail->TS = '2021-09-14 07:58:22';


        // $actualSign = new ActualSign();
        // // $actualSign->PROCESSID = '213700';
        // $actualSign->USER_GRP_IND = 'Acknowledgement of Accounting';
        // $actualSign->FRM_NAME = 'Request for Payment';
        // $actualSign->TaskTitle = '';
        // $actualSign->NS = '';
        // $actualSign->FRM_CLASS = 'REQUESTFORPAYMENT';
        // $actualSign->REMARKS = 'test';
        // $actualSign->STATUS = 'Not Started';
        // $actualSign->UID_SIGN = '0';
        // $actualSign->TS = '2021-09-14 07:58:22';
        // $actualSign->DUEDATE = '2021-09-14 00:00:00';
        // // $actualSign->SIGNDATETIME = '';
        // $actualSign->ORDERS = '4';
        // $actualSign->REFERENCE = 'RFP-2021-0358';
        // $actualSign->PODATE = '2021-09-14';
        // $actualSign->PONUM = '';
        // $actualSign->DATE = '2021-09-14';
        // $actualSign->INITID = '136';
        // $actualSign->FNAME = 'Rosevir';
        // $actualSign->LNAME = 'Ceballos';
        // $actualSign->MI = '';
        // $actualSign->DEPARTMENT = 'Information Technology';
        // $actualSign->RM_ID = '11';
        // $actualSign->REPORTING_MANAGER = 'Chua, Konrad A. ';
        // $actualSign->PROJECTID = '298';
        // $actualSign->PROJECT = '3rd Space.IP CCTV.OP.3RD01.01.001';
        // $actualSign->COMPID = '1';
        // $actualSign->COMPANY = 'Cylix Technologies, Inc.';
        // $actualSign->TYPE = 'Request for Payment';
        // $actualSign->CLIENTID = '0';
        // $actualSign->CLIENTNAME = '3rd Space';
        // $actualSign->VENDORID = '0';
        // $actualSign->VENDORNAME = '';
        // $actualSign->Max_approverCount = '5';
        // $actualSign->GUID_GROUPS = '';
        // $actualSign->DoneApproving = '0';
        // $actualSign->WebpageLink = 'rfp_approve.php';
        // $actualSign->ApprovedRemarks = '';
        // $actualSign->Payee = 'Paul Iverson Cortez';
        // $actualSign->CurrentSender = '0';
        // $actualSign->CurrentReceiver = '0';
        // $actualSign->NOTIFICATIONID = '0';
        // $actualSign->SENDTOID = '0';
        // $actualSign->NRN = 'Imported';
        // $actualSign->imported_from_excel = '0';
        // $actualSign->Amount = '241237.000000';
        // $actualSign->webapp = '1';


        // $rfpMain->save();
        // $rfpMain->rfpDetail()->save($rfpDetail);
        // $rfpMain->actualSign()->save($actualSign);

        // $actualSign->save();


        // $actualSign = new ActualSign();
        // $actualSign->PROCESSID = '213700';
        $request = new Request();
        $request->headers->set('New-Set', '1');

        $value = $request->header('New-Set', 'default');
        $rfpMain->save();
        $rfpMain->rfpDetail()->save($rfpDetail);

        $user = User::findOrFail($value);


        for ($x = 0; $x < 5; $x++) {
            $actualSignData[] =
                [
                    'PROCESSID' => $rfpMain->ID,
                    'USER_GRP_IND' => 'Acknowledgement of Accounting',
                    'FRM_NAME' => $value,
                    'TaskTitle' => $user['name'],
                    'NS' => '',
                    'FRM_CLASS' => 'REQUESTFORPAYMENT',
                    'REMARKS' => 'test',
                    'STATUS' => 'Not Started',
                    'UID_SIGN' => '0',
                    'TS' => '2021-09-14 07:58:22',
                    'DUEDATE' => '2021-09-14 00:00:00',
                    // 'SIGNDATETIME' => '',
                    'ORDERS' => $x,
                    'REFERENCE' => 'RFP-2021-0358',
                    'PODATE' => '2021-09-14',
                    'PONUM' => '',
                    'DATE' => '2021-09-14',
                    'INITID' => '136',
                    'FNAME' => 'Rosevir',
                    'LNAME' => 'Ceballos',
                    'MI' => '',
                    'DEPARTMENT' => 'Information Technology',
                    'RM_ID' => '11',
                    'REPORTING_MANAGER' => 'Chua, Konrad A. ',
                    'PROJECTID' => '298',
                    'PROJECT' => '3rd Space.IP CCTV.OP.3RD01.01.001',
                    'COMPID' => '1',
                    'COMPANY' => 'Cylix Technologies, Inc.',
                    'TYPE' => 'Request for Payment',
                    'CLIENTID' => '0',
                    'CLIENTNAME' => '3rd Space',
                    'VENDORID' => '0',
                    'VENDORNAME' => '',
                    'Max_approverCount' => '5',
                    'GUID_GROUPS' => '',
                    'DoneApproving' => '0',
                    'WebpageLink' => 'rfp_approve.php',
                    'ApprovedRemarks' => '',
                    'Payee' => 'Paul Iverson Cortez',
                    'CurrentSender' => '0',
                    'CurrentReceiver' => '0',
                    'NOTIFICATIONID' => '0',
                    'SENDTOID' => '0',
                    'NRN' => 'Imported',
                    'imported_from_excel' => '0',
                    'Amount' => '241237.000000',
                    'webapp' => '1'
                ];
        }
        if ($actualSignData[0]['ORDERS'] == 0) {
            $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            $actualSignData[0]['STATUS'] = 'In Progress';
        }

        if ($actualSignData[1]['ORDERS'] == 1) {
            $actualSignData[1]['USER_GRP_IND'] = 'For Approval of Management';
        }

        if ($actualSignData[2]['ORDERS'] == 2) {
            $actualSignData[2]['USER_GRP_IND'] = 'Releasing of Cash';
        }

        if ($actualSignData[3]['ORDERS'] == 3) {
            $actualSignData[3]['USER_GRP_IND'] = 'Initiator';
        }

        if ($actualSignData[4]['ORDERS'] == 4) {
            $actualSignData[4]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
        }






        ActualSign::insert($actualSignData);
        return ($user);
    }

    // public function saveRFP(Request $request){
    //     dd($request->date);
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Accounting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function show(RfpMain $rfpMain)
    {
        return $this->showOne($rfpMain);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accounting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function edit(RfpMain $rfpMain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RfpMain $rfpMain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accounting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function destroy(RfpMain $rfpMain)
    {
        //
    }

    public function getRfpWithDetails($id)
    {
        $data = RfpMain::select('accounting.rfp_details.CLIENTNAME', 'accounting.rfp_details.PURPOSED', 'accounting.rfp_details.PAYEE', 'accounting.rfp_details.CURRENCY', 'accounting.rfp_details.MOP', 'accounting.rfp_details.PROJECT')
            ->join('accounting.rfp_details', 'accounting.rfp_details.RFPID', '=', 'accounting.request_for_payment.ID')
            ->where('accounting.request_for_payment.ID', '=', $id)
            ->get();

        return $data;
    }
}
