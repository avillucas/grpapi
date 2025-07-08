<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use App\Models\AdoptionRequest;
use App\Models\AdoptionRequestStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdoptionRequest>
 */
class AdoptionRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdoptionRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(AdoptionRequestStatus::cases());
        
        return [
            'pet_id' => Pet::factory(),
            'user_id' => User::factory(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'application' => $this->faker->paragraph(3),
            'status' => $status->value,
            'reject_reason' => $status === AdoptionRequestStatus::REJECTED 
                ? $this->faker->randomElement([
                    'No cumple con los requisitos de espacio necesario',
                    'No tiene experiencia previa con mascotas',
                    'La mascota necesita un ambiente más tranquilo',
                    'No puede comprometerse con el tiempo necesario',
                    'No cuenta con el presupuesto suficiente para los cuidados'
                ])
                : null,
        ];
    }

    /**
     * Indicate that the adoption request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdoptionRequestStatus::PENDING->value,
        ]);
    }

    /**
     * Indicate that the adoption request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdoptionRequestStatus::APPROVED->value,
        ]);
    }

    /**
     * Indicate that the adoption request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdoptionRequestStatus::REJECTED->value,
            'reject_reason' => $this->faker->randomElement([
                'No cumple con los requisitos de espacio necesario',
                'No tiene experiencia previa con mascotas',
                'La mascota necesita un ambiente más tranquilo',
                'No puede comprometerse con el tiempo necesario',
                'No cuenta con el presupuesto suficiente para los cuidados'
            ]),
        ]);
    }

    /**
     * Create an adoption request for a specific pet and user.
     */
    public function forPetAndUser(Pet $pet, User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'pet_id' => $pet->id,
            'user_id' => $user->id,
        ]);
    }
}
