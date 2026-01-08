<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Public route for QR code scanning at kiosk
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::put('/guests/{guest}', [GuestController::class, 'update']);
    Route::patch('/guests/{guest}', [GuestController::class, 'update']);
    Route::get('/guests/{guest}/qr-token', [GuestController::class, 'qrToken']);
});
