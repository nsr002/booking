<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LiffAuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ScheduleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/liff/auth', [LiffAuthController::class, 'authenticate']);

// Protected routes (require LIFF token)
Route::middleware('verify.liff.token')->group(function () {
    // Auth
    Route::get('/me', [LiffAuthController::class, 'me']);
    
    // Packages
    Route::get('/packages', [PackageController::class, 'index']);
    Route::post('/packages/purchase', [PackageController::class, 'purchase']);
    Route::get('/user/credits', [PackageController::class, 'credits']);
    
    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    
    // Bookings
    Route::get('/bookings/my', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
});
