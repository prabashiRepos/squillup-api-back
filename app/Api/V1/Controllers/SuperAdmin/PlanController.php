<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use DB;
use Auth;
use App\Models\Plan;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Api\V1\Controllers\Common\Stripe\StripeProductController;

class PlanController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
        $this->middleware('ability:developer,create-plan', ['only' => ['store']]);
        $this->middleware('ability:developer,view-plan', ['only' => ['index']]);
        $this->middleware('ability:developer,update-plan', ['only' => ['update']]);
        $this->middleware('ability:developer,delete-plan', ['only' => ['delete']]);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:200',
            'monthly_price' => 'required|integer',
            'yearly_price' => 'required_without:yearly_discount|integer',
            'yearly_discount' => 'required_without:yearly_price|integer',

            'restrictions' => 'sometimes|required|array',
            'restrictions.*video_per_chapter' => 'nullable|integer',
            'restrictions.*q_a' => 'nullable|integer',
            'restrictions.*self_test_per_chapter' => 'nullable|integer',
            'restrictions.*past_paper_topic_interactive' => 'nullable|integer',
            'restrictions.*past_paper_topic_pdf' => 'nullable|integer',
            'restrictions.*past_paper_year_interactive' => 'nullable|integer',
            'restrictions.*past_paper_year_pdf' => 'nullable|integer',
            'restrictions.*past_paper_marking_scheme_pdf' => 'nullable|integer',
            'restrictions.*chalanges_per_chapter' => 'nullable|integer',
            'restrictions.*free_resources' => 'nullable|integer',
            'restrictions.*email_response' => 'nullable|string|in:general_queries,all',
            'restrictions.*work_sheet_per_chapter' => 'nullable|integer',

            'max_students' => 'required_without:min_students|integer',
            'min_students' => 'required_without:max_students|integer',
        ]);

        $plan = new Plan($request->all());

        $restrictions = [
            'video_per_chapter' => isset($request->restrictions['video_per_chapter']) ? $request->restrictions['video_per_chapter'] : null,
            'q_a' => isset($request->restrictions['q_a']) ? $request->restrictions['q_a'] : null,
            'self_test_per_chapter' => isset($request->restrictions['self_test_per_chapter']) ? $request->restrictions['self_test_per_chapter'] : null,
            'past_paper_topic_interactive' => isset($request->restrictions['past_paper_topic_interactive']) ? $request->restrictions['past_paper_topic_interactive'] : null,
            'past_paper_topic_pdf' => isset($request->restrictions['past_paper_topic_pdf']) ? $request->restrictions['past_paper_topic_pdf'] : null,
            'past_paper_year_interactive' => isset($request->restrictions['past_paper_year_interactive']) ? $request->restrictions['past_paper_year_interactive'] : null,
            'past_paper_year_pdf' => isset($request->restrictions['past_paper_year_pdf']) ? $request->restrictions['past_paper_year_pdf'] : null,
            'past_paper_marking_scheme_pdf' => isset($request->restrictions['past_paper_marking_scheme_pdf']) ? $request->restrictions['past_paper_marking_scheme_pdf'] : null,
            'chalanges_per_chapter' => isset($request->restrictions['chalanges_per_chapter']) ? $request->restrictions['chalanges_per_chapter'] : null,
            'free_resources' => isset($request->restrictions['free_resources']) ? $request->restrictions['free_resources'] : null,
            'email_response' => isset($request->restrictions['email_response']) ? $request->restrictions['email_response'] : null,
            'work_sheet_per_chapter' => isset($request->restrictions['work_sheet_per_chapter']) ? $request->restrictions['work_sheet_per_chapter'] : null,
        ];

        $plan->restrictions = $restrictions;

        if($plan->save()){
            if(($request->monthly_price != 0) || ($request->yearly_price != 0)){
                $stripeCon = new StripeProductController();
                $stripeProduct = $stripeCon->createProduct($request);
                $productId = isset($stripeProduct['id']) ? $stripeProduct['id'] : null;

                if($request->monthly_price != 0){
                    $plan->stripe_monthly_data = $stripeCon->createMonthlyPriceWithProduct($request, $productId);
                }

                if($request->yearly_price != 0){
                    $plan->stripe_yearly_data = $stripeCon->createYearlyPriceWithProduct($request, $productId);
                }

                $plan->save();
            }
            
            return response()->json([
                'code'   => 201,
                'data'   => $plan,
                'status' => Lang::get('messages.plan_create_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.plan_create_fail')], 200);
    }

    public function index(Request $request)
    {
        $plans = Plan::get();
        return response()->json($plans, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:plans,id',
            'name' => 'required|max:200',
            'monthly_price' => 'required|integer',
            'yearly_price' => 'required_without:yearly_discount|integer',
            'yearly_discount' => 'required_without:yearly_price|integer',

            'restrictions' => 'sometimes|required|array',
            'restrictions.*video_per_chapter' => 'nullable|integer',
            'restrictions.*q_a' => 'nullable|integer',
            'restrictions.*self_test_per_chapter' => 'nullable|integer',
            'restrictions.*past_paper_topic_interactive' => 'nullable|integer',
            'restrictions.*past_paper_topic_pdf' => 'nullable|integer',
            'restrictions.*past_paper_year_interactive' => 'nullable|integer',
            'restrictions.*past_paper_year_pdf' => 'nullable|integer',
            'restrictions.*past_paper_marking_scheme_pdf' => 'nullable|integer',
            'restrictions.*chalanges_per_chapter' => 'nullable|integer',
            'restrictions.*free_resources' => 'nullable|integer',
            'restrictions.*email_response' => 'nullable|string|in:general_queries,all',
            'restrictions.*work_sheet_per_chapter' => 'nullable|integer',

            'max_students' => 'required_without:min_students|integer',
            'min_students' => 'required_without:max_students|integer',
        ]);

        $restrictions = [
            'video_per_chapter' => isset($request->restrictions['video_per_chapter']) ? $request->restrictions['video_per_chapter'] : null,
            'q_a' => isset($request->restrictions['q_a']) ? $request->restrictions['q_a'] : null,
            'self_test_per_chapter' => isset($request->restrictions['self_test_per_chapter']) ? $request->restrictions['self_test_per_chapter'] : null,
            'past_paper_topic_interactive' => isset($request->restrictions['past_paper_topic_interactive']) ? $request->restrictions['past_paper_topic_interactive'] : null,
            'past_paper_topic_pdf' => isset($request->restrictions['past_paper_topic_pdf']) ? $request->restrictions['past_paper_topic_pdf'] : null,
            'past_paper_year_interactive' => isset($request->restrictions['past_paper_year_interactive']) ? $request->restrictions['past_paper_year_interactive'] : null,
            'past_paper_year_pdf' => isset($request->restrictions['past_paper_year_pdf']) ? $request->restrictions['past_paper_year_pdf'] : null,
            'past_paper_marking_scheme_pdf' => isset($request->restrictions['past_paper_marking_scheme_pdf']) ? $request->restrictions['past_paper_marking_scheme_pdf'] : null,
            'chalanges_per_chapter' => isset($request->restrictions['chalanges_per_chapter']) ? $request->restrictions['chalanges_per_chapter'] : null,
            'free_resources' => isset($request->restrictions['free_resources']) ? $request->restrictions['free_resources'] : null,
            'email_response' => isset($request->restrictions['email_response']) ? $request->restrictions['email_response'] : null,
            'work_sheet_per_chapter' => isset($request->restrictions['work_sheet_per_chapter']) ? $request->restrictions['work_sheet_per_chapter'] : null,
        ];

        $plan = Plan::find($request->id);
        $oldPlanData = Plan::find($request->id);
        $plan->fill($request->all());
        $plan->restrictions = $restrictions;

        if($plan->save()){
            if($plan->name != $oldPlanData->name){
                if($plan->stripe_monthly_data && isset($plan->stripe_monthly_data['product'])){
                    $stripeCon = new StripeProductController();
                    $productId = $plan->stripe_monthly_data['product'];
                    $stripeCon->updateProduct($request, $productId);
                }
            }

            if($plan->monthly_price != $oldPlanData->monthly_price){
                if($plan->stripe_monthly_data && isset($plan->stripe_monthly_data['id'])){
                    $stripeCon = new StripeProductController();
                    $productId = isset($plan->stripe_monthly_data['product']) ? $plan->stripe_monthly_data['product'] : null;
                    $plan->stripe_monthly_data = $stripeCon->createMonthlyPriceWithProduct($request, $productId);
                }
            }

            if($plan->yearly_price != $oldPlanData->yearly_price){
                if($plan->stripe_yearly_data && isset($plan->stripe_yearly_data['id'])){
                    $stripeCon = new StripeProductController();
                    $productId = isset($plan->stripe_yearly_data['product']) ? $plan->stripe_yearly_data['product'] : null;
                    $plan->stripe_yearly_data = $stripeCon->createYearlyPriceWithProduct($request, $productId);
                }
            }
            
            $plan->save();
            
            return response()->json([
                'code'   => 201,
                'data'   => $plan,
                'status' => Lang::get('messages.plan_update_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.plan_update_fail')], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::find($request->id);
        $oldPlanData = $plan;

        if($plan->delete()){
            // if($plan->stripe_monthly_data && isset($plan->stripe_monthly_data['product'])){
            //     $stripeCon = new StripeProductController();
            //     $productId = $plan->stripe_monthly_data['product'];
            //     $stripeCon->deleteProduct($productId);
            // }

            return response()->json([
                'code'   => 201,
                'status' => Lang::get('messages.plan_delete_success'),
            ], 201);
        }
        else return response()->json(['code' => 200, 'status' => Lang::get('messages.plan_delete_fail')], 200);
    }
}
