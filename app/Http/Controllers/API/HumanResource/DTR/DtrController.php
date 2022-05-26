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
        a.`id`,
        0 AS 'selected',
        a.`EmployeeName`,
        (SELECT 
          b.`PositionName` 
        FROM
          humanresource.`employees` b 
        WHERE b.`SysPK_Empl` = a.`SysPK_Empl` 
        LIMIT 1) AS positionName,
        a.`DepartmentName`,
        a.`dtr_date`,
        TIME_FORMAT(a.`in_am`, '%h:%i %p') AS in_am,
        TIME_FORMAT(a.`out_pm`, '%h:%i %p') AS out_pm,
        a.`Status` 
      FROM
        humanresource.`hr_emp_attendance` a 
        INNER JOIN general.`users` c 
          ON (a.`SysPK_Empl` = c.`Employee_id`) 
        INNER JOIN general.`systemreportingmanager` d 
          ON (c.`id` = d.`UID`) 
      WHERE a.`dummy_status` = 'For Approval' 
        AND RMID = '".$id."' 
        AND c.`UserName_User` NOT LIKE '%del@%'
        ");

        return response($data);
    }

    public function approveSelected(Request $request){
        
        $selectedData = $request->selectedData;
        $selectedData = json_decode($selectedData, true);
        
        
        
        for ($i = 0; $i < count($selectedData); $i++) {
            $dtr_date = date_create($selectedData[$i]['dtr_date']);
            $date = $selectedData[$i]['dtr_date'];

            $in_am = $selectedData[$i]['in_am'];
            $out_pm = $selectedData[$i]['out_pm'];

            $in_am = $this->merge($date, $in_am);
            $out_pm = $this->merge($date, $out_pm);

            // DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`dtr_date` = '".date_format($dtr_date, 'Y-m-d H:i:s')."', a.`Status` = '".$request->setStatus."' WHERE a.`id` = '".$selectedData[$i]['id']."' ");
            // DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`dtr_date` = '".date_format($dtr_date, 'Y-m-d')."', a.`in_am` =  '".date_format($in_am, 'Y-m-d H:i:s')."', a.`out_pm` = '".date_format($out_pm, 'Y-m-d H:i:s')."' ,a.`Status` = '".$request->setStatus."' WHERE a.`id` = '".$selectedData[$i]['id']."' ");
            DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`dtr_date` = '".date_format($dtr_date, 'Y-m-d')."', a.`in_am` =  '".$in_am."', a.`out_pm` = '".$out_pm."' ,a.`dummy_status` = '".$request->setStatus."' WHERE a.`id` = '".$selectedData[$i]['id']."' ");
        
        }
        // return response($request->setStatus);

        return response()->json(['message' => 'Attendance is now Active'], 200);

    }

    public function approve(Request $request){

        $dtr_date = date_create($request->dtr_date);
        $date = $request->dtr_date;

        $in_am = $request->in_am;
        $out_pm = $request->out_pm;

        $in_am = $this->merge($date, $in_am);
        $out_pm = $this->merge($date, $out_pm);
        

        // DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`Status` = $request->setStatus WHERE a.`id` = '".$request->id."' ");
        DB::update("UPDATE humanresource.`hr_emp_attendance` a SET a.`dtr_date` = '".date_format($dtr_date, 'Y-m-d')."', a.`in_am` =  '".$in_am."', a.`out_pm` = '".$out_pm."' ,a.`dummy_status` = '".$request->setStatus."' WHERE a.`id` = '".$request->id."' ");
        
        return response()->json(['message' => 'Attendance is now Active'], 200);

    }


    public function merge($date, $time){        
        $combinedDT = date('Y-m-d H:i:s', strtotime("$date $time"));
        return $combinedDT;
    }

    public function checkManager($id){
        $isManager = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`systemreportingmanager` a WHERE a.`RMID` = $id LIMIT 1), FALSE) AS isManager");
        return response()->json($isManager, 200);

    }
}
