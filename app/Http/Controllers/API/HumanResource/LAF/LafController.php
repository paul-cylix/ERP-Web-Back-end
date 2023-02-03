<?php

namespace App\Http\Controllers\API\HumanResource\LAF;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\HumanResource\LAF\LafMain;
use Illuminate\Support\Facades\DB;

class LafController extends ApiController
{
  public function checkIfLafExist(Request $request)
  {

    $leaveDates = $request->leaveDates;
    $leaveDates = json_decode($leaveDates, true);

    $data = LafMain::select('leave_date')
      ->where('employee_id', $request->employeeId)
      ->where('TITLEID', $request->companyId)
      ->whereIn('status', ['Completed', 'In Progress', 'For Clarification'])
      ->whereIn('leave_date', $leaveDates)
      ->get();

    return $data;
  }

  public function saveLaf(Request $request)
  {
    DB::beginTransaction();
    try {

      $guid = $this->getGuid();
      $reqRef = $this->getLafRef($request->companyId);
      $mainID = DB::select("SELECT IFNULL(MAX(main_id),0) + 1 AS main FROM humanresource.`leave_request`");

      $mainID = $mainID[0]->main;

      $leaveData = $request->leaveData;
      $leaveData = json_decode($leaveData, true);

      // insert to hr.ot main table
      for ($i = 0; $i < count($leaveData); $i++) {

        $leaveArray[] = [
          'main_id'           => $mainID,
          'reference'         => $reqRef,
          'request_date'      => date_create($request->requestedDate),
          'date_needed'       => date_create($request->requestedDate),
          'employee_id'       => $request->employeeId,
          'employee_name'     => $request->employeeName,
          'medium_of_report'  => $request->reportName,
          'report_time'       => date_create($request->reportDateTime),
          'leave_type'        => $leaveData[$i]['leave_type'],
          'leave_date'        => $leaveData[$i]['leave_date'],
          'leave_paytype'     => $leaveData[$i]['leave_paytype'],
          'leave_halfday'     => $leaveData[$i]['leave_halfday'],
          'num_days'          => $leaveData[$i]['num_days'],
          'reason'            => $request->reason,
          'status'            => 'In Progress',
          'UID'               => $request->loggedUserId,
          'fname'             => $request->loggedUserFirstName,
          'lname'             => $request->loggedUserLastName,
          'position'          => $request->loggedUserPosition,
          'reporting_manager' => $request->reportingManagerName,
          'department'        => $request->loggedUserDepartment,
          'ts'                => now(),
          'GUID'              => $guid,
          'TITLEID'           => $request->companyId,
          'webapp'            => 1
        ];
      }
      LafMain::insert($leaveArray);

      // //Insert general.actual_sign
      // for ($x = 0; $x < 2; $x++) {
      //     $array[] = array(
      //       'PROCESSID'         => $mainID,
      //       'USER_GRP_IND'      => '0',
      //       'FRM_NAME'          => $request->form,
      //       'FRM_CLASS'         => 'frmLeaveApplication',            //Hold
      //       'REMARKS'           => $request->purpose,
      //       'STATUS'            => 'Not Started',
      //       'DUEDATE'           => now(),
      //       'ORDERS'            => $x,
      //       'REFERENCE'         => $reqRef,
      //       'PODATE'            => now(),
      //       'DATE'              => now(),
      //       'INITID'            => $request->loggedUserId,
      //       'FNAME'             => $request->loggedUserFirstName,
      //       'LNAME'             => $request->loggedUserLastName,
      //       'DEPARTMENT'        => $request->loggedUserDepartment,
      //       'RM_ID'             => $request->reportingManagerId,
      //       'REPORTING_MANAGER' => $request->reportingManagerName,
      //       'PROJECTID'         => '0',
      //       'PROJECT'           => $request->loggedUserDepartment,
      //       'COMPID'            => $request->companyId,
      //       'COMPANY'           => $request->companyName,
      //       'TYPE'              => $request->form,
      //       'CLIENTID'          => '0',
      //       'CLIENTNAME'        => $request->companyName,
      //       'Max_approverCount' => '2',
      //       'DoneApproving'     => '0',
      //       'Payee'             => 'N/A',
      //     );
      // }

      // if ($array[0]['ORDERS'] == 0) {
      //     $array[0]['USER_GRP_IND'] = 'Acknowledgement of Reporting Manager';
      //     $array[0]['STATUS'] = 'In Progress';
      // }

      // if ($array[1]['ORDERS'] == 1) {
      //     $array[1]['USER_GRP_IND'] = 'For HR Management Approval';
      // }
      // DB::table('general.actual_sign')->insert($array);



      $isInserted = $this->insertActualSign($request, $mainID, 'Leave Request', $reqRef);
      if (!$isInserted) throw new \Exception('Actual Sign data Failed to save');


      DB::commit();
      return response()->json(['message' => 'Your Leave Request was successfully submitted.'], 200);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json($e, 500);
    }
  }

  public function getLafMain($id, $companyId)
  {
    // $lafMain = LafMain::where('main_id', $id)->where('TITLEID', $companyId)->get();

    $lafMain = DB::select("SELECT 
        *,
        (SELECT 
          b.`RMID` 
        FROM
          general.`systemreportingmanager` b 
        WHERE b.`RMName` = a.`reporting_manager` 
        LIMIT 1) AS 'rm_id',
        (SELECT 
          c.`id` 
        FROM
          general.`setup_dropdown_items` c 
        WHERE c.`type` = 'Medium of Report' 
          AND c.`status` = 'Active' 
          AND c.`item` = a.`medium_of_report` 
        LIMIT 1) AS 'medium_of_report_id' 
      FROM
        humanresource.`leave_request` a 
      WHERE a.`main_id` = $id AND a.`TITLEID` = $companyId ");

    return response()->json($lafMain, 200);
  }
}
