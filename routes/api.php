<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request){
        $user = User::where('email', $request->input('email'))->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Login successful', 'user' => $user,'token'  => $user->createToken('api')->plainTextToken], 200);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
}});