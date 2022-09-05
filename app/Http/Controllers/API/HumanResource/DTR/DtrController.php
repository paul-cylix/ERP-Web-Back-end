<?php

namespace App\Http\Controllers\API\HumanResource\DTR;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DtrController extends ApiController
{
    public function index($id){
        $data = DB::select("
        SELECT 
        a.id,
        0 AS 'selected',
        a.`userid`,
        a.`date_entry` AS 'dtr_date',
        DATE_FORMAT(a.`clock_in`, '%Y-%m-%d %h:%i %p') AS in_am,
        DATE_FORMAT(a.`clock_out`, '%Y-%m-%d %h:%i %p') AS out_pm,
        a.`status`,
        b.`display_name` AS 'EmployeeName',
        c.`positionName`,
        c.`DepartmentName` 
      FROM
        erpweb.`app_two` a 
        INNER JOIN erpweb.`users_attendance` b 
          ON a.`userid` = b.`employee_id`
        INNER JOIN humanresource.`employees` c 
          ON    b.`employee_id` = c.`SysPK_Empl`
        WHERE b.`manager_id`  = '".$id."'   and a.`status` = 'For Approval'
        ");

        return response($data);
    }

    public function approveSelected(Request $request){
        Log::debug($request);
        $selectedData = $request->selectedData;
        $selectedData = json_decode($selectedData, true);
        
        for ($i = 0; $i < count($selectedData); $i++) {
            $dtr_date = date_create($selectedData[$i]['dtr_date']);
            // $date = $selectedData[$i]['dtr_date'];

            $in_am = date_create($selectedData[$i]['in_am']);
            $out_pm = date_create($selectedData[$i]['out_pm']);
            $in_am = date_format($in_am, 'Y-m-d H:i:s');
            $out_pm = date_format($out_pm, 'Y-m-d H:i:s');
            
            // Log::debug($in_am);
            
            // $in_am = $this->merge($date, $in_am);
            // $out_pm = $this->merge($date, $out_pm);
// 
            DB::update("UPDATE erpweb.`app_two` a SET a.`date_entry` = '".date_format($dtr_date, 'Y-m-d')."', a.`clock_in` =  '".$in_am."', a.`clock_out` = '".$out_pm."' ,a.`status` = '".$request->setStatus."' WHERE a.`id` = '".$selectedData[$i]['id']."' ");
        }
        return response()->json(['message' => 'Attendance is now Active'], 200);
    }

    // public function approve(Request $request){

    //     $dtr_date = date_create($request->dtr_date);
    //     $date = $request->dtr_date;

    //     $in_am = $request->in_am;
    //     $out_pm = $request->out_pm;

    //     $in_am = $this->merge($date, $in_am);
    //     $out_pm = $this->merge($date, $out_pm);
        

    //     // DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`Status` = $request->setStatus WHERE a.`id` = '".$request->id."' ");
    //     DB::update("UPDATE erpweb.`app_two` a SET a.`date_entry` = '".date_format($dtr_date, 'Y-m-d')."', a.`clock_in` =  '".$in_am."', a.`clock_out` = '".$out_pm."' ,a.`status` = '".$request->setStatus."' WHERE a.`id` = '".$request->id."' ");

    //     return response()->json(['message' => 'Attendance is now Active'], 200);

    // }


    public function merge($date, $time){        
        $combinedDT = date('Y-m-d H:i:s', strtotime("$date $time"));

        Log::debug($combinedDT);
        return $combinedDT;
    }

    public function checkManager($id){
        $isManager = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`systemreportingmanager` a WHERE a.`RMID` = $id LIMIT 1), FALSE) AS isManager");
        return response()->json($isManager, 200);

    }
}
