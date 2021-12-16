<?php

namespace App\Api\V1\Controllers\ParentAuth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Notifications\SignupNotification;

use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Authentication;
use Illuminate\Support\Facades\Validator;

class SignUpController extends Controller
{
    
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|unique:users,email|email:filter|max:200',
            'phone' => 'required|unique:users,phone|regex:/[0-9]{9}/',
            'country_code' => 'sometimes|required|integer',
            'password' => 'required|min:6|max:50',
            'conf_password' => 'required|min:6|max:50',
            //'captcha' => 'required|captcha',
        ]);

        $user = new User($request->all());

        if (!$user->save()) {
            throw new HttpException(500);
        }

        $user->attachRole("parent");
        $token = $user->createToken('auth_token')->plainTextToken;

        $roles = array();
        $permissions = array();
        $userUniqueString = $user->id . $user->email;
        $userUniqueToken = md5($userUniqueString);

        $user->roles->map(function ($role) use (&$roles, &$permissions) {
            $role->permissions->map(function ($permission) use (&$permissions) {
                array_push($permissions, $permission->name);
                return $permission;
            });

            array_push($roles, $role->name);
            return $role;
        });

        $user->roles_array = $roles;
        $user->permissions_array = $permissions;

        $data = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => $request->password,
        ];

        try {
            $notify = Notification::send($user, new SignupNotification($user, $data));
        } catch (\Exception$e) {

        }

        return response()->json([
            'code' => 201,
            'status' => 'ok',
            'user' => $user,
            'token_type' => 'Bearer',
            'token' => $token,
            'user_signature' => $userUniqueToken,
            'token_expiration' => config('sanctum.expiration'),
        ], 201);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:users,email|email:filter|max:200',
        ]);

        $otp = rand(1000, 9999);

        $data = [
            'email' => $request->email,
            'otp' => $otp,
        ];

        $authentication = new Authentication();
        $authentication->otp = $otp;
        $authentication->expired = true;
        $authentication->save();

        try {
            $notify = Notification::send(new OtpNotification($data));
        } catch (\Exception$e) {

        }

        return response()->json(['status' => Lang::get('messages.request_otp_success')], 201);
    }

    public function verifyOtp(Request $request)
    {

        $authentication = Authentication::where([['otp', '=', $request->otp], ['expired', '=', true]])->first();

        if ($authentication) {
            //auth()->login($user, true);
            Authentication::where('otp', '=', $request->otp)->update(['expired' => false]);
            //$accessToken = auth()->user()->createToken('authToken')->accessToken;
            response()->json(['status' => Lang::get('messages.otp_verified')], 201);
            //return response(["status" => 200, "message" => "Success", 'user' => auth()->user(), 'access_token' => $accessToken]);
        } else {
            return response()->json(['status' => Lang::get('messages.invalid_otp')], 401);

        }
    }
}
