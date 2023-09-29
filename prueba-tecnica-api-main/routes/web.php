<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\TaxesController;

Route::group(['middleware' => ['cors', 'authentication']], function () {

    //taxes
    Route::apiResource('web/configuration/taxes', TaxesController::class);
    
});