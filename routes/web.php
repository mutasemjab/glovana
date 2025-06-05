<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\TripTypeController;
use App\Http\Controllers\User\HomeController;
use App\Models\Booking;
use App\Models\User;
use Asciisd\Knet\Http\Controllers\ReceiptController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use IZaL\Knet\KnetBilling;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group whichf
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/migrate-refresh', function () {
    // Run the migration command
    Artisan::call('migrate:fresh --seed');

    // Get the output of the command
    $output = Artisan::output();

    // Return a response with the output
    return response()->json(['message' => 'Migration and seeding completed successfully', 'output' => $output]);
});


Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {

});
