<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required',
            'password'=> 'required',
            'companyId' => 'required',
        ]);
        if (!Auth::attempt($login)){
            return response()->json(['message' => 'The email or password you’ve entered is incorrect.'], 401);
        }
        $user = Auth::user();
        $user = User::find($user['id']);
    
        $ObjToken = $user->createToken('Personal Access Token');
        $token = $user->createToken('Personal Access Token')->accessToken;
        $expiration = $ObjToken->token->expires_at->diffInSeconds(Carbon::now());
       
        $userData = array('user' => $user, 'Personal_Access_Token' => $token, 'expires_at' => $expiration );
        
  
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
