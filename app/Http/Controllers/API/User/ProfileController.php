<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Validator;

class ProfileController extends Controller
{
    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'id' => 'required'
        ]);

        $user = User::where('id', $request->id)->get();

        if(!Hash::check($request->current_password, $user[0]->password)) {
            return response()->json(['status' => 'failed', 'message' => 'password not match']);
        }

        if ($validator->passes()) {
            User::find($request->id)->update(['password'=> Hash::make($request->new_password)]);
            return response()->json(['status' => 'success', 'message' => 'Password has been changed.']);
        }

        return response()->json(['error' => $validator->errors()]);
    }
}
