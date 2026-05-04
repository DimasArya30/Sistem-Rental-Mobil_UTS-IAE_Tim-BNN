<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Service API Routes
| Provider & Consumer Endpoints
|--------------------------------------------------------------------------
*/

Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::get('/{id}', [CustomerController::class, 'show']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::delete('/{id}', [CustomerController::class, 'destroy']);

    Route::get('/{id}/rentals', [CustomerController::class, 'getRentalHistory']);
});
