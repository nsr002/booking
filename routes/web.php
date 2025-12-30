<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingManageController;
use App\Http\Controllers\Admin\ScheduleManageController;
use App\Http\Controllers\Admin\PackageManageController;
use App\Http\Controllers\Admin\UserManageController;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Bookings
    Route::get('/bookings', [BookingManageController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingManageController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/approve', [BookingManageController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{id}/reject', [BookingManageController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{id}/complete', [BookingManageController::class, 'complete'])->name('bookings.complete');
    
    // Schedules
    Route::resource('schedules', ScheduleManageController::class);
    
    // Packages
    Route::resource('packages', PackageManageController::class);
    
    // Users
    Route::get('/users', [UserManageController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserManageController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserManageController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserManageController::class, 'update'])->name('users.update');
    Route::post('/users/{userId}/packages/{packageId}/approve', [UserManageController::class, 'approvePackage'])->name('users.packages.approve');
    Route::post('/users/{userId}/packages/{packageId}/reject', [UserManageController::class, 'rejectPackage'])->name('users.packages.reject');
});

// LIFF Pages
Route::prefix('liff')->group(function () {
    Route::get('/booking', function () {
        return view('liff.booking');
    })->name('liff.booking');
    
    Route::get('/history', function () {
        return view('liff.history');
    })->name('liff.history');
    
    Route::get('/packages', function () {
        return view('liff.packages');
    })->name('liff.packages');
});
