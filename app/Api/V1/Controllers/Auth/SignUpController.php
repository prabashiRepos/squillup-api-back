<?php

namespace App\Api\V1\Controllers\Auth;

use Auth;
use Config;
use Validator;
use App\Models\User;
// use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Hash;


use App\Notifications\SignupNotification;
use Illuminate\Support\Facades\Notification;


class SignUpController extends Controller
{   
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email'         => 'required|unique:users,email|email:filter|max:200',
            'phone'         => 'required|unique:users,phone|regex:/[0-9]{9}/',
            'country_code'  => 'sometimes|required|integer',
            'password'      => 'required|min:6|max:50',
        ]);

        $user = new User($request->all());

        if(!$user->save()) {
            throw new HttpException(500);
        }

        $user->attachRole("parent");
        $token = $user->createToken('auth_token')->plainTextToken;

        $roles = array();
        $permissions = array();
        $userUniqueString = $user->id.$user->email;
        $userUniqueToken = md5($userUniqueString);
        
        $user->roles->map(function($role) use(&$roles, &$permissions){
            $role->permissions->map(function($permission) use(&$permissions){
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

        try{
            $notify = Notification::send($user, new SignupNotification($user, $data));
        }
        catch(\Exception $e){
            
        }


        return response()->json([
            'code'           => 201,
            'status'         => 'ok',
            'user'           => $user,
            'token_type'     => 'Bearer',
            'token'          => $token,
            'user_signature' => $userUniqueToken,
            'token_expiration' => config('sanctum.expiration'),
        ], 201);
    }

    public function me(Request $request)
    {
        // return auth()->user();
        return $request->user();
    }


    public function signUp(Request $request, JWTAuth $JWTAuth, $role=null)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|max:100',
            'last_name'     => 'required|max:100',
            'company_name ' => 'nullable|max:100',
            'country'       => 'required|max:100',
            'email'         => 'required|unique:users,email|email:filter|max:100',
            'phone'         => 'sometimes|required|unique:users,phone|regex:/[0-9]{9}/',
            'country_code'  => 'sometimes|required|integer',
            'dob'           => 'sometimes|required|date|date_format:Y-m-d',
            'password'      => 'required|min:6',

            'city'             => 'nullable|max:200',
            'door'             => 'nullable|max:200',
            'floor'            => 'nullable|max:200',
            'street'           => 'nullable|max:200',
            'street_number'    => 'nullable|max:200',
            'zip_code'         => 'nullable|max:200',
            'by'               => 'nullable|max:500',
            'one_line_address' => 'nullable|max:1000',
            'use_address_as_subscriptions' => 'nullable|boolean',
            'use_elivery_address_as_subscriptions' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $additionalDataArray = array();
        foreach($request->all() as $key =>  $value){
            $exp_key = explode('_', $key);
            if(isset($exp_key[1])){
                if(($exp_key[0] == 'additional') && ($exp_key[1] == 'data')) $additionalDataArray[$key] = $value;
            }
        }

        $deliveryAddressArray = array();
        foreach($request->all() as $key =>  $value){
            $exp_key = explode('_', $key);
            if(isset($exp_key[1])){
                if(($exp_key[0] == 'delivery') && ($exp_key[1] == 'address')) $deliveryAddressArray[$key] = $value;
            }
        }

        $user = new User($request->all());
        $user->other_data = $additionalDataArray;
        $user->delivery_address = $deliveryAddressArray;

        if(!$user->save()) {
            throw new HttpException(500);
        }

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        if(is_null($role)) $user->attachRole('trainer'); 

        $roles = array();
        $token = $JWTAuth->fromUser($user);
        $userUniqueString = $user->id.$user->email;
        $userUniqueToken = md5($userUniqueString);

        $user->roles->map(function($role) use(&$roles){
            array_push($roles, $role->name);
            return $role;
        });

        $user->roles_array = $roles;

        return response()->json([
            'code'         => 201,
            'status'       => 'ok',
            'user'         => $user,
            'token'        => $token,
            'unique_token' => $userUniqueToken,
            'expires_in'   => Auth::guard()->factory()->getTTL() * 60
        ], 201);
    }
}