<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\AdoptionOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdoptionOffer>
 */
class AdoptionOfferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdoptionOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            '¡Adoptame!',
            'Busco hogar',
            'Familia loving',
            'Mi nuevo hogar',
            'Dame amor',
            '¡Soy tu amigo!',
            'Hogar dulce',
            'Tu compañero'
        ];

        $headlines = [
            'Soy un compañero leal que busca una familia que me quiera y me cuide para toda la vida.',
            'Me encanta jugar y dar mucho amor. ¿Serás vos mi nueva familia?',
            'Busco un hogar donde pueda correr, jugar y recibir muchos mimos todos los días.',
            'Soy muy cariñoso y me llevo bien con otros animales. ¡Adoptame!',
            'Necesito una familia que me dé amor, cuidados y muchas aventuras juntos.',
            'Soy el compañero perfecto para alguien que busque amor incondicional.',
            'Me gusta pasear, jugar y estar cerca de las personas. ¿Me das una oportunidad?',
            'Tengo mucho amor para dar y estoy esperando a mi familia ideal.'
        ];

        return [
            'pet_id' => Pet::factory(),
            'title' => $this->faker->randomElement($titles),
            'headline' => $this->faker->randomElement($headlines),
        ];
    }
}
