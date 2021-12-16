<?php

namespace App\Api\V1\Controllers\Common\Stripe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StripeProductController extends Controller
{
    /**
     * Create a new RoleController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', []);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|max:200',
        ]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        return $stripe->products->create(
            ['name' =>  $request->name]
        );
    }

    public function updateProduct(Request $request, $productId=null)
    {
        $request->validate([
            'name' => 'required|max:200',
        ]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        return $stripe->products->update(
            $productId,
            ['name' =>  $request->name]
        );
    }

    public function deleteProduct($productId)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        return $stripe->products->delete(
            $productId,
            []
        );
    }

    public function createMonthlyPriceWithProduct(Request $request, $productId=null)
    {
        $request->validate([
            'name' => 'required|max:200',
            'monthly_price' => 'required|integer',
            'yearly_price' => 'required_without:yearly_discount|integer',
        ]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $priceData = [
            'unit_amount' => $request->monthly_price*100,
            'currency' => env('STRIPE_CURRENCY'),
            'recurring' => ['interval' => 'month'],
        ];

        if($productId) $priceData['product'] =  $productId;
        else $priceData['product_data'] = ['name' =>  $request->name];

        return $stripe->prices->create($priceData);
    }

    public function createYearlyPriceWithProduct(Request $request, $productId=null)
    {
        $request->validate([
            'name' => 'required|max:200',
            'monthly_price' => 'required|integer',
            'yearly_price' => 'required_without:yearly_discount|integer',
        ]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $priceData = [
            'unit_amount' => $request->yearly_price*100,
            'currency' => env('STRIPE_CURRENCY'),
            // 'recurring' => ['interval' => 'year'],
        ];

        if($productId) $priceData['product'] =  $productId;
        else $priceData['product_data'] = ['name' =>  $request->name];

        return $stripe->prices->create($priceData);
    }
}
