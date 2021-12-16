<?php

namespace App\Api\V1\Controllers\Auth;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ForgotPasswordController extends Controller
{
    public function sendResetEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'redirect_url' => 'required|url',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $user = User::where('email', '=', $request->get('email'))->first();

        if(!$user) {
            throw new NotFoundHttpException();
        }

        $broker = $this->getPasswordBroker();
        $sendingResponse = $broker->sendResetLink($request->only('email'));

        if($sendingResponse !== Password::RESET_LINK_SENT) {
            throw new HttpException(500);
        }
        
        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    private function getPasswordBroker()
    {
        return Password::broker();
    }
}
