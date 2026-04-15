<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;

Route::get('/', [App\Http\Controllers\ParkingController::class, 'index'])->name('parking.index');
require __DIR__.'/auth.php';

// User profile & reservations (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('user.dashboard');
});

// Admin Dashboard routes (protected - uncomment auth middleware for production)
// Route::middleware(['auth:sanctum'])->prefix('admin')->name('admin.')->group(function () {
Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'statsJson'])->name('stats');
    Route::get('/charts', [DashboardController::class, 'chartsJson'])->name('charts');

    // Parking Lots CRUD
    Route::get('/parking-lots', [\App\Http\Controllers\Admin\ParkingLotController::class, 'index'])->name('parking-lots.index');
    Route::get('/parking-lots/{parkingLot}', [\App\Http\Controllers\Admin\ParkingLotController::class, 'show'])->name('parking-lots.show');
    Route::post('/parking-lots', [\App\Http\Controllers\Admin\ParkingLotController::class, 'store'])->name('parking-lots.store');
    Route::put('/parking-lots/{parkingLot}', [\App\Http\Controllers\Admin\ParkingLotController::class, 'update'])->name('parking-lots.update');
    Route::post('/parking-lots/{parkingLot}/toggle', [\App\Http\Controllers\Admin\ParkingLotController::class, 'toggleStatus'])->name('parking-lots.toggle');

    // Active Bookings
    Route::get('/bookings/active', [\App\Http\Controllers\Admin\BookingController::class, 'activeIndex'])->name('bookings.active');
    Route::post('/bookings/{booking}/complete', [\App\Http\Controllers\Admin\BookingController::class, 'completeBooking'])->name('bookings.complete');
});
// });

// Operator Dashboard
Route::prefix('operator')->middleware('operator')->name('operator.')->group(function () {
    Route::get('/dashboard',                  [\App\Http\Controllers\Operator\OperatorController::class, 'dashboard'])->name('dashboard');
    Route::post('/check-in',                  [\App\Http\Controllers\Operator\OperatorController::class, 'checkIn'])->name('checkIn');
    Route::post('/{booking}/activate',        [\App\Http\Controllers\Operator\OperatorController::class, 'activateReservation'])->name('activate');
    Route::get('/{booking}/checkout-preview', [\App\Http\Controllers\Operator\OperatorController::class, 'checkoutPreview'])->name('checkoutPreview');
    Route::post('/{booking}/payment',         [\App\Http\Controllers\Operator\OperatorController::class, 'processPayment'])->name('payment');
    Route::post('/{booking}/checkout',        [\App\Http\Controllers\Operator\OperatorController::class, 'checkOut'])->name('checkOut');
});

// });

/*
To enable auth:
1. php artisan make:middleware AdminAuth or use existing auth:sanctum
2. Uncomment Route::middleware(['auth:sanctum'])
3. Add super admin user/guard.
*/

