<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\PetStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'photo' => null, // Por defecto sin foto
            'status' => $this->faker->randomElement(PetStatus::cases())->value,
        ];
    }

    /**
     * Indicate that the pet is in transit.
     */
    public function transit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PetStatus::TRANSIT->value,
        ]);
    }

    /**
     * Indicate that the pet is adopted.
     */
    public function adopted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PetStatus::ADOPTED->value,
        ]);
    }

    /**
     * Indicate that the pet is deceased.
     */
    public function deceased(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PetStatus::DECEASED->value,
        ]);
    }

    /**
     * Indicate that the pet has a photo.
     */
    public function withPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo' => 'pets/sample-pet.jpg',
        ]);
    }
}
