<?php

namespace App\Traits;

use App\Models\Accounting\RFP\RfpMain;
use Illuminate\Support\Facades\DB;
use App\Models\General\Attachments;
use App\Models\Accounting\RFP\RfpLiquidation;



trait AccountingTrait
{
  // RFP
  // Get request_for_payment and rfp_details
  public function getrfpMainDetail($processId)
  {
    $rfpDetails = RfpMain::select('accounting.rfp_details.CLIENTNAME', 'accounting.rfp_details.PURPOSED', 'accounting.rfp_details.PAYEE', 'accounting.rfp_details.CURRENCY', 'accounting.rfp_details.MOP', 'accounting.rfp_details.PROJECT', 'accounting.request_for_payment.REQREF')
      ->join('accounting.rfp_details', 'accounting.rfp_details.RFPID', '=', 'accounting.request_for_payment.ID')
      ->where('accounting.request_for_payment.ID', '=', $processId)
      ->get();

    return $rfpDetails;
  }

  // get actual_sign
  public function getActualSign($processId, $companyId, $form)
  {
    $actualSign = DB::table('general.actual_sign as a')
      ->where('a.PROCESSID', $processId)
      ->where('a.FRM_NAME', $form)
      ->where('a.COMPID', $companyId)
      ->select('a.RM_ID', 'a.REPORTING_MANAGER', 'a.ID', 'a.USER_GRP_IND', 'a.STATUS', 'a.TS', 'a.DUEDATE', 'a.Amount', 'a.INITID')
      ->get();

    return $actualSign;
  }

  // get Attachments
  public function getAttachments($processId, $form)
  {
    $attachments = Attachments::where('REQID', $processId)->where('formName', $form)->get();
    return $attachments;
  }

  // get rfp_liquidation
  public function getRfpLiquidation($processId)
  {
    $liquidation = RfpLiquidation::where('RFPID', $processId)->get();
    return $liquidation;
  }

  // get recipient
  public function getRecipient($processId, $loggedUserId, $companyId, $formName)
  {
    $recipients = DB::select("SELECT a.uid as 'code',(SELECT UserFull_name FROM general.`users` usr WHERE usr.id = a.uid) AS 'name'
    FROM
    (SELECT initid AS 'uid' FROM general.`actual_sign` WHERE processid = $processId AND `FRM_NAME` = '" . $formName . "' AND `COMPID` = '" . $companyId . "' AND initid <> '" . $loggedUserId . "'
    UNION ALL
    SELECT UID_SIGN AS 'uid'  FROM general.`actual_sign` WHERE processid = $processId AND `FRM_NAME` = '" . $formName . "' AND `COMPID` = '" . $companyId . "' AND `status` = 'Completed' AND uid_sign <> '" . $loggedUserId . "')
    a GROUP BY uid;");
    return $recipients;
  }

  public function getCurrency() {
    $currency = DB::select("SELECT CurrencyName as 'code', CurrencyName as 'name' FROM accounting.`currencysetup`");
    return $currency;
  }

  public function getExpenseType(){
    $expenseType = DB::select("SELECT type AS 'code', type AS 'name' FROM accounting.`expense_type_setup`");
    return $expenseType;
  }
}
