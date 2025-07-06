<?php

use App\Models\Pet;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\AdoptionRequest;
use App\Models\AdoptionRequestStatus;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('can list all adoption requests', function () {
    $pets = Pet::factory()->count(2)->create();
    $users = User::factory()->count(2)->create();
    
    AdoptionRequest::factory()->count(3)->create();

    $response = $this->get('/api/adoption-requests');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'data' => [
            '*' => [
                'id',
                'address',
                'phone',
                'application',
                'status',
                'pet' => [
                    'id',
                    'name',
                    'status',
                    'photo_url'
                ],
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'created_at',
                'updated_at'
            ]
        ]
    ]);
    
    $this->assertCount(3, $response->json('data'));
});

test('can show a specific adoption request', function () {
    $pet = Pet::factory()->create(['name' => 'Buddy']);
    $user = User::factory()->create(['name' => 'John Doe']);
    
    $adoptionRequest = AdoptionRequest::factory()->create([
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '123 Main St',
        'phone' => '555-1234',
        'application' => 'I love dogs',
        'status' => AdoptionRequestStatus::PENDING->value
    ]);

    $response = $this->get("/api/adoption-requests/{$adoptionRequest->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Adoption request retrieved successfully',
        'data' => [
            'id' => $adoptionRequest->id,
            'address' => '123 Main St',
            'phone' => '555-1234',
            'application' => 'I love dogs',
            'status' => 'pending',
            'pet' => [
                'name' => 'Buddy'
            ],
            'user' => [
                'name' => 'John Doe'
            ]
        ]
    ]);
});

test('returns 404 when showing non-existent adoption request', function () {
    $response = $this->get('/api/adoption-requests/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Adoption request not found'
    ]);
});

test('can create an adoption request', function () {
    $pet = Pet::factory()->create();
    $user = User::factory()->create();

    $requestData = [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '456 Oak St',
        'phone' => '555-5678',
        'application' => 'I have experience with pets and would love to adopt this one.',
        'status' => 'pending'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Adoption request created successfully',
        'data' => [
            'address' => '456 Oak St',
            'phone' => '555-5678',
            'application' => 'I have experience with pets and would love to adopt this one.',
            'status' => 'pending'
        ]
    ]);

    $this->assertDatabaseHas('adoption_requests', [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '456 Oak St',
        'phone' => '555-5678',
        'status' => 'pending'
    ]);
});

test('creates adoption request with default pending status', function () {
    $pet = Pet::factory()->create();
    $user = User::factory()->create();

    $requestData = [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '789 Pine St',
        'phone' => '555-9999',
        'application' => 'Default status test'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'status' => 'pending'
        ]
    ]);
});

test('prevents duplicate pending adoption requests for same pet and user', function () {
    $pet = Pet::factory()->create();
    $user = User::factory()->create();

    // Create first adoption request
    AdoptionRequest::factory()->create([
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'status' => AdoptionRequestStatus::PENDING->value
    ]);

    // Try to create another for same pet and user
    $requestData = [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '123 Test St',
        'phone' => '555-0000',
        'application' => 'Duplicate request'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(409);
    $response->assertJson([
        'message' => 'User already has a pending adoption request for this pet'
    ]);
});

test('allows new request if previous one was approved or rejected', function () {
    $pet = Pet::factory()->create();
    $user = User::factory()->create();

    // Create approved adoption request
    AdoptionRequest::factory()->create([
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'status' => AdoptionRequestStatus::APPROVED->value
    ]);

    // Should allow new request
    $requestData = [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '123 Test St',
        'phone' => '555-0000',
        'application' => 'New request after approval'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(201);
});

test('validation fails with missing required fields', function () {
    $requestData = [
        'address' => '123 Test St'
        // Missing pet_id, user_id, phone, application
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pet_id', 'user_id', 'phone', 'application']);
});

test('validation fails with non-existent pet or user', function () {
    $requestData = [
        'pet_id' => 999, // Non-existent pet
        'user_id' => 999, // Non-existent user
        'address' => '123 Test St',
        'phone' => '555-0000',
        'application' => 'Test application'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pet_id', 'user_id']);
});

test('validation fails with invalid status', function () {
    $pet = Pet::factory()->create();
    $user = User::factory()->create();

    $requestData = [
        'pet_id' => $pet->id,
        'user_id' => $user->id,
        'address' => '123 Test St',
        'phone' => '555-0000',
        'application' => 'Test application',
        'status' => 'invalid_status'
    ];

    $response = $this->post('/api/adoption-requests', $requestData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['status']);
});

test('can update an adoption request', function () {
    $adoptionRequest = AdoptionRequest::factory()->create([
        'address' => 'Old Address',
        'phone' => '555-0000',
        'status' => AdoptionRequestStatus::PENDING->value
    ]);

    $updateData = [
        'address' => 'New Address',
        'phone' => '555-1111',
        'status' => 'approved'
    ];

    $response = $this->put("/api/adoption-requests/{$adoptionRequest->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Adoption request updated successfully',
        'data' => [
            'id' => $adoptionRequest->id,
            'address' => 'New Address',
            'phone' => '555-1111',
            'status' => 'approved'
        ]
    ]);

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $adoptionRequest->id,
        'address' => 'New Address',
        'phone' => '555-1111',
        'status' => 'approved'
    ]);
});

test('can partially update an adoption request', function () {
    $adoptionRequest = AdoptionRequest::factory()->create([
        'address' => 'Original Address',
        'phone' => '555-0000',
        'application' => 'Original Application'
    ]);

    $updateData = [
        'address' => 'Updated Address Only'
    ];

    $response = $this->put("/api/adoption-requests/{$adoptionRequest->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'address' => 'Updated Address Only',
            'phone' => '555-0000', // Should remain unchanged
            'application' => 'Original Application' // Should remain unchanged
        ]
    ]);
});

test('returns 404 when updating non-existent adoption request', function () {
    $updateData = [
        'address' => 'Updated Address'
    ];

    $response = $this->put('/api/adoption-requests/999', $updateData);

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Adoption request not found'
    ]);
});

test('can delete an adoption request', function () {
    $adoptionRequest = AdoptionRequest::factory()->create();

    $response = $this->delete("/api/adoption-requests/{$adoptionRequest->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Adoption request deleted successfully'
    ]);

    $this->assertDatabaseMissing('adoption_requests', [
        'id' => $adoptionRequest->id
    ]);
});

test('returns 404 when deleting non-existent adoption request', function () {
    $response = $this->delete('/api/adoption-requests/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Adoption request not found'
    ]);
});

test('can approve an adoption request', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $response = $this->patch("/api/adoption-requests/{$adoptionRequest->id}/approve");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Adoption request approved successfully',
        'data' => [
            'id' => $adoptionRequest->id,
            'status' => 'approved'
        ]
    ]);

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $adoptionRequest->id,
        'status' => 'approved'
    ]);
});

test('can reject an adoption request', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $response = $this->patch("/api/adoption-requests/{$adoptionRequest->id}/reject");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Adoption request rejected successfully',
        'data' => [
            'id' => $adoptionRequest->id,
            'status' => 'rejected'
        ]
    ]);

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $adoptionRequest->id,
        'status' => 'rejected'
    ]);
});

test('returns 404 when approving non-existent adoption request', function () {
    $response = $this->patch('/api/adoption-requests/999/approve');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Adoption request not found'
    ]);
});

test('returns 404 when rejecting non-existent adoption request', function () {
    $response = $this->patch('/api/adoption-requests/999/reject');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Adoption request not found'
    ]);
});

test('requires authentication for all adoption request operations', function () {
    $adoptionRequest = AdoptionRequest::factory()->create();

    $response = $this->withoutMiddleware(['auth:sanctum'])->get('/api/adoption-requests');
    $response->assertStatus(401);
});
