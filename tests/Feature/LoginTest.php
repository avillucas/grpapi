<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
test('the user can login ', function () {
    $user = User::factory()->create([
        'email' => 'email@email.com',
        'password' => Hash::make('password')
    ]);
    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertStatus(200);
});

test('login fails with incorrect email', function () {
    $user = User::factory()->create([
        'email' => 'email@email.com',
        'password' => Hash::make('password')
    ]);
    
    $response = $this->post('/api/login', [
        'email' => 'wrong@email.com',
        'password' => 'password'
    ]);

    $response->assertStatus(422);
});

test('login fails with incorrect password', function () {
    $user = User::factory()->create([
        'email' => 'email@email.com',
        'password' => Hash::make('password')
    ]);
    
    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password'
    ]);

    $response->assertStatus(401);
});

test('login fails with missing email', function () {
    $response = $this->post('/api/login', [
        'password' => 'password'
    ]);

    $response->assertStatus(422);
});

test('login fails with missing password', function () {
    $response = $this->post('/api/login', [
        'email' => 'email@email.com'
    ]);

    $response->assertStatus(422);
});

test('login fails with empty credentials', function () {
    $response = $this->post('/api/login', []);

    $response->assertStatus(422);
});