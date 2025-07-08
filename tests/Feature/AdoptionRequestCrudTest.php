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

test('can get my adoption requests ordered by creation date descending', function () {
    $otherUser = User::factory()->create();
    
    // Create pets
    $pet1 = Pet::factory()->create();
    $pet2 = Pet::factory()->create(); 
    $pet3 = Pet::factory()->create();
    
    // Create adoption requests for the authenticated user
    $myRequest1 = AdoptionRequest::factory()->create([
        'user_id' => $this->user->id,
        'pet_id' => $pet1->id,
        'created_at' => now()->subDays(3)
    ]);
    
    $myRequest2 = AdoptionRequest::factory()->create([
        'user_id' => $this->user->id,
        'pet_id' => $pet2->id,
        'created_at' => now()->subDays(1)
    ]);
    
    $myRequest3 = AdoptionRequest::factory()->create([
        'user_id' => $this->user->id,
        'pet_id' => $pet3->id,
        'created_at' => now()
    ]);
    
    // Create adoption request for another user (should not appear)
    AdoptionRequest::factory()->create([
        'user_id' => $otherUser->id,
        'pet_id' => $pet1->id
    ]);

    $response = $this->get('/api/adoption-requests/mine');

    $response->assertStatus(200)
        ->assertJsonStructure([
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
                        'photo_url',
                        'age',
                        'type',
                        'breed',
                        'size'
                    ],
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

    $data = $response->json('data');
    
    // Should return only 3 requests (not the other user's request)
    expect($data)->toHaveCount(3);
    
    // Should be ordered by creation date descending (newest first)
    expect($data[0]['id'])->toBe($myRequest3->id);
    expect($data[1]['id'])->toBe($myRequest2->id);
    expect($data[2]['id'])->toBe($myRequest1->id);
});

test('returns empty array when user has no adoption requests', function () {
    // Don't create any adoption requests for this user
    
    $response = $this->get('/api/adoption-requests/mine');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'My adoption requests retrieved successfully',
            'data' => []
        ]);
});

test('mine GET endpoint requires authentication', function () {
    $response = $this->withoutMiddleware(['auth:sanctum'])->get('/api/adoption-requests/mine');
    
    $response->assertStatus(401);
});

test('can create adoption request for authenticated user via mine endpoint', function () {
    $pet = Pet::factory()->create();

    $adoptionRequestData = [
        'pet_id' => $pet->id,
        'address' => 'Av. Corrientes 1234, CABA',
        'phone' => '1122334455',
        'application' => 'Me encanta esta mascota y quiero adoptarla porque...'
    ];

    $response = $this->postJson('/api/adoption-requests/mine', $adoptionRequestData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'address',
                'phone',
                'application',
                'status',
                'pet' => [
                    'id',
                    'name',
                    'status',
                    'photo_url',
                    'age',
                    'type',
                    'breed',
                    'size'
                ],
                'created_at',
                'updated_at'
            ]
        ]);

    $this->assertDatabaseHas('adoption_requests', [
        'pet_id' => $pet->id,
        'user_id' => $this->user->id,
        'address' => 'Av. Corrientes 1234, CABA',
        'phone' => '1122334455',
        'application' => 'Me encanta esta mascota y quiero adoptarla porque...',
        'status' => 'pending'
    ]);
});

test('cannot create adoption request via mine endpoint with invalid data', function () {
    $response = $this->postJson('/api/adoption-requests/mine', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pet_id', 'address', 'phone', 'application']);
});

test('cannot create adoption request via mine endpoint for non-existent pet', function () {
    $adoptionRequestData = [
        'pet_id' => 999,
        'address' => 'Av. Corrientes 1234, CABA',
        'phone' => '1122334455',
        'application' => 'Me encanta esta mascota'
    ];

    $response = $this->postJson('/api/adoption-requests/mine', $adoptionRequestData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pet_id']);
});

test('cannot create duplicate pending adoption request via mine endpoint', function () {
    $pet = Pet::factory()->create();
    
    // Create first adoption request
    AdoptionRequest::factory()->create([
        'user_id' => $this->user->id,
        'pet_id' => $pet->id,
        'status' => 'pending'
    ]);

    // Try to create second adoption request for same pet
    $adoptionRequestData = [
        'pet_id' => $pet->id,
        'address' => 'Av. Corrientes 1234, CABA',
        'phone' => '1122334455',
        'application' => 'Me encanta esta mascota'
    ];

    $response = $this->postJson('/api/adoption-requests/mine', $adoptionRequestData);

    $response->assertStatus(409)
        ->assertJson(['message' => 'User already has a pending adoption request for this pet']);
});

test('mine POST endpoint requires authentication', function () {
    $pet = Pet::factory()->create();
    
    $adoptionRequestData = [
        'pet_id' => $pet->id,
        'address' => 'Av. Corrientes 1234, CABA',
        'phone' => '1122334455',
        'application' => 'Me encanta esta mascota'
    ];

    $response = $this->withoutMiddleware(['auth:sanctum'])->postJson('/api/adoption-requests/mine', $adoptionRequestData);
    
    $response->assertStatus(401);
});

test('can reject adoption request with reason', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $rejectData = [
        'reject_reason' => 'No cumple con los requisitos de espacio necesario para esta mascota.'
    ];

    $response = $this->postJson("/api/adoption-requests/{$adoptionRequest->id}/reject", $rejectData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'status',
                'reject_reason'
            ]
        ])
        ->assertJson([
            'data' => [
                'status' => 'rejected',
                'reject_reason' => 'No cumple con los requisitos de espacio necesario para esta mascota.'
            ]
        ]);

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $adoptionRequest->id,
        'status' => 'rejected',
        'reject_reason' => 'No cumple con los requisitos de espacio necesario para esta mascota.'
    ]);
});

test('cannot reject adoption request without reason', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $response = $this->postJson("/api/adoption-requests/{$adoptionRequest->id}/reject", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reject_reason']);
});

test('cannot reject adoption request with empty reason', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $rejectData = [
        'reject_reason' => ''
    ];

    $response = $this->postJson("/api/adoption-requests/{$adoptionRequest->id}/reject", $rejectData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reject_reason']);
});

test('cannot reject adoption request with reason too long', function () {
    $adoptionRequest = AdoptionRequest::factory()->pending()->create();

    $rejectData = [
        'reject_reason' => str_repeat('a', 1001) // 1001 characters, exceeds max of 1000
    ];

    $response = $this->postJson("/api/adoption-requests/{$adoptionRequest->id}/reject", $rejectData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reject_reason']);
});

test('adoption request responses include reject_reason field', function () {
    $rejectedRequest = AdoptionRequest::factory()->rejected()->create();

    $response = $this->getJson("/api/adoption-requests/{$rejectedRequest->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'reject_reason'
            ]
        ]);
});
