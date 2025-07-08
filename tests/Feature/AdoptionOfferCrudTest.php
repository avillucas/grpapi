<?php

use App\Models\Pet;
use App\Models\User;
use App\Models\AdoptionOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

test('can list all adoption offers', function () {
    // Create some adoption offers
    AdoptionOffer::factory()->count(3)->create();

    $response = $this->getJson('/api/adoption-offers');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'headline',
                    'pet' => [
                        'id',
                        'name',
                        'photo_url',
                        'status',
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

    expect($response->json('data'))->toHaveCount(3);
});

test('can create an adoption offer', function () {
    $pet = Pet::factory()->create();

    $adoptionOfferData = [
        'pet_id' => $pet->id,
        'title' => '¡Adoptame!',
        'headline' => 'Soy un perrito muy cariñoso que busca una familia que me quiera mucho.'
    ];

    $response = $this->postJson('/api/adoption-offers', $adoptionOfferData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'title',
                'headline',
                'pet',
                'created_at',
                'updated_at'
            ]
        ]);

    $this->assertDatabaseHas('adoption_offers', [
        'pet_id' => $pet->id,
        'title' => '¡Adoptame!',
        'headline' => 'Soy un perrito muy cariñoso que busca una familia que me quiera mucho.'
    ]);
});

test('cannot create adoption offer with invalid data', function () {
    $response = $this->postJson('/api/adoption-offers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pet_id', 'title', 'headline']);
});

test('cannot create adoption offer for non-existent pet', function () {
    $adoptionOfferData = [
        'pet_id' => 999,
        'title' => '¡Adoptame!',
        'headline' => 'Soy un perrito muy cariñoso.'
    ];

    $response = $this->postJson('/api/adoption-offers', $adoptionOfferData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['pet_id']);
});

test('cannot create multiple adoption offers for same pet', function () {
    $pet = Pet::factory()->create();
    
    // Create first adoption offer
    AdoptionOffer::factory()->create(['pet_id' => $pet->id]);

    // Try to create second adoption offer for same pet
    $adoptionOfferData = [
        'pet_id' => $pet->id,
        'title' => 'Otra oferta',
        'headline' => 'Otra descripción'
    ];

    $response = $this->postJson('/api/adoption-offers', $adoptionOfferData);

    $response->assertStatus(409)
        ->assertJson(['message' => 'Pet already has an adoption offer']);
});

test('can show specific adoption offer', function () {
    $offer = AdoptionOffer::factory()->create();

    $response = $this->getJson("/api/adoption-offers/{$offer->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $offer->id,
                'title' => $offer->title,
                'headline' => $offer->headline
            ]
        ]);
});

test('returns 404 for non-existent adoption offer', function () {
    $response = $this->getJson('/api/adoption-offers/999');

    $response->assertStatus(404);
});

test('can update adoption offer', function () {
    $offer = AdoptionOffer::factory()->create();

    $updateData = [
        'title' => 'Nuevo título',
        'headline' => 'Nueva descripción más larga para la oferta de adopción.'
    ];

    $response = $this->putJson("/api/adoption-offers/{$offer->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $offer->id,
                'title' => 'Nuevo título',
                'headline' => 'Nueva descripción más larga para la oferta de adopción.'
            ]
        ]);

    $this->assertDatabaseHas('adoption_offers', [
        'id' => $offer->id,
        'title' => 'Nuevo título',
        'headline' => 'Nueva descripción más larga para la oferta de adopción.'
    ]);
});

test('can delete adoption offer', function () {
    $offer = AdoptionOffer::factory()->create();

    $response = $this->deleteJson("/api/adoption-offers/{$offer->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('adoption_offers', [
        'id' => $offer->id
    ]);
});

test('validates title length', function () {
    $pet = Pet::factory()->create();

    $adoptionOfferData = [
        'pet_id' => $pet->id,
        'title' => 'Este título es demasiado largo y supera los 30 caracteres permitidos',
        'headline' => 'Descripción válida'
    ];

    $response = $this->postJson('/api/adoption-offers', $adoptionOfferData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('validates headline length', function () {
    $pet = Pet::factory()->create();

    $adoptionOfferData = [
        'pet_id' => $pet->id,
        'title' => 'Título válido',
        'headline' => 'Esta descripción es demasiado larga y supera los 120 caracteres permitidos para el campo headline de la oferta de adopción.'
    ];

    $response = $this->postJson('/api/adoption-offers', $adoptionOfferData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['headline']);
});
