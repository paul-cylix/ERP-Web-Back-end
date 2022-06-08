<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Hash;
use Validator;

class RegisterController extends Controller
{
    public function showUsers() {
        $result = DB::table('general.users AS g')
        ->select('g.id','g.UserName_User','g.UserFull_name','h.SysPK_Empl','h.FirstName_Empl','h.LastName_Empl','h.DepartmentName','h.PositionName')
        ->join('humanresource.employees AS h', 'g.Employee_id', '=', 'h.SysPK_Empl')
        ->where('h.SysPK_Empl', '!=', '-1')
        ->get();
        return response()->json($result, 200);
    }

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->id    = $request->id; //general.user.id
            $user->name    = $request->name;
            $user->email    = $request->email;
            $user->password    = Hash::make($request->password);
            $user->admin    = "false";
            $user->employee_id    = $request->employee_id;
            $user->fname    = $request->fname;
            $user->lname    = $request->lname;
            $user->department    = $request->department;
            $user->positionName    = $request->positionName;
            $user->companyId    = 1;
            $user->companyName    = "Cylix Technologies, Inc.";
            $user->save();
            return response()->json(['status' => 'success', 'message' => 'Registed Successfully!']);
        }

        return response()->json(['status' => 'failed', 'message' => 'The email has already been taken.']);
        
    }
}
