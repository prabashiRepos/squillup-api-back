<?php

namespace App\Api\V1\Controllers\Parent;

use DB;
use Auth;
use Validator;
use App\Models\Plan;
use App\Models\User;
use App\Models\UsersPlan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use \Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlanController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:sanctum', []);
        // $this->middleware('ability:parent,buy-plan', ['only' => ['buyPlan']]);
        // $this->middleware('ability:parent,order-plan', ['only' => ['index']]);
        // $this->middleware('ability:parent,order-plan', ['only' => ['update']]);
        // $this->middleware('ability:parent,order-plan', ['only' => ['delete']]);

        $this->middleware('role:parent', ['only' => ['viewMyInvoices']]);
        $this->middleware('role:parent', ['only' => ['cancelPlan']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyPlan(Request $request)
    {
        $user = Auth::guard()->user(); 
        if($user->hasRole('parent'))  $request->merge(["parent_id" =>  $user->id]);

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'plan_type' => 'required|in:monthly,yearly',
            'parent_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $planType = $request->plan_type;
        $plan = Plan::find($request->plan_id);
        $planPrice = $plan[$planType.'_price'];

        $checkPlanExists = UsersPlan::where('user_id', $user->id)
                                    // ->where('plan_id', $plan->id)
                                    ->whereIn('status', ['active', 'succeeded'])
                                    ->count();

        if($checkPlanExists) return response()->json(['status' => false, 'message' => 'plan_already_exists' ], 200);

        if($planPrice != 0){
            $encriptedUser = Crypt::encrypt($user->id);
            $planPriceKey = ($planType == 'monthly') ? 'stripe_monthly_data' : 'stripe_yearly_data';

            if(isset($plan[$planPriceKey]) && isset($plan[$planPriceKey]['id'])){
                $planPriceId = $plan[$planPriceKey]['id'];

                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $checkout_session = \Stripe\Checkout\Session::create([
                    'line_items' => [[
                        'price' => $planPriceId,
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => [
                        'card',
                    ],
                    'mode' => ($planType == 'monthly') ? 'subscription' : 'payment',
                    'success_url' => env('APP_URL').'/stripe_success?user_id='.$encriptedUser,
                    'cancel_url' => env('APP_URL').'/stripe_fail?user_id='.$encriptedUser,
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                    ]
                ]);
         
                $saveUserPlan = UsersPlan::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'price' => $planPrice,
                    'start_date' => null,
                    'end_date' => null,
                    'data' => $checkout_session,
                    'created_at' => date('Y-m-d h:i:s'),
                ]);

                // return Redirect::to($checkout_session->url);
                return response()->json(['url' => $checkout_session->url], 201);
            }
            else return response()->json([ 'status' => false, 'message' => 'no_price_data'], 200);
        }
        else{
            $saveUserPlan = UsersPlan::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'price' => $planPrice,
                'start_date' => null,
                'end_date' => null,
                'created_at' => date('Y-m-d h:i:s'),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'success',
            ], 201);
        }
    }

    public function viewMyPlans(Request $request)
    {
        $user = Auth::guard()->user(); 
        if($user->hasRole('parent'))  $request->merge(["parent_id" =>  $user->id]);

        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $parent = User::find($request->parent_id);

        $myPlans = UsersPlan::with(['plan' => function($query) {
                                        $query->select('id', 'name');
                                    }])
                                    ->where('user_id', $parent->id)
                                    ->whereIn('status', ['active', 'succeeded'])
                                    ->get()
                                    ->makeHidden(['data', 'invoices']);

        return response()->json($myPlans, 201);
    }

    public function viewMyInvoices(Request $request)
    {
        $user = Auth::guard()->user(); 
        if($user->hasRole('parent'))  $request->merge(["parent_id" =>  $user->id]);

        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $parent = User::find($request->parent_id);

        $myInvoices = UsersPlan::select('user_id', 'status', 'invoices')
                                    ->where('user_id', $parent->id)
                                    ->whereIn('status', ['active', 'succeeded'])
                                    ->get();

        return response()->json($myInvoices, 201);
    }

    public function changePlan(Request $request)
    {
        $user = Auth::guard()->user(); 
        if($user->hasRole('parent'))  $request->merge(["parent_id" =>  $user->id]);

        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'plan_type' => 'required|in:monthly,yearly',
            'id' => 'required|exists:users_plans,id,user_id,'.$request->parent_id,
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $parent = User::find($request->parent_id);

        $myPlan = UsersPlan::where('user_id', $parent->id)
                                ->whereIn('status', ['active', 'succeeded'])
                                ->find($request->id);

        if($myPlan){
            if($myPlan->plan_id != $request->plan_id){
                $myPlan->status = 'canceled';
                $myPlan->save();
                return $this->buyPlan($request);
            }
            else return response()->json(['status' => false, 'message' => 'you_already_in_this_plan'], 200);
        }
        else return response()->json(['status' => false, 'message' => 'no_active_plans'], 200);
    }

    public function cancelPlan(Request $request)
    {
        $user = Auth::guard()->user(); 
        if($user->hasRole('parent'))  $request->merge(["parent_id" =>  $user->id]);

        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
            'id' => 'required|exists:users_plans,id,user_id,'.$request->parent_id,
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $parent = User::find($request->parent_id);

        $myPlan = UsersPlan::where('user_id', $parent->id)
                                ->whereIn('status', ['active', 'succeeded'])
                                ->find($request->id);

        if($myPlan){
            $myPlan->status = 'canceled';
            $myPlan->save();

            return response()->json(['status' => true, 'message' => 'plan_canceled'], 201);
        }
        else return response()->json(['status' => false, 'message' => 'no_active_plans'], 200);
    }

    public function stripeSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $decriptedUser = Crypt::decrypt($request->user_id);
        $checkPlanExists = UsersPlan::where('user_id', $decriptedUser)->whereIn('status', ['active', 'succeeded'])->count();
        if($checkPlanExists) return response()->json(['status' => false, 'message' => 'plan_already_exists' ], 200);

        
        $checkPlan = UsersPlan::where('user_id', $decriptedUser)->orderBy('id', 'DESC')->first();

        if($checkPlan){
            $session = $checkPlan;
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            if(isset($session->data['mode']) && $session->data['mode'] == 'payment'){
                $payment = $stripe->paymentIntents->retrieve(
                    $session->data['payment_intent'],
                    []
                );

                if(isset($payment['status']) && $payment['status'] == 'succeeded'){
                    $deletePlan = UsersPlan::where('user_id', $decriptedUser)->whereNotIn('id', [$session->id])->delete();
                    $checkPlan->start_date = date("Y-m-d h:i:s");
                    $checkPlan->end_date = date('Y-m-d  h:i:s', strtotime(date("Y-m-d h:i:s"). ' + 1 years'));
                    $checkPlan->status = 'succeeded';
                    $checkPlan->save();

                    return response()->json([ 'status' => true, 'message' => 'success'], 201);
                }
                else return response()->json([ 'status' => false, 'message' => 'payment_status_error_in_payment'], 200);
            }
            else{
                $currentSession = $stripe->checkout->sessions->retrieve(
                    $session->data['id'],
                    []
                );

                if(isset($currentSession['subscription'])){
                    $subscription = $stripe->subscriptions->retrieve(
                        $currentSession['subscription'],
                        []
                    );

                    if(isset($subscription['status']) && $subscription['status'] == 'active'){
                        $deletePlan = UsersPlan::where('user_id', $decriptedUser)->whereNotIn('id', [$session->id])->delete();
                        $checkPlan->start_date = date("Y-m-d h:i:s");
                        $checkPlan->end_date = date('Y-m-d  h:i:s', strtotime(date("Y-m-d h:i:s"). ' + 30 days'));
                        $checkPlan->status = 'active';
                        $checkPlan->save();

                        $invoices = $stripe->invoices->all(['subscription' => $subscription['id']]);
                        $checkPlan->invoices = $invoices['data'];
                        $checkPlan->save();

                        return response()->json([ 'status' => true, 'message' => 'success'], 201);
                    }
                    else return response()->json([ 'status' => false, 'message' => 'payment_status_error_in_subscription'], 200);
                }
                else return response()->json([ 'status' => false, 'message' => 'subscription_status_error'], 200);
            }
        }
        return response()->json([ 'status' => false, 'message' => 'no_plans'], 201);
    }

    public function stripe_fail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $decriptedUser = Crypt::decrypt($request->user_id);
        $deletePlan = UsersPlan::where('user_id', $decriptedUser)->delete();

        return response()->json([ 'status' => false, 'message' => 'payment_fail'], 201);
    }
}
