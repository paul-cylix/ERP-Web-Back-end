<?php

namespace App\Http\Controllers\API\Accounting\RFP;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\RFP\RfpMain;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\General\ActualSign;
use App\Models\Accounting\RFP\RfpDetail;
use Illuminate\Support\Carbon;
use App\Models\General\Attachments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfpController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $now = Carbon::now();
        // $data = RfpMain::whereYear('TS',$now->year)->get('REQREF');

        // foreach($data as $entries){
        //     $entries->REQREF = substr($entries->REQREF, 9);
        // }
        // return $this->showOne($data->max());

        // return $this->getRfpRef();
        // return $this->getRfpRef();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{  

        $guid   = $this->getGuid();
        $reqRef = $this->getRfpRef($request->companyId);

        $rfpMain                    = new RfpMain();
        $rfpMain->DRAFT_IDEN        = 0;
        $rfpMain->DRAFTNUM          = '';
        $rfpMain->DATE              = now();
        $rfpMain->REQREF            = $reqRef;
        $rfpMain->Deadline          = date_create($request->dateNeeded);
        $rfpMain->AMOUNT            = floatval(str_replace(',', '', $request->amount));
        $rfpMain->STATUS            = 'In Progress';
        $rfpMain->UID               = $request->loggedUserId;
        $rfpMain->FNAME             = $request->loggedUserFirstName;
        $rfpMain->LNAME             = $request->loggedUserLastName;
        $rfpMain->DEPARTMENT        = $request->loggedUserDepartment;
        $rfpMain->REPORTING_MANAGER = $request->reportingManagerName;
        $rfpMain->POSITION          = $request->loggedUserPosition;
        $rfpMain->TS                = now();
        $rfpMain->GUID              = $guid;
        $rfpMain->COMMENTS          = null;
        $rfpMain->ISRELEASED        = '0';
        $rfpMain->TITLEID           = $request->companyId;
        $rfpMain->webapp            = '1';
        $rfpDetail                  = new RfpDetail();
        $rfpDetail->PROJECTID       = $request->projectId;
        $rfpDetail->ClientID        = $request->clientId;
        $rfpDetail->CLIENTNAME      = $request->clientName;
        $rfpDetail->TITLEID         = $request->companyId;
        $rfpDetail->PAYEEID         = '0';
        $rfpDetail->MAINID          = $request->mainId;
        $rfpDetail->PROJECT         = $request->projectName;
        $rfpDetail->DATENEEDED      = date_create($request->dateNeeded);
        $rfpDetail->PAYEE           = $request->payeeName;
        $rfpDetail->MOP             = $request->modeOfPayment;
        $rfpDetail->PURPOSED        = $request->purpose;
        $rfpDetail->DESCRIPTION     = $request->purpose;
        $rfpDetail->CURRENCY        = $request->currency;
        $rfpDetail->currency_id     = '0';
        $rfpDetail->AMOUNT          = floatval(str_replace(',', '', $request->amount));
        $rfpDetail->STATUS          = 'ACTIVE';
        $rfpDetail->GUID            = $guid;
        $rfpDetail->RELEASEDCASH    = '0';
        $rfpDetail->TS              = now();
        $rfpMain->save();

        $rfpMain->rfpDetail()->save($rfpDetail);

        $isInserted = $this->insertActualSign($request, $rfpMain->ID, 'Request for Payment', $reqRef);
        if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');
            
        $request->request->add(['processId' => $rfpMain->ID]);
        $request->request->add(['referenceNumber' => $reqRef]);

        $this->addAttachments($request);

        DB::commit();
        return response()->json([
            'message' => 'Request for Payment has been successfully submitted', 'code' => 200
        ]);

    }catch(\Exception $e){
        DB::rollback();
        Log::debug($e);
    
        // throw error response
        return response()->json($e, 500);
    }



    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Accounting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function show(RfpMain $rfpMain)
    {
        //
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
}
