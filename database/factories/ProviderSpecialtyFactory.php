<?php

namespace Database\Factories;

use App\Models\ProviderSpecialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderSpecialty>
 */
class ProviderSpecialtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_primary' => fake()->boolean(20),
            'subspecialty' => fake()->optional()->randomElement([
                'Interventional Cardiology',
                'Pediatric Cardiology',
                'Sports Medicine',
                'Geriatrics',
                'Neonatology',
            ]),
            'is_board_certified' => fake()->boolean(70),
            'board_certification_date' => fake()->optional()->date(),
            'is_active' => true,
        ];
    }
}
