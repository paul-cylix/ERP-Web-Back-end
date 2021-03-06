<?php

namespace App\Http\Controllers\API\CylixPortal;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CylixPortalController extends ApiController
{
    public function index()
    {
        $employees = DB::select("SELECT 
        a.`SysPK_Empl` AS 'employee_id', 
        a.`Name_Empl` AS 'employee_fullname', 
        (SELECT b.`UserName_User` FROM general.`users` b WHERE b.`Employee_id` = a.`SysPK_Empl` AND b.`status` = 'ACTIVE' AND b.`Employee_id` NOT LIKE '%del@%' LIMIT 1) AS 'employee_email'
      FROM
        humanresource.`employees` a 
      WHERE a.`CompanyID` = 1 
        AND a.`Status_Empl` LIKE 'Active%'
      ORDER BY a.`Name_Empl` 
      ");
        $managers = DB::select("SELECT a.`RMID` AS id, b.`UserFull_name` AS manager_name, b.`Employee_id` AS employee_id, b.`UserName_User` AS username  FROM general.`systemreportingmanager` a INNER JOIN general.`users` b ON a.`RMID` = b.`id` GROUP BY a.`RMID` ORDER BY b.`UserFull_name`");
        return response()->json([
            'employees' => $employees, 'managers' => $managers
        ]);
    }


    public function saveUserAttendance(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|email',
            'fullname' => 'required',
            'employeeId' => 'required',
            'isManager' => 'required',
            'password' => 'required|confirmed|min:8|max:32',
            'rank' => 'required',
            'managerId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['failed' => $validator->errors()]);
        }

        $usernameEmail = $request->username;
        $userFullname = $request->fullname;
        $employeeId = intval($request->employeeId);

        // check if general.users exist 
        $generalUser = DB::select("SELECT 
        a.`id`, 
        a.`UserFull_name`, 
        a.`UserName_User`, 
        a.`Employee_id` 
        FROM general.`users` a 
        WHERE a.`UserName_User` = '" . $usernameEmail . "' 
        AND a.`status` = 'active' 
        ORDER BY a.`savedate` 
        LIMIT 1 ");



        // check if email exist in erpweb.user_attendance
        $attendanceUser = DB::select("SELECT * FROM erpweb.`users_attendance` a 
        WHERE a.`username` = '" . $usernameEmail . "'");



        DB::beginTransaction();
        try {


            if (empty($generalUser) && empty($attendanceUser)) {

                $genUserId = DB::table('general.users')->insertGetId([
                    'UserName_User' => $usernameEmail,
                    'UserFull_name' => $userFullname,
                    'Password_User' => 'dg4uCwwDtek=',
                    'Employee_id' => $employeeId,
                    'email_address' => $usernameEmail,
                    'IsManager' => intval($request->IsManager),
                ]);


                DB::table('erpweb.users_attendance')->insert([
                    'id' => $genUserId,
                    'display_name' => $userFullname,
                    'username' => $usernameEmail,
                    'password' => bcrypt($request->password),
                    'employee_id' => $employeeId,
                    'rank' => $request->rank,
                    'manager_id' => intval($request->managerId),
                ]);

                DB::commit();
                return response()->json(['success' => 'Registered Successfully!']);
                // return 'save to general.users and erpweb.users_attendance';

            } elseif (empty($generalUser)) {
                $genUserId = DB::table('general.users')->insertGetId([
                    'UserName_User' => $usernameEmail,
                    'UserFull_name' => $userFullname,
                    'Password_User' => 'dg4uCwwDtek=',
                    'Employee_id' => $employeeId,
                    'email_address' => $usernameEmail,
                    'IsManager' => intval($request->IsManager),
                ]);
                DB::commit();
                return response()->json(['success' => 'User Registered Successfully!']);
                // return 'save to general.users';

            } elseif (empty($attendanceUser)) {

                // User id of existing email
                $generalUserId = $generalUser[0]->id;

                DB::table('erpweb.users_attendance')->insert([
                    'id' => $generalUserId,
                    'display_name' => $userFullname,
                    'username' => $usernameEmail,
                    'password' => bcrypt($request->password),
                    'employee_id' => $employeeId,
                    'rank' => $request->rank,
                    'manager_id' => intval($request->managerId),
                ]);
                DB::commit();
                return response()->json(['success' => 'Record Registered Successfully!']);
                // return 'save to erpweb.users_attendance';

            } else {
                return response()->json(['exist' => 'User Record Already Exist!']);
                // return 'Record Already Exist';
            }

   
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e, 500);


        }
    }


    public function activateAll()
    {
        $queryData = DB::table('humanresource.hr_emp_attendance')
        ->where('Status','For Approval')
        ->update(['Status' => 'Active']);
        return response()->json($queryData);
    }


    public function getEmployeeAttendance(){
        $queryData = DB::select("
        SELECT 
            a.`id`,
            a.`userid` AS 'user_id',
            a.`emp_name` AS 'employee_name',
            DATE_FORMAT(a.`date_entry`, '%m/%d/%Y') AS 'dtr_date', 
            (SELECT DATE_FORMAT(MIN(b.`time_entry`),'%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'IN' AND b.`userid` = a.`userid`) AS 'in',
            IFNULL(
	            (SELECT DATE_FORMAT(MAX(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`),
	            (SELECT DATE_FORMAT(MIN(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = DATE_ADD(a.`date_entry`,INTERVAL 1 DAY) AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`)
	            )AS 'out'
            FROM erpweb.`app` a
            GROUP BY a.`date_entry`,a.`userid`
        ");
        return response()->json($queryData);
    }

    public function getFilteredEmployeeAttendance(Request $request){

        Log::debug(gettype($request->dateRange));

        if ($request->dateRange && $request->employee) {
            $user_id = $request->employee['code'];
            $start = date("Y-m-d",strtotime($request->dateRange[0]));   
            $end = date("Y-m-d",strtotime($request->dateRange[1])); 

            $queryData = DB::select("
            SELECT 
                a.`id`,
                a.`userid` AS 'user_id',
                a.`emp_name` AS 'employee_name',
                DATE_FORMAT(a.`date_entry`, '%m/%d/%Y') AS 'dtr_date', 
                (SELECT DATE_FORMAT(MIN(b.`time_entry`),'%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'IN' AND b.`userid` = a.`userid`) AS 'in',
                IFNULL(
                    (SELECT DATE_FORMAT(MAX(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`),
                    (SELECT DATE_FORMAT(MIN(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = DATE_ADD(a.`date_entry`,INTERVAL 1 DAY) AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`)
                    )AS 'out'
                FROM erpweb.`app` a
                WHERE a.`userid` = '".$user_id."'
                AND a.`date_entry` BETWEEN '".$start."' 
                AND '".$end."'
                GROUP BY a.`date_entry`,a.`userid`
            ");

        } elseif($request->dateRange == false && $request->employee) {
        
            $user_id = $request->employee['code'];

            $queryData = DB::select("
            SELECT 
                a.`id`,
                a.`userid` AS 'user_id',
                a.`emp_name` AS 'employee_name',
                DATE_FORMAT(a.`date_entry`, '%m/%d/%Y') AS 'dtr_date', 
                (SELECT DATE_FORMAT(MIN(b.`time_entry`),'%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'IN' AND b.`userid` = a.`userid`) AS 'in',
                IFNULL(
                    (SELECT DATE_FORMAT(MAX(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`),
                    (SELECT DATE_FORMAT(MIN(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = DATE_ADD(a.`date_entry`,INTERVAL 1 DAY) AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`)
                    )AS 'out'
                FROM erpweb.`app` a
                WHERE a.`userid` = '".$user_id."'
                GROUP BY a.`date_entry`,a.`userid`
            ");
        } elseif($request->dateRange && $request->employee == false) {
            $start = date("Y-m-d",strtotime($request->dateRange[0]));   
            $end = date("Y-m-d",strtotime($request->dateRange[1]));   

            $queryData = DB::select("
            SELECT 
                a.`id`,
                a.`userid` AS 'user_id',
                a.`emp_name` AS 'employee_name',
                DATE_FORMAT(a.`date_entry`, '%m/%d/%Y') AS 'dtr_date', 
                (SELECT DATE_FORMAT(MIN(b.`time_entry`),'%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'IN' AND b.`userid` = a.`userid`) AS 'in',
                IFNULL(
                    (SELECT DATE_FORMAT(MAX(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = a.`date_entry` AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`),
                    (SELECT DATE_FORMAT(MIN(b.`time_entry`), '%h:%i %p') FROM erpweb.`app` b WHERE b.`date_entry` = DATE_ADD(a.`date_entry`,INTERVAL 1 DAY) AND b.`mode` = 'OUT' AND b.`userid` = a.`userid`)
                    )AS 'out'
                FROM erpweb.`app` a
                WHERE a.`date_entry` BETWEEN '".$start."' 
                AND '".$end."'
                GROUP BY a.`date_entry`,a.`userid`
            ");
        } 

  
        return response()->json($queryData);
        
    }






}
