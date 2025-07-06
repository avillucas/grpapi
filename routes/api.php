<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdoptionRequestController;

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// User CRUD routes (protected by authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('pets', PetController::class);
    Route::apiResource('adoption-requests', AdoptionRequestController::class);
    
    // Additional routes for adoption request actions
    Route::patch('adoption-requests/{id}/approve', [AdoptionRequestController::class, 'approve'])->name('adoption-requests.approve');
    Route::patch('adoption-requests/{id}/reject', [AdoptionRequestController::class, 'reject'])->name('adoption-requests.reject');
});