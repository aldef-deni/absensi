<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
    Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);
    Route::get('/attendance/history', [AttendanceApiController::class, 'history']);

    Route::get('/leaves', [LeaveApiController::class, 'index']);
    Route::post('/leaves', [LeaveApiController::class, 'store']);
});
