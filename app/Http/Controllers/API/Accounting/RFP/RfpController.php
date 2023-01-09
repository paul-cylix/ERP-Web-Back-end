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
        $rfpMain->TITLEID           = '1';
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


        // $actualSign = new ActualSign();
        // $actualSign->PROCESSID = '213700';
        // $request = new Request();
        // $request->headers->set('New-Set','1');
        // $value = $request->header('New-Set', 'defaultIfNull');


        $rfpMain->rfpDetail()->save($rfpDetail);

        // $user = User::findOrFail($value);


        for ($x = 0; $x < 5; $x++) {
            $actualSignData[] = 
                [
                    'PROCESSID'         => $rfpMain->ID,
                    'USER_GRP_IND'      => 'Acknowledgement of Accounting',
                    'FRM_NAME'          => 'Request for Payment',
                    'FRM_CLASS'         => 'REQUESTFORPAYMENT',
                    'REMARKS'           => $request->purpose,
                    'STATUS'            => 'Not Started',
                    'TS'                => now(),
                    'DUEDATE'           => date_create($request->dateNeeded),
                    'ORDERS'            => $x,
                    'REFERENCE'         => $reqRef,
                    'PODATE'            => date_create($request->dateNeeded),
                    'DATE'              => date_create($request->dateNeeded),
                    'INITID'            => $request->loggedUserId,
                    'FNAME'             => $request->loggedUserFirstName,
                    'LNAME'             => $request->loggedUserLastName,
                    'DEPARTMENT'        => $request->loggedUserDepartment,
                    'RM_ID'             => $request->reportingManagerId,
                    'REPORTING_MANAGER' => $request->reportingManagerName,
                    'PROJECTID'         => $request->projectId,
                    'PROJECT'           => $request->projectName,
                    'COMPID'            => $request->companyId,
                    'COMPANY'           => $request->companyName,
                    'TYPE'              => 'Request for Payment',
                    'CLIENTID'          => $request->clientId,
                    'CLIENTNAME'        => $request->clientName,
                    'Max_approverCount' => '5',
                    'DoneApproving'     => '0',
                    'WebpageLink'       => 'rfp_approve.php',
                    'ApprovedRemarks'   => '',
                    'Payee'             => $request->payeeName,
                    'Amount'            => floatval(str_replace(',', '', $request->amount)),
                ];
        }
        if ($actualSignData[0]['ORDERS'] == 0) {
            $actualSignData[0]['USER_GRP_IND'] = 'Reporting Manager';
            $actualSignData[0]['STATUS']       = 'In Progress';
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



        ActualSign:: insert($actualSignData);
        
        $request->request->add(['processId' => $rfpMain->ID]);
        $request->request->add(['referenceNumber' => $reqRef]);

        $this->addAttachments($request);

        // if ($request->hasFile('file')) {

        //     foreach ($request->file as $file) {
        //         $completeFileName = $file->getClientOriginalName();
        //         $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
        //         $extension = $file->getClientOriginalExtension();
        //         $randomized = rand();
        //         $newFileName = str_replace(' ', '', $fileNameOnly) . '-' . $randomized . '' . time() . '.' . $extension;
        //         $reqRef = str_replace('-', '_', $reqRef);
        //         $mimeType = $file->getMimeType();
        //         // $myPath = "C:/Users/Iverson/Desktop/Attachments/".session('LoggedUser_CompanyID')."/RFP/".$rfpCode;

        //         // For moving the file
        //         $destinationPath = "public/Attachments/{$request->companyId}/RFP/" . $reqRef;
        //         // For preview
        //         $storagePath = "storage/Attachments/{$request->companyId}/RFP/" . $reqRef;
        //         $symPath = "public/Attachments/RFP";
        //         $file->storeAs($destinationPath, $completeFileName);
        //         $fileDestination = $storagePath . '/' . $completeFileName;

        //         // $image = base64_encode(file_get_contents($file));

        //         // DB::table('repository.rfp')->insert([
        //         //     'REFID' => $rfpMain->ID,
        //         //     'FileName' => $completeFileName,
        //         //     'IMG' => $image,
        //         //     'UID' => $request->loggedUserId,
        //         //     'Ext' => $extension
        //         // ]);

        //         $attachmentsData = [
        //             'INITID' => $request->loggedUserId,
        //             'REQID' => $rfpMain->ID,
        //             'filename' => $completeFileName,
        //             'filepath' => $storagePath,
        //             'fileExtension' => $extension,
        //             'newFilename' => $newFileName,
        //             'fileDestination' => $destinationPath,
        //             'mimeType' => $mimeType,
        //             // 'imageBytes' => $image,
        //             'formName' => 'Request for Payment',
        //             'created_at' => date('Y-m-d H:i:s')
        //         ];



        //         Attachments::insert($attachmentsData);
        //     }
        // }


        DB::commit();
        // return response()->json($request, 201);
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
