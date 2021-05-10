<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'API\AuthController@login');
Route::post('register', 'API\AuthController@register');

Route::post('forgot', 'API\AuthController@forgot');
Route::post('reset', 'API\AuthController@passwordReset');
Route::post('logout', 'API\AuthController@logout')->middleware('auth:api');
Route::put('update', 'API\AuthController@updateUserInformation')->middleware('auth:api');
Route::post('verify', 'API\AuthController@emailVerify');





Route::post('products', 'API\ProductController@index');
Route::post('product/{id}', 'API\ProductController@show');
Route::post('addProduct', 'API\ProductController@store')->middleware('auth:api');
Route::put('product/{id}', 'API\ProductController@update')->middleware('auth:api');
Route::post('deleteProduct', 'API\ProductController@deleteProduct')->middleware('auth:api');
Route::post('searchForProduct', 'API\ProductController@searchForProduct');

Route::post('addImage', 'API\ProductImageController@addImage')->middleware('auth:api');
Route::post('getProductImagesUrls/{id}', 'API\ProductImageController@getProductImagesUrls')->middleware('auth:api');
Route::post('deleteImage', 'API\ProductImageController@deleteImage')->middleware('auth:api');
