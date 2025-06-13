<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ProviderDetailsController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Permission\Models\Permission;
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

define('PAGINATION_COUNT',11);
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {




 Route::group(['prefix'=>'admin','middleware'=>'auth:admin'],function(){
 Route::get('/',[DashboardController::class,'index'])->name('admin.dashboard');
 Route::get('logout',[LoginController::class,'logout'])->name('admin.logout');


 // other route


/*         start  update login admin                 */
Route::get('/admin/edit/{id}',[LoginController::class,'editlogin'])->name('admin.login.edit');
Route::post('/admin/update/{id}',[LoginController::class,'updatelogin'])->name('admin.login.update');
/*         end  update login admin                */

/// Role and permission
Route::resource('employee', 'App\Http\Controllers\Admin\EmployeeController',[ 'as' => 'admin']);
Route::get('role', 'App\Http\Controllers\Admin\RoleController@index')->name('admin.role.index');
Route::get('role/create', 'App\Http\Controllers\Admin\RoleController@create')->name('admin.role.create');
Route::get('role/{id}/edit', 'App\Http\Controllers\Admin\RoleController@edit')->name('admin.role.edit');
Route::patch('role/{id}', 'App\Http\Controllers\Admin\RoleController@update')->name('admin.role.update');
Route::post('role', 'App\Http\Controllers\Admin\RoleController@store')->name('admin.role.store');
Route::post('admin/role/delete', 'App\Http\Controllers\Admin\RoleController@delete')->name('admin.role.delete');

Route::get('/permissions/{guard_name}', function($guard_name){
    return response()->json(Permission::where('guard_name',$guard_name)->get());
});



// Notification
Route::get('/notifications/create',[NotificationController::class,'create'])->name('notifications.create');
Route::post('/notifications/send',[NotificationController::class,'send'])->name('notifications.send');



Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('pages.index');
    Route::get('/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/store', [PageController::class, 'store'])->name('pages.store');
    Route::get('/edit/{id}', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/update/{id}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/delete/{id}', [PageController::class, 'destroy'])->name('pages.destroy');
});

Route::prefix('provider-details')->name('admin.providerDetails.')->group(function () {
    // Main CRUD routes
    Route::get('/{providerId}', [ProviderDetailsController::class, 'index'])->name('index');
    Route::get('/{providerId}/create', [ProviderDetailsController::class, 'create'])->name('create');
    Route::post('/{providerId}', [ProviderDetailsController::class, 'store'])->name('store');
    Route::get('/{providerId}/{serviceTypeId}/edit', [ProviderDetailsController::class, 'edit'])->name('edit');
    Route::put('/{providerId}/{serviceTypeId}', [ProviderDetailsController::class, 'update'])->name('update');
    Route::delete('/{providerId}/{serviceTypeId}', [ProviderDetailsController::class, 'destroy'])->name('destroy');

    // Availability routes
    Route::get('/{providerId}/{serviceTypeId}/availabilities', [ProviderDetailsController::class, 'availabilities'])->name('availabilities');
    Route::post('/{providerId}/{serviceTypeId}/availabilities', [ProviderDetailsController::class, 'storeAvailability'])->name('availabilities.store');
    Route::delete('/{providerId}/{serviceTypeId}/availabilities/{availabilityId}', [ProviderDetailsController::class, 'destroyAvailability'])->name('availabilities.destroy');

    // Unavailability routes
    Route::get('/{providerId}/{serviceTypeId}/unavailabilities', [ProviderDetailsController::class, 'unavailabilities'])->name('unavailabilities');
    Route::post('/{providerId}/{serviceTypeId}/unavailabilities', [ProviderDetailsController::class, 'storeUnavailability'])->name('unavailabilities.store');
    Route::delete('/{providerId}/{serviceTypeId}/unavailabilities/{unavailabilityId}', [ProviderDetailsController::class, 'destroyUnavailability'])->name('unavailabilities.destroy');

    // Image management routes
    Route::delete('/images/{imageId}', [ProviderDetailsController::class, 'deleteImage'])->name('deleteImage');
    Route::delete('/galleries/{galleryId}', [ProviderDetailsController::class, 'deleteGallery'])->name('deleteGallery');
});

// Resource Route
Route::resource('settings', SettingController::class);
Route::resource('users', UserController::class);
Route::resource('providers', ProviderController::class);
Route::resource('services', ServiceController::class);
Route::resource('deliveries', DeliveryController::class);
Route::resource('coupons', CouponController::class);
Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('banners', BannerController::class);
Route::resource('types', TypeController::class);
Route::resource('orders', OrderController::class);
Route::resource('appointments', AppointmentController::class);

Route::resource('wallet_transactions', WalletTransactionController::class)->except(['edit', 'update', 'destroy']);
Route::get('wallet_transactions/filter', [WalletTransactionController::class, 'filter'])->name('wallet_transactions.filter');
Route::get('users/{id}/transactions', [WalletTransactionController::class, 'userTransactions'])->name('wallet_transactions.userTransactions');


Route::get('/withdrawals', [WithdrawalRequestController::class, 'index'])->name('withdrawals.index');
Route::get('/history/{id}', [WithdrawalRequestController::class, 'history'])->name('admin.withdrawals.history');
Route::post('/approve/{id}', [WithdrawalRequestController::class, 'approve'])->name('admin.withdrawals.approve');
Route::post('/reject/{id}', [WithdrawalRequestController::class, 'reject'])->name('admin.withdrawals.reject');


// functionloty routes
Route::delete('/products/images/{imageId}', [ProductController::class, 'deleteImage'])->name('products.deleteImage');
});
});



Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>'guest:admin'],function(){
    Route::get('login',[LoginController::class,'show_login_view'])->name('admin.showlogin');
    Route::post('login',[LoginController::class,'login'])->name('admin.login');

});







