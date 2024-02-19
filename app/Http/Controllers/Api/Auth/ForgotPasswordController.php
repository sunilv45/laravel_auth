<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request){
        $this->validate($request,[
            'email' => 'required|email'
        ]);
        //dd($request->email);
        $email = $request->email;
        if(User::where('email', $email)->doesntExist()){
            return response(['message'=>'User email does not exists !!','status'=>400],200);
        }
        $token = Str::random(60);

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => now()->addHours(6)
        ]);

        //Send Mail
        Mail::send('mail.password_reset', ['token'=> $token], function($message) use($email){
            $message->to($email);
            $message->subject('Reset Your Password');
        });

        return response(['message'=> 'Password Reset link has been sent on the given email.','status'=>200],200);
    }

    public function reset(Request $request) {
        $this->validate($request,[
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $token = $request->token;
        $passwordReset = DB::table('password_reset_tokens')->where('token', $token)->first();
        if(!$passwordReset){
            return response(['message'=>'Token not found..!!','status'=>400],200);
        }

        if(!$passwordReset->created_at >= now()){
            return response(['message'=>'Token has expired...!','status'=>400],200);
        }
        $user = User::where('email',$passwordReset->email)->first();
        if(!$user){
            return response(['message'=>'User does not exists.','status'=>400],200);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('token', $token)->delete();
        return response(['message'=>'Password updated successfully.','status'=>200],200);
    }
}
