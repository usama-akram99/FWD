<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Auth;


class AuthController extends Controller
{
    public function signup(Request $request){
        $user = new User();
        $isEmailExist = User::where('email',$request->email)->first();
        if($isEmailExist){
            return response()->json([
                'status' => '0',
                'messege' => 'Email Already Exists',
            ]);
        }
        else{
            $user->name=$request->firstname;
            $user->email=$request->email;
            $verificationCode = rand(1000,9999);
            $user->email_verification_code= $verificationCode;
            $user->password= Hash::make($request->password);
            $user->role=$request->role;
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
                'status' => '1',
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
                    'status' => '1',
                    'messege' => 'Email verified successfully',
                ]);
            }
            else{
                return response()->json([
                    'status' => '0',
                    'messege' => 'Verification code mismatch',
                ]);
            }
        }
        else {
            return response()->json([
                'status' => '0',
                'messege' => 'Email does not exist',
            ]);
        }
    }

          

    public function login(Request $request){ 
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
        if(Auth::attempt($data)){ 
            $user = Auth::user(); 
            if($user->email_verified_at == null){
                return response()->json([
                    'status' => '0',
                    'messege' => 'Email is not verified',
                ]);
            }
            else{
                $tokenResult = $user->createToken('Laravel')->accessToken; 
                User::where('email',$request->email)->update(array('api_token' => $tokenResult->token));
                Auth::user()->update(array('api_token' => $tokenResult->token));
                $user = User::find(Auth::id());
                return response()->json([
                    'status' => '1',
                    'messege' => 'Login Successfully',
                    'user' =>  $user,
                ]);
            }
        } 
        else{ 
            return response()->json([
                    'status' => '0',
                    'messege' => 'User name and password does not match',
                ]);
        } 
    }


    public function forgotPassword(Request $request){
        $isEmailExist = User::where('email',$request->email)->first();
        if($isEmailExist){
            $verificationCode = rand(1000,9999);
            User::where('email',$request->email)->update(array('email_verification_code' => $verificationCode));
            $data = [
                'name' => $isEmailExist->firstname,
                'verification_code' => $verificationCode,
                'email' => $request->email
            ];
            MailController::sendEmail($data);
            return response()->json([
                'status' => '1',
                'messege' => 'Confirmation email sent'
            ]);
        }
        else{
            return response()->json([
                'status' => '0',
                'messege' => 'Email does not Already Exists'
            ]);
        }
    }


        public function updatePassword(Request $request){
        $userToken = User::where('email',$request->email)->first('email_verification_code');
        if($userToken){
            if($userToken->email_verification_code == $request->token){
            $current_date_time = Carbon::now()->toDateTimeString();
            User::where('email',$request->email)->update(array('email_verified_at' => $current_date_time));
            User::where('email',$request->email)->update(array('password' => Hash::make($request->password)));
            return response()->json([
            'status' => '1',
           'messege' => 'Password update',
            ]);
        }
        else{
            return response()->json([
            'status' => '0',
            'messege' => 'Email is not verified',
            ]);
        }
        }
        else {
            return response()->json([
            'status' => '0',
           'messege' => 'Email does not exist',
           ]);
        }
      
    }

     public function test(Request $request){
       
      dd("dsad");
    }













 }














