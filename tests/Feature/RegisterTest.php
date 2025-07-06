<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
test('user can register with valid credentials', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'message',
        'token',
        'user' => [
            'id',
            'name',
            'email'
        ]
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe'
    ]);
});

test('registration fails with missing name', function () {
    $response = $this->post('/api/register', [
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('registration fails with missing email', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('registration fails with invalid email format', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('registration fails with duplicate email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('registration fails with missing password', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('registration fails with short password', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '123',
        'password_confirmation' => '123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('registration fails with password confirmation mismatch', function () {
    $response = $this->post('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});
