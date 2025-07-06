<?php

use App\Models\Pet;
use App\Models\User;
use App\Models\PetStatus;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    Storage::fake('public');
});

test('can list all pets', function () {
    Pet::factory()->count(3)->create();

    $response = $this->get('/api/pets');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'data' => [
            '*' => [
                'id',
                'name',
                'photo',
                'photo_url',
                'status',
                'created_at',
                'updated_at'
            ]
        ]
    ]);
    
    $this->assertCount(3, $response->json('data'));
});

test('can show a specific pet', function () {
    $pet = Pet::factory()->create([
        'name' => 'Buddy',
        'status' => PetStatus::TRANSIT->value
    ]);

    $response = $this->get("/api/pets/{$pet->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Pet retrieved successfully',
        'data' => [
            'id' => $pet->id,
            'name' => 'Buddy',
            'status' => 'transit'
        ]
    ]);
});

test('returns 404 when showing non-existent pet', function () {
    $response = $this->get('/api/pets/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Pet not found'
    ]);
});

test('can create a pet without photo', function () {
    $petData = [
        'name' => 'Rex',
        'status' => 'adopted'
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Pet created successfully',
        'data' => [
            'name' => 'Rex',
            'status' => 'adopted',
            'photo' => null
        ]
    ]);

    $this->assertDatabaseHas('pets', [
        'name' => 'Rex',
        'status' => 'adopted'
    ]);
});

test('can create a pet with photo', function () {
    $file = UploadedFile::fake()->image('pet.jpg', 800, 600)->size(1024); // 1MB

    $petData = [
        'name' => 'Luna',
        'status' => 'transit',
        'photo' => $file
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Pet created successfully',
        'data' => [
            'name' => 'Luna',
            'status' => 'transit'
        ]
    ]);

    $pet = Pet::where('name', 'Luna')->first();
    $this->assertNotNull($pet->photo);
    $this->assertTrue(Storage::disk('public')->exists($pet->photo));
});

test('validates photo size limit', function () {
    $file = UploadedFile::fake()->image('big-pet.jpg', 2000, 2000)->size(3072); // 3MB (over limit)

    $petData = [
        'name' => 'BigPet',
        'photo' => $file
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['photo']);
});

test('validates photo file types', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1024); // PDF file

    $petData = [
        'name' => 'TestPet',
        'photo' => $file
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['photo']);
});

test('validates pet status enum values', function () {
    $petData = [
        'name' => 'InvalidPet',
        'status' => 'invalid_status'
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['status']);
});

test('creates pet with default transit status when no status provided', function () {
    $petData = [
        'name' => 'DefaultPet'
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'status' => 'transit'
        ]
    ]);
});

test('validation fails when creating pet without name', function () {
    $petData = [
        'status' => 'adopted'
    ];

    $response = $this->post('/api/pets', $petData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('can update a pet', function () {
    $pet = Pet::factory()->create([
        'name' => 'OldName',
        'status' => PetStatus::TRANSIT->value
    ]);

    $updateData = [
        'name' => 'NewName',
        'status' => 'adopted'
    ];

    $response = $this->put("/api/pets/{$pet->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Pet updated successfully',
        'data' => [
            'id' => $pet->id,
            'name' => 'NewName',
            'status' => 'adopted'
        ]
    ]);

    $this->assertDatabaseHas('pets', [
        'id' => $pet->id,
        'name' => 'NewName',
        'status' => 'adopted'
    ]);
});

test('can partially update a pet', function () {
    $pet = Pet::factory()->create([
        'name' => 'OriginalName',
        'status' => PetStatus::TRANSIT->value
    ]);

    $updateData = [
        'status' => 'adopted'
    ];

    $response = $this->put("/api/pets/{$pet->id}", $updateData);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'name' => 'OriginalName', // Should remain unchanged
            'status' => 'adopted'
        ]
    ]);
});

test('can update pet photo', function () {
    $pet = Pet::factory()->withPhoto()->create();
    $oldPhoto = $pet->photo;

    $newFile = UploadedFile::fake()->image('new-pet.png', 600, 600)->size(512);

    $updateData = [
        'photo' => $newFile
    ];

    $response = $this->put("/api/pets/{$pet->id}", $updateData);

    $response->assertStatus(200);
    
    $pet->refresh();
    $this->assertNotEquals($oldPhoto, $pet->photo);
    $this->assertTrue(Storage::disk('public')->exists($pet->photo));
});

test('returns 404 when updating non-existent pet', function () {
    $updateData = [
        'name' => 'Updated Name'
    ];

    $response = $this->put('/api/pets/999', $updateData);

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Pet not found'
    ]);
});

test('can delete a pet', function () {
    $pet = Pet::factory()->create();

    $response = $this->delete("/api/pets/{$pet->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Pet deleted successfully'
    ]);

    $this->assertDatabaseMissing('pets', [
        'id' => $pet->id
    ]);
});

test('deletes pet photo when deleting pet', function () {
    $pet = Pet::factory()->withPhoto()->create();
    $photoPath = $pet->photo;

    Storage::disk('public')->put($photoPath, 'fake image content');
    $this->assertTrue(Storage::disk('public')->exists($photoPath));

    $response = $this->delete("/api/pets/{$pet->id}");

    $response->assertStatus(200);
    $this->assertFalse(Storage::disk('public')->exists($photoPath));
});

test('returns 404 when deleting non-existent pet', function () {
    $response = $this->delete('/api/pets/999');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Pet not found'
    ]);
});

test('requires authentication for all pet operations', function () {
    $pet = Pet::factory()->create();

    $response = $this->withoutMiddleware(['auth:sanctum'])->get('/api/pets');
    $response->assertStatus(401);
});
