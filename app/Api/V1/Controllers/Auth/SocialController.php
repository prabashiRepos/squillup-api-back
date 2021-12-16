<?php

namespace App\Api\V1\Controllers\Common\Auth;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Log the user in with social medias
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Log the user in with social medias
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $provider_user = Socialite::with('google')->stateless()->user();

        if (!$user = User::where('email', $provider_user->email)->first()) {
            $user = User::create([
                'email' => $provider_user->email,
                'name' => $provider_user->name,
                // 'provider' => $this->provider,
                // 'provider_id' => $user->id
            ]);
        }

        $token = Auth()->login($user);

        $roles = array();
        $userUniqueString = $user->id.$user->email;
        $userUniqueToken = md5($userUniqueString);
        
        $user->roles->map(function($role) use(&$roles){
            array_push($roles, $role->name);
            return $role;
        });

        $user->roles_array = $roles;
        $updateLastLogin = User::find($user->id)->update(['last_login' => Carbon::now()]);

        return response()->json([
            'code'               => 201,
            'status'             => 'ok',
            'user'               => $user,
            'token'              => $token,
            'unique_user_token'  => $userUniqueToken,
            'expires_in_minutes' => Auth::guard()->factory()->getTTL(),
        ], 201);
    }
}