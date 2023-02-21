<?php

namespace App\Http\Controllers\API\Accounting\PC;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\PC\PcExpenseSetup;
use Illuminate\Http\Request;
use App\Models\Accounting\PC\PcMain;
use App\Models\Accounting\PC\PcTranspoSetup;
use Illuminate\Support\Facades\DB;

class PcController extends ApiController
{
    public function savePc(Request $request)
    {
        DB::beginTransaction();
        try {

            // $request->validate([
            //     'amount' => 'required|numeric|between:1,1000',
            // ]);

            $reference = $this->getPcRef($request->companyId);
            $guid      = $this->getGuid();

            $pcMain = new PcMain();

            $pcMain->REQREF            = $reference;
            $pcMain->UID               = $request->loggedUserId;
            $pcMain->LNAME             = $request->loggedUserLastName;
            $pcMain->FNAME             = $request->loggedUserFirstName;
            $pcMain->DEPARTMENT        = $request->loggedUserDepartment;
            $pcMain->REPORTING_MANAGER = $request->reportingManagerName;
            $pcMain->TRANS_DATE        = now();
            $pcMain->REQUESTED_DATE    = now();
            $pcMain->REQUESTED_AMT     = floatval(str_replace(',', '', $request->amount));
            $pcMain->DEADLINE          = date_create($request->dateNeeded);
            $pcMain->DESCRIPTION       = $request->purpose;
            $pcMain->STATUS            = 'In Progress';
            $pcMain->GUID              = $guid;
            $pcMain->PROJECT           = $request->projectName;
            $pcMain->TS                = now();
            $pcMain->PAYEE             = $request->payeeName;
            $pcMain->ISRELEASED        = 0;
            $pcMain->RELEASEDCASH      = 0;
            $pcMain->PRJID             = $request->projectId;
            $pcMain->CLIENT_NAME       = $request->clientName;
            $pcMain->CLIENT_ID         = $request->clientId;
            $pcMain->TITLEID           = $request->companyId;
            $pcMain->webapp            = 1;
            $pcMain->save();

            //Insert general.actual_sign
            //         for ($x = 0; $x < 4; $x++) {
            //             $array[] = array(
            // 'PROCESSID'         => $pcMain->id,
            // 'USER_GRP_IND'      => '0',
            // 'FRM_NAME'          => 'Petty Cash Request',                               //Hold
            // 'TaskTitle'         => '',
            // 'NS'                => '',
            // 'FRM_CLASS'         => 'PETTYCASHREQUEST',                                 //Hold
            // 'REMARKS'           => $request->purpose,
            // 'STATUS'            => 'Not Started',
            // 'DUEDATE'           => date_create($request->dateNeeded),
            // 'ORDERS'            => $x,
            // 'REFERENCE'         => $reference,
            // 'PODATE'            => date_create($request->dateNeeded),
            // 'DATE'              => date_create($request->dateNeeded),
            // 'INITID'            => $request->loggedUserId,
            // 'FNAME'             => $request->loggedUserFirstName,
            // 'LNAME'             => $request->loggedUserLastName,
            // 'DEPARTMENT'        => $request->loggedUserDepartment,
            // 'RM_ID'             => $request->reportingManagerId,
            // 'REPORTING_MANAGER' => $request->reportingManagerName,
            // 'PROJECTID'         => $request->projectId,
            // 'PROJECT'           => $request->projectName,
            // 'COMPID'            => $request->companyId,
            // 'COMPANY'           => $request->companyName,
            // 'TYPE'              => 'Request for Pettycash',
            // 'CLIENTID'          => $request->clientId,
            // 'CLIENTNAME'        => $request->clientName,
            // 'Max_approverCount' => '3',
            // 'DoneApproving'     => '0',
            // 'WebpageLink'       => 'pc_approve.php',
            // 'Payee'             => $request->payeeName,
            // 'Amount'            => floatval(str_replace(',', '', $request->amount)),
            //             );
            //         }

            //         if ($array[0]['ORDERS'] == 0) {
            //             $array[0]['USER_GRP_IND'] = 'Reporting Manager';
            //             $array[0]['STATUS']       = 'In Progress';
            //         }

            //         if ($array[1]['ORDERS'] == 1) {
            //             $array[1]['USER_GRP_IND'] = 'For Approval of Accounting Payable';
            //         }

            //         if ($array[2]['ORDERS'] == 2) {
            //             $array[2]['USER_GRP_IND'] = 'Liquidation';
            //         }

            //         if ($array[3]['ORDERS'] == 3) {
            //             $array[3]['USER_GRP_IND'] = 'Acknowledgement of Accounting';
            //         }

            //         DB::table('general.actual_sign')->insert($array);

            $isInserted = $this->insertActualSign($request, $pcMain->id, 'Petty Cash Request', $reference);
            if(!$isInserted) throw new \Exception('Actual Sign data Failed to save');

            // $this->insertAttachments($request,$pcMain->id,$reference);
            $request->request->add(['processId' => $pcMain->id]);
            $request->request->add(['referenceNumber' => $reference]);
            $this->addAttachments($request);

            DB::commit();
            return response()->json(['message' => 'Your Petty Cash Request was successfully submitted.'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e, 500);
        }
    }

    public function getPcMain($id)
    {
        $reMain = PcMain::findOrFail($id);
        return $this->showOne($reMain);
    }

    public function getExpense($id)
    {
        $expenseData = PcExpenseSetup::where('PCID', $id)->get();
        return response()->json($expenseData, 200);
    }
    public function getTranspo($id)
    {
        $transpoData = PcTranspoSetup::where('PCID', $id)->get();
        return response()->json($transpoData, 200);
    }
}
