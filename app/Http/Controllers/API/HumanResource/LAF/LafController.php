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
        $guid = $this->getGuid();
        $reqRef = $this->getLafRef($request->companyId);
        $mainID = DB::select("SELECT IFNULL(MAX(main_id),0) + 1 AS main FROM humanresource.`leave_request`");

        $mainID = $mainID[0]->main;

        $this->insertLafMain($request,$mainID,$reqRef,$guid);

        //Insert general.actual_sign
        for ($x = 0; $x < 2; $x++) {
            $array[] = array(
                'PROCESSID' => $mainID,
                'USER_GRP_IND' => '0',
                'FRM_NAME' => $request->form,
                'FRM_CLASS' => 'frmLeaveApplication', //Hold
                'REMARKS' => $request->purpose,
                'STATUS' => 'Not Started',
                'DUEDATE' => now(),
                'ORDERS' => $x,
                'REFERENCE' => $reqRef,
                'PODATE' => now(),
                'DATE' => now(),
                'INITID' => $request->loggedUserId,
                'FNAME' => $request->loggedUserFirstName,
                'LNAME' => $request->loggedUserLastName,
                'DEPARTMENT' => $request->loggedUserDepartment,
                'RM_ID' => $request->reportingManagerId,
                'REPORTING_MANAGER' => $request->reportingManagerName,
                'PROJECTID' => '0',
                'PROJECT' => $request->loggedUserDepartment,
                'COMPID' => $request->companyId,
                'COMPANY' => $request->companyName,
                'TYPE' => $request->form,
                'CLIENTID' => '0',
                'CLIENTNAME' => $request->companyName,
                'Max_approverCount' => '2',
                'DoneApproving' => '0',
                'Payee' => 'N/A',
            );
        }

        if ($array[0]['ORDERS'] == 0) {
            $array[0]['USER_GRP_IND'] = 'Acknowledgement of Reporting Manager';
            $array[0]['STATUS'] = 'In Progress';
        }

        if ($array[1]['ORDERS'] == 1) {
            $array[1]['USER_GRP_IND'] = 'For HR Management Approval';
        }


        DB::table('general.actual_sign')->insert($array);


        return response()->json(['message' => 'Your Leave Request was successfully submitted.'], 200);
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
