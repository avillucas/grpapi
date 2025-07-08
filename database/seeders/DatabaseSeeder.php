<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\User;
use App\Models\AdoptionOffer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create some pets
        $pets = Pet::factory()->count(10)->create();

        // Create adoption offers for some pets (not all)
        $pets->take(5)->each(function ($pet) {
            AdoptionOffer::factory()->create(['pet_id' => $pet->id]);
        });
    }
}
