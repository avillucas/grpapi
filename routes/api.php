<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// User CRUD routes (protected by authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});