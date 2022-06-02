<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;

class AuthController extends Controller
{
     public function login(Request $request){
     $user = new User();
     $isEmailExist = User::where('email',$request->email)->first();

     if($isEmailExist){
       return response()->json([
      'messege' => 'Email Already Exists'
      ]);
     }
     else{
     $user->name=$request->firstname;
     $user->email=$request->email;
     $verificationCode = rand(1000,9999);
     $user->email_verification_code= $verificationCode;
     $user->password=$request->password;
     $user->role=$request->role;
     $user->save();
     $tokenResult = $user->createToken('user',['users'])->accessToken; 
     $user->api_token=$tokenResult->token;
     $user->save();

     $userDetail = new UserDetail();
     $userDetail->user_id=$user->id;
     $userDetail->name=$request->firstname;
     $userDetail->Lastname=$request->lastname;
     $userDetail->phone=$request->phone;
     $userDetail->country=$request->country;
     $userDetail->gender=$request->gender;
     $userDetail->subscription= 0;
     $userDetail->save();

     $data = [
            'name' => $request->firstname,
            'verification_code' => $verificationCode,
            'email' => $request->email
        ];
     
     MailController::sendEmail($data);
     
     return response()->json([
      'status' => 'Record Added Successfully',
      'messege' => 'Email sent'
      ]);
 
     }
 }

      public function emailVerification(Request $request){
	$userToken = User::where('email',$request->email)->first('email_verification_code');
	if($userToken){
	    if($userToken->email_verification_code == $request->token){
	    $current_date_time = Carbon::now()->toDateTimeString();
        User::where('email',$request->email)->update(array('email_verified_at' => $current_date_time));
        return response()->json([
	   'status' => 'User is valid',
	    ]);
	}
	else{
	    return response()->json([
	    'status' => 'User is not valid',
	    ]);
	}
	}
	else {
	    return response()->json([
	   'status' => 'Email does not exist',
	   ]);
	}
      
         }













}
