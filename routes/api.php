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
Route::post('resend', 'API\AuthController@resendCode');

//products
Route::post('products', 'API\ProductController@index');
Route::post('showProductsforUsers', 'API\ProductController@showProductsforUsers')->middleware('auth:api');
Route::post('product/{id}', 'API\ProductController@show');
Route::post('addProduct', 'API\ProductController@store')->middleware('auth:api');
Route::put('product/{id}', 'API\ProductController@update')->middleware('auth:api');
// Route::post('deleteProduct/{id}', 'API\ProductController@deleteProduct')->middleware('auth:api');
Route::post('softDeleteProduct/{id}', 'API\ProductController@softDeleteProduct')->middleware('auth:api');
Route::post('showDeletedProducts', 'API\ProductController@showDeletedProducts')->middleware('auth:api');
Route::post('restoreDeletedProduct/{id}', 'API\ProductController@restoreDeletedProduct')->middleware('auth:api');
Route::post('searchForProduct', 'API\ProductController@searchForProduct');
Route::post('searchForDeletedProduct', 'API\ProductController@searchForDeletedProduct')->middleware('auth:api');


Route::post('addImage', 'API\ProductImageController@addImage')->middleware('auth:api');
Route::post('getProductImagesUrls/{id}', 'API\ProductImageController@getProductImagesUrls');
Route::post('deleteImage', 'API\ProductImageController@deleteImage')->middleware('auth:api');

Route::post('addSize', 'API\ProductSizeController@addSize')->middleware('auth:api');
Route::post('getProductSizes/{id}', 'API\ProductSizeController@getProductSizes');
Route::post('deleteSize', 'API\ProductSizeController@deleteSize')->middleware('auth:api');

Route::post('addColorQuantityforCertainSize', 'API\ProductSizeColorQuantityController@addColorQuantityforCertainSize')->middleware('auth:api');
Route::post('getProductColorQuantityofCertainSize', 'API\ProductSizeColorQuantityController@getProductColorQuantityofCertainSize');
Route::post('deleteColorofCertainSize', 'API\ProductSizeColorQuantityController@deleteColorofCertainSize')->middleware('auth:api');
Route::post('updateColorQuantityofCertainSize', 'API\ProductSizeColorQuantityController@updateColorQuantityofCertainSize')->middleware('auth:api');

//favoraite products
Route::post('myFavoraiteProducts', 'API\FavoraiteProductsController@myFavoraiteProducts')->middleware('auth:api');
Route::post('addProducttoFavoriate', 'API\FavoraiteProductsController@addProducttoFavoriate')->middleware('auth:api');
Route::post('removeProductfromFavoriate', 'API\FavoraiteProductsController@removeProductfromFavoriate')->middleware('auth:api');

//Category
Route::post('categories', 'API\CategoryController@index');
Route::post('category/{id}', 'API\CategoryController@show');
Route::post('addCategory', 'API\CategoryController@store')->middleware('auth:api');
Route::put('category/{id}', 'API\CategoryController@update')->middleware('auth:api');
Route::post('deleteCategory', 'API\CategoryController@deleteCategory')->middleware('auth:api');

//Product Category
Route::post('showAllProductCategories/{id}', 'API\ProductCategoryController@showAllProductCategories');
Route::post('addCategorytoProduct', 'API\ProductCategoryController@addCategorytoProduct')->middleware('auth:api');
Route::post('removeProductfromCategory', 'API\ProductCategoryController@removeProductfromCategory')->middleware('auth:api');

//Coupons Routes
Route::get('displayCoupons','API\CouponController@displayCoupons')->middleware('auth:api');
Route::post('storeNewCoupon','API\CouponController@storeNewCoupon')->middleware('auth:api');
Route::put('updateCoupon/{id}','API\CouponController@updateCoupon')->middleware('auth:api');
Route::delete('destroyCoupon/{id}','API\CouponController@destroyCoupon')->middleware('auth:api');
Route::post('applyCoupon','API\CouponController@applyCoupon');

//UserBag
Route::middleware('auth:api')->group( function (){

Route::get('myBag','API\UserBagController@myBag');
Route::post('addTobag/{id}','API\UserBagController@addTobag');
Route::put('updateBag/{id}','API\UserBagController@updateBag');
Route::delete('deleteProductBag/{id}','API\UserBagController@deleteProductBag');
Route::delete('destroyBag','API\UserBagController@destroyBag');
Route::get('totalPrice','API\UserBagController@getTotalBagPrice');
});

//Order Routes
Route::middleware('auth:api')->group( function (){

    Route::post('makeOrder','API\OrderController@makeOrder');
    Route::get('allOrders','API\OrderController@allOrders');
    Route::get('getOpenedOrders','API\OrderController@getOpenedOrders');
    Route::get('getClosedOrders','API\OrderController@getClosedOrders');
    Route::post('confirmSend/{orderId}','API\OrderController@confirmSend');
    //Route::get('myOrders','API\OrderController@myOrders');

});

//Offer Routes
Route::get('offers','API\OfferController@offers');
Route::post('addNewOffer/{productId}','API\OfferController@addNewOffer')->middleware('auth:api');
Route::put('updateOffer/{idOffer}','API\OfferController@updateOffer')->middleware('auth:api');
Route::delete('destroyOffer/{idOffer}','API\OfferController@destroyOffer')->middleware('auth:api');
Route::get('productsOffers','API\OfferController@productsOffers');

Route::get('faq', 'API\FAQController@index');
Route::post('addQuestion', 'API\FAQController@store')->middleware('auth:api');
Route::put('update/question/{id}', 'API\FAQController@update')->middleware('auth:api');
Route::delete('delete/question/{id}', 'API\FAQController@deleteQuestion')->middleware('auth:api');

Route::get('oci', 'API\OwnerCommunicationInfoController@index');
Route::post('add/communication', 'API\OwnerCommunicationInfoController@store')->middleware('auth:api');
Route::put('update/communication/{id}', 'API\OwnerCommunicationInfoController@update')->middleware('auth:api');
Route::delete('delete/communication/{id}', 'API\OwnerCommunicationInfoController@delete')->middleware('auth:api');

Route::get('reviews', 'API\ReviewController@getReview');
Route::post('add/review', 'API\ReviewController@addReview')->middleware('auth:api');
Route::put('edit/review/{id}', 'API\ReviewController@editReview')->middleware('auth:api');
Route::delete('delete/review/{id}', 'API\ReviewController@deleteReview')->middleware('auth:api');

Route::post('sendEmail', 'API\SendEmailController@sendEmailForAllUsers')->middleware('auth:api');
Route::post('send/Email/for/users', 'API\SendEmailController@sendEmailForSpecificUsers')->middleware('auth:api');
Route::post('change/email/option/{id}', 'API\SendEmailController@changeAcceptEmailOption')->middleware('auth:api');

//push notification Routes
Route::middleware('auth:api')->group( function (){
    //send notification
Route::post('push-notification', 'API\PushNotificationController@sendPushNotification');
//store token
Route::post('save-token', 'API\PushNotificationController@saveToken');
//show all notification for one user
Route::get('display-notifications', 'API\PushNotificationController@displayNotifications');
//show notification content
Route::get('show-notification/{id}', 'API\PushNotificationController@showNotification');
//close receiving notifications
Route::post('close-notification', 'API\PushNotificationController@closeNotification');
//allow receive notifiations
Route::post('open-notification', 'API\PushNotificationController@openNotification');
//mark notification as read
Route::put('mark-asRead/{id}', 'API\PushNotificationController@markasread');
//delete notification for user
Route::delete('delete-notification/{id}', 'API\PushNotificationController@deleteNotification');
//delete all notifications for user
Route::delete('clear-notifications', 'API\PushNotificationController@clearNotifications');

});

//statistics
//Route::post('productsCount', 'API\StatisticsController@productsCount')->middleware('auth:api');
Route::post('allProductsTypeCount', 'API\StatisticsController@allProductsTypeCount')->middleware('auth:api');
Route::post('avaliableProductsTypeCount', 'API\StatisticsController@avaliableProductsTypeCount')->middleware('auth:api');
Route::post('soldProductsCount', 'API\StatisticsController@soldProductsCount')->middleware('auth:api');
Route::post('wallet', 'API\StatisticsController@wallet')->middleware('auth:api');
Route::post('quantitybyProductName', 'API\StatisticsController@quantitybyProductName')->middleware('auth:api');
Route::post('todaySoldProductsCount', 'API\StatisticsController@todaySoldProductsCount')->middleware('auth:api');
Route::post('lastWeekSoldProductsCounts', 'API\StatisticsController@lastWeekSoldProductsCounts')->middleware('auth:api');
Route::post('lastMonthSoldProductsCounts', 'API\StatisticsController@lastMonthSoldProductsCounts')->middleware('auth:api');
Route::post('yearByMonthSoldProductsCounts', 'API\StatisticsController@yearByMonthSoldProductsCounts')->middleware('auth:api');

// Route::post('LastWeekSoldProductsCount', 'API\StatisticsController@LastWeekSoldProductsCount')->middleware('auth:api');
// Route::post('LastMonthSoldProductsCount', 'API\StatisticsController@LastMonthSoldProductsCount')->middleware('auth:api');

