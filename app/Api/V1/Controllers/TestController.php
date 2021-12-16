<?php

namespace App\Api\V1\Controllers;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TestController extends Controller
{
    
    public function Test(Request $request)
    {

        
    }
}