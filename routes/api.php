<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdoptionOfferController;
use App\Http\Controllers\AdoptionRequestController;

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// User CRUD routes (protected by authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('adoption-requests/mine', [AdoptionRequestController::class, 'myRequests'])->name('adoption-requests.my-requests');
    Route::post('adoption-requests/mine', [AdoptionRequestController::class, 'mine'])->name('adoption-requests.mine');
    Route::post('adoption-requests/myself', [AdoptionRequestController::class, 'myself'])->name('adoption-requests.myself');
});
// User CRUD routes (protected by admin authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('pets', PetController::class);
    Route::apiResource('adoption-requests', AdoptionRequestController::class);
    Route::apiResource('adoption-offers', AdoptionOfferController::class);
    
    Route::post('adoption-requests/{id}/approve', [AdoptionRequestController::class, 'approve'])->name('adoption-requests.approve');
    Route::post('adoption-requests/{id}/reject', [AdoptionRequestController::class, 'reject'])->name('adoption-requests.reject');
});

