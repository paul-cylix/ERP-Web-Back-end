<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required',
            'password'=> 'required',
            // 'companyId' => 'required',
        ]);
        if (!Auth::attempt($login)){
            return response()->json(['message' => 'The email or password you’ve entered is incorrect.'], 401);
        }
        $user = Auth::user();
        $user = User::find($user['id']);
    
        $ObjToken = $user->createToken('Personal Access Token');
        $token = $user->createToken('Personal Access Token')->accessToken;
        $expiration = $ObjToken->token->expires_at->diffInSeconds(Carbon::now());
        
        Log::debug($user->id);

        // check if manager
        $isManager = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`systemreportingmanager` a WHERE a.`RMID` = $user->id LIMIT 1), FALSE) AS isManager");
        $isManager = $isManager[0]->isManager;

        // $isHR = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`systemuserroleprofile` a WHERE a.`UID` = '".$user->id."' AND a.`ProfileName` = 'HR Payroll'), FALSE) AS 'isHR'");
        $isHR = DB::select("SELECT IFNULL((SELECT TRUE FROM general.`systemuserroleprofile` a WHERE a.`UID` = '".$user->id."' AND a.`ProfileName` IN ('HR Payroll', 'Admin', 'HR Coordinator') LIMIT 1 ), FALSE) AS 'isHR'");
        $isHR = $isHR[0]->isHR;
        
        Log::debug($isHR);


        // get user joined company
        $companies = $this->showCompanies($user->id);

        


        $userData = array('user' => $user, 'Personal_Access_Token' => $token, 'expires_at' => $expiration, 'isManager' => $isManager, 'company' => $companies, 'isHR' => $isHR );
        
  
        return response()->json($userData);
    }


    public function profile(){
        $user_data = Auth::user();
    //    return User::all();
        return response()->json([
            'status' => true,
            'message' => 'user data',
            'data' => $user_data
        ]);

    // return '1';


    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $token->delete();
        // $request->user()->token()->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
        // return response()->json(['token' =>  $request->user()]);
    }

    public function showCompanies($id){
        $companies = DB::select("SELECT 
        title_id AS companyID,
        title_name AS companyName 
      FROM
        general.`project_title` a 
        INNER JOIN general.`allowedcmp` b 
          ON (a.`title_id` = b.`CID`) 
      WHERE b.`UID` = $id");

        return $companies;
      
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }





    
}
