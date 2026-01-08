<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::put('/guests/{guest}', [GuestController::class, 'update']);
    Route::patch('/guests/{guest}', [GuestController::class, 'update']);
});
