<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list all users', function () {
    // Crear algunos usuarios adicionales
    User::factory()->count(3)->create();

    $response = $this->get('/api/users');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'data' => [
            '*' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]
    ]);
    
    // Verificar que hay al menos 4 usuarios (1 del beforeEach + 3 creados)
    $this->assertGreaterThanOrEqual(4, count($response->json('data')));
});

test('can show a specific user', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'User retrieved successfully',
        'data' => [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]
    ]);
});

test('returns 404 when showing non-existent user', function () {
    $response = $this->get('/api/users/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'User not found'
    ]);
});

test('can create a new user', function () {
    $userData = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123'
    ];

    $response = $this->post('/api/users', $userData);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'User created successfully',
        'data' => [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com'
    ]);

    // Verificar que la contraseña no se devuelve en la respuesta
    $response->assertJsonMissing(['password']);
});

test('validation fails when creating user with invalid data', function () {
    $userData = [
        'name' => '', // Nombre vacío
        'email' => 'invalid-email', // Email inválido
        'password' => '123' // Contraseña muy corta
    ];

    $response = $this->post('/api/users', $userData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('validation fails when creating user with duplicate email', function () {
    $existingUser = User::factory()->create(['email' => 'duplicate@example.com']);

    $userData = [
        'name' => 'Jane Doe',
        'email' => 'duplicate@example.com',
        'password' => 'password123'
    ];

    $response = $this->post('/api/users', $userData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('can update a user', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com'
    ];

    $response = $this->put("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'User updated successfully',
        'data' => [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com'
    ]);
});

test('can partially update a user', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com'
    ]);

    $updateData = [
        'name' => 'Updated Name Only'
    ];

    $response = $this->put("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'User updated successfully',
        'data' => [
            'id' => $user->id,
            'name' => 'Updated Name Only',
            'email' => 'original@example.com' // Email no debería cambiar
        ]
    ]);
});

test('can update user password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $updateData = [
        'password' => 'newpassword123'
    ];

    $response = $this->put("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200);
    
    // Verificar que la contraseña cambió
    $user->refresh();
    $this->assertNotEquals($originalPassword, $user->password);
});

test('returns 404 when updating non-existent user', function () {
    $updateData = [
        'name' => 'Updated Name'
    ];

    $response = $this->put('/api/users/999', $updateData);

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'User not found'
    ]);
});

test('validation fails when updating with invalid email', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create(['email' => 'taken@example.com']);

    $updateData = [
        'email' => 'taken@example.com' // Email ya existe
    ];

    $response = $this->put("/api/users/{$user->id}", $updateData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('can delete a user', function () {
    $user = User::factory()->create();

    $response = $this->delete("/api/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'User deleted successfully'
    ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id
    ]);
});

test('returns 404 when deleting non-existent user', function () {
    $response = $this->delete('/api/users/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'User not found'
    ]);
});

test('requires authentication for user operations', function () {
    $user = User::factory()->create();

    // Crear una nueva instancia de test sin autenticación
    $unauthenticatedTest = $this->withoutMiddleware(['auth:sanctum']);
    
    $response = $this->get('/api/users');
    $response->assertStatus(401);
});
