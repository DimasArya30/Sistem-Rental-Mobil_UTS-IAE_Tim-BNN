<?php

use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rental Service API Routes
| Provider & Consumer Endpoints
|--------------------------------------------------------------------------
*/

Route::prefix('rentals')->group(function () {

    // PROVIDER endpoints
    Route::get('/', [RentalController::class, 'index']);
    Route::get('/active', [RentalController::class, 'getActiveRentals']);
    Route::get('/customer/{customerId}', [RentalController::class, 'getByCustomer']);
    Route::get('/{id}', [RentalController::class, 'show']);

    // CONSUMER endpoints - memanggil CustomerService & CarService
    Route::post('/', [RentalController::class, 'store']);
    Route::put('/{id}/return', [RentalController::class, 'returnCar']);
});
