<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Api\v1\Provider\AppointmentProviderController;
use App\Http\Controllers\Api\v1\User\ProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\User\AuthController;
use App\Http\Controllers\Api\v1\User\UserAddressController;
use App\Http\Controllers\Api\v1\User\UploadPhotoVoiceController;
use App\Http\Controllers\Api\v1\User\RatingController;
use App\Http\Controllers\Api\v1\User\DeliveryController;
use App\Http\Controllers\Api\v1\User\TypeController;
use App\Http\Controllers\Api\v1\User\FavouriteController;
use App\Http\Controllers\Api\v1\Provider\AuthProviderController;
use App\Http\Controllers\Api\v1\Provider\DiscountController;
use App\Http\Controllers\Api\v1\Provider\WithdrawalRequestProviderController;
use App\Http\Controllers\Api\v1\Provider\RatingProviderController;
use App\Http\Controllers\Api\v1\Provider\WalletProviderController;

use App\Http\Controllers\Api\v1\Provider\ForgotPasswordProviderController;
use App\Http\Controllers\Api\v1\Provider\UploadPhotoVoiceProviderController;

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
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/google-login', [AuthController::class, 'googleLogin']);
    Route::post('/apple-login', [AuthController::class, 'appleLogin']);
    Route::get('/banners', [BannerController::class, 'index']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::get('/pages/{type}', [PageController::class, 'index']);
    Route::get('/getServices',  [ServiceController::class, 'index']);
    Route::get('/getTypes', [TypeController::class, 'index']);

    Route::post('/check-phone', [ForgotPasswordController::class, 'checkPhone']);

    // Update password
    Route::post('/update-password', [ForgotPasswordController::class, 'updatePassword']);




    // Auth Route
    Route::group(['middleware' => ['auth:user-api']], function () {


        // هدول كانو برة الauth بس دخلتهم جوا عشان favourite
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'getProductsFromCategory']);

        Route::get('/products/{id}', [ProductController::class, 'productDetails']);
        Route::get('product/search', [ProductController::class, 'searchProduct']);
        Route::get('/providers/type/{typeId}', [ProviderController::class, 'getProvidersByType']);
        Route::get('/providers/{providerId}', [ProviderController::class, 'getProviderDetails']);
        Route::get('/allProviders', [ProviderController::class, 'getMapLocations']);
        Route::get('provider/search', [ProviderController::class, 'searchProviders']);
        Route::get('provider/vip', [ProviderController::class, 'getVipProviders']);
        // end

        Route::get('/active', [AuthController::class, 'active']);

        // image for chat
        Route::get('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'index']);
        Route::post('/uploadPhotoVoice', [UploadPhotoVoiceController::class, 'store']);

        Route::post('/update_profile', [AuthController::class, 'updateProfile']);
        Route::post('/delete_account', [AuthController::class, 'deleteAccount']);
        Route::get('/userProfile', [AuthController::class, 'userProfile']);

        //Notification
        Route::get('/notifications', [AuthController::class, 'notifications']);
        Route::post('/notifications', [AuthController::class, 'sendMessage']);

        Route::post('/ratings', [RatingController::class, 'store']);
        Route::post('/product/ratings', [RatingController::class, 'storeRatingProduct']);

        Route::get('/addresses', [UserAddressController::class, 'index']);
        Route::post('/addresses', [UserAddressController::class, 'store']);
        Route::put('/addresses/{id}', [UserAddressController::class, 'update']);
        Route::delete('/addresses/{id}', [UserAddressController::class, 'destroy']);
        Route::post('/addresses/calculate-delivery-fee', [UserAddressController::class, 'calculateDeliveryFee']);

        Route::get('/wallet/transactions', [WalletController::class, 'getTransactions']);

        //Ecommerce

        Route::get('/productFavourites', [FavouriteController::class, 'index']);
        Route::post('/productFavourites', [FavouriteController::class, 'store']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::delete('/cart/{id}', [CartController::class, 'delete']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'details']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

        Route::get('/coupons', [CouponController::class, 'index']);
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
        // End Ecommerce

        //Provider Display in user app

        Route::get('/providerFavourites', [FavouriteController::class, 'indexProvider']);
        Route::post('/providerFavourites', [FavouriteController::class, 'storeProvider']);

        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentController::class, 'index']); // GET /api/appointments
            Route::post('/', [AppointmentController::class, 'store']); // POST /api/appointments
            Route::put('/{appointmentId}', [AppointmentController::class, 'update']);
            Route::post('/{appointmentId}/select-payment-method', [AppointmentController::class, 'selectPaymentMethod']);
            Route::post('/{appointmentId}/status', [AppointmentController::class, 'updateAppointmentStatus']);

            // User can view appointments requiring payment method selection
            Route::get('/pending-payment', [AppointmentController::class, 'getPendingPaymentAppointments']);
        });

        Route::prefix('points')->group(function () {
            // Get points transactions history
            Route::get('/', [PointsController::class, 'index']);
            // Convert points to money
            Route::post('/convert', [PointsController::class, 'convertPointsToMoney']);
        });
        // End the Provider Display in user app

    });
});



// provider

Route::group(['prefix' => 'v1/provider'], function () {


    Route::post('/check-phone', [ForgotPasswordProviderController::class, 'checkPhone']);

    // Update password
    Route::post('/update-password', [ForgotPasswordProviderController::class, 'updatePassword']);

    // Auth Route
    Route::group(['middleware' => ['auth:provider-api','check.provider.activation']], function () {

        Route::get('/active', [AuthProviderController::class, 'active']);
        Route::post('/updateStatus/{id}', [AuthProviderController::class, 'updateStatusOnOff']);
        Route::post('/withdrawal/request',  [WithdrawalRequestProviderController::class, 'requestWithdrawal']);


        // image for chat
        Route::get('/uploadPhotoVoice', [UploadPhotoVoiceProviderController::class, 'index']);
        Route::post('/uploadPhotoVoice', [UploadPhotoVoiceProviderController::class, 'store']);
        Route::post('/update_profile', [AuthProviderController::class, 'updateProviderProfile']);
        Route::post('/delete_account', [AuthProviderController::class, 'deleteAccount']);
        Route::get('/providerProfile', [AuthProviderController::class, 'getProviderProfile']);
        Route::post('/complete-profile', [AuthProviderController::class, 'completeProviderProfile']);
        Route::post('/types/{providerTypeId}', [AuthProviderController::class, 'updateProviderType']);

        //Notification
        Route::get('/notifications', [AuthProviderController::class, 'notifications']);
        Route::post('/notifications', [AuthProviderController::class, 'sendMessageFromProvider']);

        Route::get('/ratings', [RatingProviderController::class, 'index']);
        Route::get('/wallet/transactions', [WalletProviderController::class, 'getTransactions']);


        // Additional utility routes
        Route::delete('/images', [AuthProviderController::class, 'deleteProviderImages']);
        Route::delete('/gallery', [AuthProviderController::class, 'deleteProviderGalleries']);
        Route::get('/pending-payment-confirmation', [AppointmentProviderController::class, 'getPendingPaymentConfirmations']);


        Route::prefix('appointments')->group(function () {
            // Get all appointments with filtering
            Route::get('/', [AppointmentProviderController::class, 'getProviderAppointments']);
            // Get appointment details
            Route::get('/{appointmentId}', [AppointmentProviderController::class, 'getAppointmentDetails']);
            // Update appointment status
            Route::post('/{appointmentId}/status', [AppointmentProviderController::class, 'updateAppointmentStatus']);
            // New payment confirmation route
            Route::post('/{appointmentId}/confirm-payment', [AppointmentProviderController::class, 'confirmPayment']);

            //Report
            Route::get('/provider/payment-report', [AppointmentProviderController::class, 'paymentReport']);
        });


        Route::prefix('discounts')->group(function () {
            // Get discounts for a specific provider type
            Route::get('/provider-type/{providerTypeId}', [DiscountController::class, 'getByProviderType']);

            // Create a new discount
            Route::post('/', [DiscountController::class, 'store']);

            // Update an existing discount
            Route::put('/{discountId}', [DiscountController::class, 'update']);

            // Delete a discount
            Route::delete('/{discountId}', [DiscountController::class, 'destroy']);
        });
    });
});
