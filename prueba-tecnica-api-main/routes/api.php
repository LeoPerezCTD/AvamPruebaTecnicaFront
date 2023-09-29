<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Login\RegisterController;
use App\Http\Controllers\Home\HomeController;

use App\Http\Controllers\util\SupportMessageController;
use App\Http\Controllers\util\BoldSESController;
use App\Http\Controllers\util\BoldUUID;
use App\Http\Controllers\util\SmsController;
use App\Http\Controllers\util\UploadImagesController;


use App\Http\Controllers\GeneralController;
use App\Http\Controllers\Menu\MenuController;

use App\Http\Controllers\Configuration\PeopleController;
use App\Http\Controllers\Products\ProductsController;
use App\Http\Controllers\Products\QuoteController;

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

Route::post('/upload-images', [UploadImagesController::class, 'chargeImages']);
Route::get('/list-images', [UploadImagesController::class, 'listObjects']);
Route::get('/uuid', [BoldUUID::class, 'generateUUID']);
Route::get('/message', [SmsController::class, 'index']);
Route::get('/email', [BoldSESController::class,'sendEmailGet']);

Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [LoginController::class, 'authentication']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/register', [ForgotPasswordController::class, 'forgotPassword']);

    // Route::post('/login', [LoginController::class, 'sendOTP']);
    // Route::post('/verifylogin', [LoginController::class, 'authenticationOTP']);
    // Route::post('/signup', [RegisterController::class, 'register']);
    // Route::post('/forgot-number', [RegisterController::class, 'forgotNumber']);
    // Route::post('/configuration/policy-acceptance', [ PoliciesController::class,'policyAcceptance' ]);
    
    // Route::apiResource('/fifo-history', FifoHistoryController::class);
    // Route::post('/login', [LoginController::class, 'authentication']);
    //Route::post('/register', [ForgotPasswordController::class, 'forgotPassword']);        // forma de escribir ruta laravel 9
    // Route::post('/forgot-password','Login\ForgotPasswordController@forgotPassword');    forma de escribir ruta laravel 8
    
    // Inventory
    // Route::apiResource('inventory/fifo_history',FifoHistoryController::class);
});

Route::group(['middleware' => ['cors', 'authentication']], function () {
    // home
    Route::apiResource('/home', HomeController::class);
    Route::post('/support-message', [SupportMessageController::class, 'sendMessage']);
    Route::post('/menu', [MenuController::class, 'getMenu']);
    Route::apiResource('/general', GeneralController::class);

    // Products
    Route::apiResource('/products',ProductsController::class);
    // Quotes
    Route::apiResource('/quote',QuoteController::class);

    // Configuration
    Route::apiResource('/configuration/policies', PoliciesController::class);
    Route::apiResource('/configuration/users', Configuration\UsersController::class);
    Route::apiResource('/configuration/profile', PeopleController::class);

});
