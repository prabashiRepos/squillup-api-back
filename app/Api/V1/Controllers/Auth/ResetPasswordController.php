<?php

namespace App\Api\V1\Controllers\Auth;

use DB;
use Auth;
use Hash;
use Config;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordController extends Controller
{
    public function checkResetPasswordToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $tokens = DB::table('password_resets')->where('email', $request->email)->get();

        if($tokens->count()){
            if (Hash::check($request->token, $tokens[0]->token)) {
                return response()->json(['status' => 'ok'], 201);
            }
        }

        throw new NotFoundHttpException(); 
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->reset($user, $password);
            }
        );

        if($response !== Password::PASSWORD_RESET) {
            throw new HttpException(500);
        }

        return response()->json([
            'code' => 201,
            'status' => 'ok',
        ], 201);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  ResetPasswordRequest  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function reset($user, $password)
    {
        $user->password = $password;
        $user->save();
    }
}
