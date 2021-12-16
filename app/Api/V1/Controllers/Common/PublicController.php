<?php

namespace App\Api\V1\Controllers\Common;

use Auth;
use Validator;
use App\Models\Plan;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicController extends Controller
{
    use Helpers;
    public function __construct()
    {
        
    }

    public function viewPlans(Request $request)
    {
        $plans = Plan::get()->makeHidden(['stripe_monthly_data', 'stripe_yearly_data']);
        return response()->json($plans, 201);
    }
}