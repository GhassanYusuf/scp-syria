<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('parking-lots', \App\Http\Controllers\Api\ParkingLotController::class);
    Route::get('parking-lots/search', [\App\Http\Controllers\Api\ParkingLotController::class, 'search']);
    Route::get('parking-lots/{parkingLot}/status', [\App\Http\Controllers\Api\ParkingLotController::class, 'status']);
    Route::post('car-registries', [\App\Http\Controllers\Api\CarRegistryController::class, 'checkIn']);
    Route::put('car-registries/{carRegistry}/exit', [\App\Http\Controllers\Api\CarRegistryController::class, 'checkOut']);
    Route::post('bookings', \App\Http\Controllers\Api\BookingController::class);
    Route::get('bookings', [\App\Http\Controllers\Api\BookingController::class, 'index']);
});
?>
