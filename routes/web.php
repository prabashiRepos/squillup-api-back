<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('stripe_success', 'App\Api\V1\Controllers\Parent\PlanController@stripeSuccess');
Route::get('stripe_fail', 'App\Api\V1\Controllers\Parent\PlanController@stripeFail');

Route::get('resetpassword/{token}', ['as' => 'password.reset', function($token){
    return view('reset')->with('token', $token);
}]);
