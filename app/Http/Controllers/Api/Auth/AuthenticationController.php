<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function login(Request $request){
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $login = $request->only('email','password');
        if(!Auth::attempt($login)){
            return response(['message'=>'Invalid Login Credential!!'],401);
        }
        /**
         * @var User $user
         */
        $user = Auth::user();
        $token = $user->createToken($user->name);
        return response([
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email,
            'created_at'=>$user->created_at,
            'updated_at'=>$user->updated_at,
            'token' => $token->accessToken,
            'token_expires_at' => $token->token->expires_at,
        ],200);
    }

    public function logout(Request $request){
        $this->validate($request,[
            'allDevice' => 'required|boolean',
        ]);
        /**
         * @var User $user
         */
        $user = Auth::user();
        if($request->allDevice){
            $user->tokens->each(function($token){
                $token->delete();
            });
            return response(['message'=>'Logged out from all devices !!','status'=>200],200);
        }else{
            $userToken = $user->token();
            $userToken->delete();
            return response(['message'=>'Logged out successfully !!','status'=>200],200);
        }
        
    }
}
