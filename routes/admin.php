<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FinesDiscountsController;
use App\Http\Controllers\Admin\NoteVoucherController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PointsController;
use App\Http\Controllers\Reports\PointsReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ProviderDeleteRequestController;
use App\Http\Controllers\Admin\ProviderDetailsController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\VipSubscriptionController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Controllers\Reports\InventoryReportController;
use App\Http\Controllers\Reports\OrderReportController;
use App\Http\Controllers\Reports\PaymentReportController;
use App\Http\Controllers\Reports\ProviderReportController;
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

define('PAGINATION_COUNT', 11);
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {




    Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('logout', [LoginController::class, 'logout'])->name('admin.logout');


        // other route


        /*         start  update login admin                 */
        Route::get('/admin/edit/{id}', [LoginController::class, 'editlogin'])->name('admin.login.edit');
        Route::post('/admin/update/{id}', [LoginController::class, 'updatelogin'])->name('admin.login.update');
        /*         end  update login admin                */

        /// Role and permission
        Route::resource('employee', 'App\Http\Controllers\Admin\EmployeeController', ['as' => 'admin']);
        Route::get('role', 'App\Http\Controllers\Admin\RoleController@index')->name('admin.role.index');
        Route::get('role/create', 'App\Http\Controllers\Admin\RoleController@create')->name('admin.role.create');
        Route::get('role/{id}/edit', 'App\Http\Controllers\Admin\RoleController@edit')->name('admin.role.edit');
        Route::patch('role/{id}', 'App\Http\Controllers\Admin\RoleController@update')->name('admin.role.update');
        Route::post('role', 'App\Http\Controllers\Admin\RoleController@store')->name('admin.role.store');
        Route::post('admin/role/delete', 'App\Http\Controllers\Admin\RoleController@delete')->name('admin.role.delete');

        Route::get('/permissions/{guard_name}', function ($guard_name) {
            return response()->json(Permission::where('guard_name', $guard_name)->get());
        });


        Route::post('providers/{id}/cancel-request', [ProviderController::class, 'cancelProviderRequest'])
            ->name('providers.cancel-request');
    
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');
        
        // Notification
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');



        Route::prefix('pages')->group(function () {
            Route::get('/', [PageController::class, 'index'])->name('pages.index');
            Route::get('/create', [PageController::class, 'create'])->name('pages.create');
            Route::post('/store', [PageController::class, 'store'])->name('pages.store');
            Route::get('/edit/{id}', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('/update/{id}', [PageController::class, 'update'])->name('pages.update');
            Route::delete('/delete/{id}', [PageController::class, 'destroy'])->name('pages.destroy');
        });

        Route::prefix('provider-details')->name('admin.providerDetails.')->group(function () {
            // Image management routes - MUST COME FIRST (most specific)
            Route::delete('images/{imageId}', [ProviderDetailsController::class, 'deleteImage'])->name('deleteImage');
            Route::delete('galleries/{galleryId}', [ProviderDetailsController::class, 'deleteGallery'])->name('deleteGallery');

            // Provider Services Management Routes - SECOND (also specific)
            Route::get('{providerId}/types/{providerTypeId}/services', [ProviderDetailsController::class, 'manageServices'])->name('manageServices');
            Route::post('{providerId}/types/{providerTypeId}/services', [ProviderDetailsController::class, 'storeService'])->name('storeService');
            Route::put('{providerId}/types/{providerTypeId}/services/{serviceId}', [ProviderDetailsController::class, 'updateService'])->name('updateService');
            Route::delete('{providerId}/types/{providerTypeId}/services/{serviceId}', [ProviderDetailsController::class, 'destroyService'])->name('destroyService');

            // Availability routes - THIRD
            Route::get('{providerId}/{serviceTypeId}/availabilities', [ProviderDetailsController::class, 'availabilities'])->name('availabilities');
            Route::post('{providerId}/{serviceTypeId}/availabilities', [ProviderDetailsController::class, 'storeAvailability'])->name('availabilities.store');
            Route::delete('{providerId}/{serviceTypeId}/availabilities/{availabilityId}', [ProviderDetailsController::class, 'destroyAvailability'])->name('availabilities.destroy');

            // Unavailability routes - FOURTH
            Route::get('{providerId}/{serviceTypeId}/unavailabilities', [ProviderDetailsController::class, 'unavailabilities'])->name('unavailabilities');
            Route::post('{providerId}/{serviceTypeId}/unavailabilities', [ProviderDetailsController::class, 'storeUnavailability'])->name('unavailabilities.store');
            Route::delete('{providerId}/{serviceTypeId}/unavailabilities/{availabilityId}', [ProviderDetailsController::class, 'destroyUnavailability'])->name('unavailabilities.destroy');

            // Main CRUD routes - LAST (most general)
            Route::get('{providerId}', [ProviderDetailsController::class, 'index'])->name('index');
            Route::get('{providerId}/create', [ProviderDetailsController::class, 'create'])->name('create');
            Route::post('{providerId}', [ProviderDetailsController::class, 'store'])->name('store');
            Route::get('{providerId}/{serviceTypeId}/edit', [ProviderDetailsController::class, 'edit'])->name('edit');
            Route::put('{providerId}/{serviceTypeId}', [ProviderDetailsController::class, 'update'])->name('update');
            Route::delete('{providerId}/{serviceTypeId}', [ProviderDetailsController::class, 'destroy'])->name('destroy');
        });


        // Report
        Route::get('/reports/points', [PointsReportController::class, 'index'])->name('reports.points');

        Route::get('/admin/payment-report', [PaymentReportController::class, 'paymentReport'])->name('admin.payment.report');
        Route::prefix('providers/report')->group(function () {
            Route::get('/', [ProviderReportController::class, 'index'])->name('admin.providers.report.index');
            Route::get('/{provider}', [ProviderReportController::class, 'show'])->name('admin.providers.report.show');
        });
        
        Route::prefix('reports')->as('reports.')->group(function () {
            // Order Reports
            Route::prefix('orders')->as('orders.')->group(function () {
                Route::get('/', [OrderReportController::class, 'index'])->name('index');
                Route::get('/generate', [OrderReportController::class, 'generate'])->name('generate');
                Route::get('/export', [OrderReportController::class, 'export'])->name('export');
            });
            
            // Inventory Reports  
            Route::prefix('inventory')->as('inventory.')->group(function () {
                Route::get('/', [InventoryReportController::class, 'index'])->name('index');
                Route::get('/generate', [InventoryReportController::class, 'generate'])->name('generate');
                Route::get('/export', [InventoryReportController::class, 'export'])->name('export');
            });
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
        Route::resource('note-vouchers', NoteVoucherController::class);

        Route::get('wallet_transactions/filter', [WalletTransactionController::class, 'filter'])->name('wallet_transactions.filter');

        Route::resource('wallet_transactions', WalletTransactionController::class)->except(['edit', 'update', 'destroy']);

        Route::resource('admin/vip-subscriptions', VipSubscriptionController::class, ['as' => 'admin']);
        Route::post('vip-subscriptions/update-expired', [VipSubscriptionController::class, 'updateExpiredSubscriptions'])
            ->name('admin.vip-subscriptions.update-expired');

        Route::resource('fines-discounts', FinesDiscountsController::class)->except(['edit', 'update']);
        Route::post('fines-discounts/{fineDiscount}/apply', [FinesDiscountsController::class, 'apply'])->name('fines-discounts.apply');
        Route::post('fines-discounts/{fineDiscount}/reverse', [FinesDiscountsController::class, 'reverse'])->name('fines-discounts.reverse');
        Route::post('fines-discounts/bulk-apply', [FinesDiscountsController::class, 'bulkApply'])->name('fines-discounts.bulk-apply');
        Route::post('fines-discounts/process-all-pending', [FinesDiscountsController::class, 'processAllPending'])->name('fines-discounts.process-all-pending');

        // Settings routes
        Route::get('fines-discounts-settings', [FinesDiscountsController::class, 'settings'])->name('fines-discounts.settings');
        Route::post('fines-discounts-settings', [FinesDiscountsController::class, 'updateSettings'])->name('fines-discounts.update-settings');

        // History routes
        Route::get('users/{user}/fine-history', [FinesDiscountsController::class, 'userHistory'])->name('users.fine-history');
        Route::get('providers/{provider}/fine-history', [FinesDiscountsController::class, 'providerHistory'])->name('providers.fine-history');


        Route::get('/withdrawals', [WithdrawalRequestController::class, 'index'])->name('withdrawals.index');
        Route::get('/history/{id}', [WithdrawalRequestController::class, 'history'])->name('admin.withdrawals.history');
        Route::post('/approve/{id}', [WithdrawalRequestController::class, 'approve'])->name('admin.withdrawals.approve');
        Route::post('/reject/{id}', [WithdrawalRequestController::class, 'reject'])->name('admin.withdrawals.reject');
        Route::prefix('ratings')->group(function () {
            Route::get('/', [RatingController::class, 'index'])->name('admin.ratings.index');
            Route::delete('/{rating}', [RatingController::class, 'destroy'])->name('admin.ratings.destroy');
        });

        // functionloty routes
        Route::delete('/products/images/{imageId}', [ProductController::class, 'deleteImage'])->name('products.deleteImage');
    
        Route::get('/usedCoupons', [CouponController::class, 'displayCouponUsed'])->name('usedCoupons.index');
    
        Route::name('admin.')->group(function () {
    
                // Provider Delete Requests Management
                Route::get('provider-delete-requests', [ProviderDeleteRequestController::class, 'index'])
                    ->name('provider-delete-requests.index');
                
                Route::get('provider-delete-requests/{id}', [ProviderDeleteRequestController::class, 'show'])
                    ->name('provider-delete-requests.show');
                
                Route::post('provider-delete-requests/{id}/approve', [ProviderDeleteRequestController::class, 'approve'])
                    ->name('provider-delete-requests.approve');
                
                Route::post('provider-delete-requests/{id}/reject', [ProviderDeleteRequestController::class, 'reject'])
                    ->name('provider-delete-requests.reject');
                
                Route::get('provider-delete-requests/statistics', [ProviderDeleteRequestController::class, 'getStatistics'])
                    ->name('provider-delete-requests.statistics');
        });

        Route::prefix('provider/{providerId}/types/{providerTypeId}/discounts')->name('discounts.')->group(function () {
            Route::get('/', [DiscountController::class, 'index'])->name('index');
            Route::get('/create', [DiscountController::class, 'create'])->name('create');
            Route::post('/', [DiscountController::class, 'store'])->name('store');
            Route::get('/{discountId}/edit', [DiscountController::class, 'edit'])->name('edit');
            Route::put('/{discountId}', [DiscountController::class, 'update'])->name('update');
            Route::delete('/{discountId}', [DiscountController::class, 'destroy'])->name('destroy');
            Route::patch('/{discountId}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('toggleStatus');
        });

            Route::post('/wallet/update', [UserController::class, 'updateWallet'])->name('wallet.update');
            Route::post('/provider/wallet/update', [ProviderController::class, 'updateProviderWallet'])->name('provider.wallet.update');

             Route::get('/user/{user}/points/history', [PointsController::class, 'history'])->name('users.points.history');
             Route::post('/points/update', [PointsController::class, 'update'])->name('points.update');
    });
});



Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => 'guest:admin'], function () {
    Route::get('login', [LoginController::class, 'show_login_view'])->name('admin.showlogin');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login');
});
