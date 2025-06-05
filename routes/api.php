<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Api\v1\Driver\ComplaintDriverController;
use App\Http\Controllers\Api\v1\Driver\OrderDriverController;
use App\Http\Controllers\Api\v1\Driver\RatingDriverController;
use App\Http\Controllers\Api\v1\Driver\ServiceDriverController;
use App\Http\Controllers\Api\v1\Driver\HomeDriverController;
use App\Http\Controllers\Api\v1\Driver\WalletDriverController;
use App\Http\Controllers\Api\v1\Driver\WithdrawalRequestDriverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\User\AuthController;
use App\Http\Controllers\Api\v1\User\UserAddressController;
use App\Http\Controllers\Api\v1\User\UploadPhotoVoiceController;

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

//Route unAuth
Route::group(['prefix' => 'v1/user'], function () {

    //---------------- Auth --------------------//
    Route::get('/getOptions', [OptionController::class, 'getOptions']);
    Route::post('/check-phone', [AuthController::class, 'checkPhone']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/services', [ServicesController::class, 'index']);
    Route::get('/pages/{type}', [PageController::class, 'index']);

    // Auth Route
    Route::group(['middleware' => ['auth:user-api']], function () {

        Route::get('/active', [AuthController::class, 'active']);
        Route::post('/withdrawal/request',  [WithdrawalRequestUserController::class, 'requestWithdrawal']);

        // image for chat
        Route::get('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'index']);
        Route::post('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'store']);

        Route::post('/update_profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/delete_account', [AuthController::class, 'deleteAccount']);
        Route::get('/userProfile', [AuthController::class, 'userProfile']);

        //Notification
        Route::get('/notifications', [AuthController::class, 'notifications']);
        Route::post('/notifications', [AuthController::class, 'sendToUser']);

        Route::post('/ratings', [RatingController::class, 'store']);

        Route::get('/addresses', [UserAddressController::class, 'index']);
        Route::post('/addresses', [UserAddressController::class, 'store']);
        Route::get('/addresses/{id}', [UserAddressController::class, 'show']);
        Route::put('/addresses/{id}', [UserAddressController::class, 'update']);
        Route::delete('/addresses/{id}', [UserAddressController::class, 'destroy']);

        Route::get('/wallet/transactions', [WalletController::class, 'getTransactions']);

        Route::get('/complaints', [ComplaintController::class, 'index']);
        Route::post('/complaints', [ComplaintController::class, 'store']);
        Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
        Route::put('/complaints/{id}', [ComplaintController::class, 'update']);
        Route::delete('/complaints/{id}', [ComplaintController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/active', [OrderController::class, 'activeOrders']);
        Route::get('/orders/completed', [OrderController::class, 'completedOrders']);
        Route::get('/orders/cancelled', [OrderController::class, 'cancelledOrders']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

        Route::get('/coupons', [CouponController::class, 'index']);
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
    });
});



// Driver

Route::group(['prefix' => 'v1/driver'], function () {

    //---------------- Auth --------------------//
    Route::get('/getOptions', [OptionController::class, 'getOptions']);


    // Auth Route
    Route::group(['middleware' => ['auth:driver-api']], function () {

        Route::get('/active', [AuthController::class, 'active']);
        Route::post('/updateStatus', [AuthController::class, 'updateStatusOnOff']);
        Route::post('/homeDriver', HomeDriverController::class);
        Route::post('/withdrawal/request',  [WithdrawalRequestDriverController::class, 'requestWithdrawal']);

        // image for chat
        Route::get('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'index']);
        Route::post('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'store']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update_profile', [AuthController::class, 'updateProfile']);
        Route::post('/delete_account', [AuthController::class, 'deleteAccount']);
        Route::get('/driverProfile', [AuthController::class, 'driverProfile']);
        //Notification
        Route::get('/notifications', [AuthController::class, 'notifications']);
        Route::post('/notifications', [AuthController::class, 'sendToUser']);

        Route::get('/ratings', [RatingDriverController::class, 'index']);
        Route::get('/getServices', [ServiceDriverController::class, 'index']);
        Route::post('/storeOrUpdateStatus', [ServiceDriverController::class, 'storeOrUpdateStatus']);
        Route::get('/wallet/transactions', [WalletDriverController::class, 'getTransactions']);

        Route::get('/complaints', [ComplaintDriverController::class, 'getTransactions']);

        Route::get('/orders', [OrderDriverController::class, 'index']);
        Route::get('/orders/active', [OrderDriverController::class, 'activeOrders']);
        Route::get('/orders/completed', [OrderDriverController::class, 'completedOrders']);
        Route::get('/orders/cancelled', [OrderDriverController::class, 'cancelledOrders']);
        Route::post('/orders', [OrderDriverController::class, 'store']);
        Route::get('/orders/{id}', [OrderDriverController::class, 'show']);
        Route::post('/orders/{id}/cancel', [OrderDriverController::class, 'cancelOrder']);
        Route::post('/orders/{id}/status', [OrderDriverController::class, 'updateStatus']);
    });
});
