<?php

namespace App\Api\V1\Controllers\ParentAuth;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Api\V1\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $lockoutTime = 10;
    protected $decayMinutes = 10;
    protected $maxLoginAttempts = 5;
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param 
     * @return \Illuminate\Http\JsonResponse
     */
    public function parentLogin(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $request->validate([
            'email'    => 'required|email:filter',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            $this->incrementLoginAttempts($request);
            throw new AccessDeniedHttpException("Your entered email or password is incorrect.");
        }

        $this->clearLoginAttempts($request);
        $user = User::where('email', $request['email'])->firstOrFail();
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
        // $updateLastLogin = User::find($user->id)->update(['last_login' => Carbon::now()]);

        return response()->json([
            'code'           => 201,
            'success' => true,
            'status'         => 'Login Success',
            'user'           => $user,
            'token_type'     => 'Bearer',
            'token'          => $token,
            'user_signature' => $userUniqueToken,
            'token_expiration' => config('sanctum.expiration'),
        ], 201);
    }
}