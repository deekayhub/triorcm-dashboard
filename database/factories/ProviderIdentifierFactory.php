<?php

namespace Database\Factories;

use App\Models\ProviderIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderIdentifier>
 */
class ProviderIdentifierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identifier_type' => fake()->randomElement(['dea', 'taxonomy', 'clia', 'cds', 'other']),
            'identifier_value' => fake()->bothify('??#######'),
            'issuing_state' => fake()->optional()->stateAbbr(),
            'issue_date' => fake()->optional()->date(),
            'expiration_date' => fake()->optional()->date(),
            'verification_status' => fake()->randomElement(['unverified', 'pending', 'verified', 'failed']),
            'verification_notes' => fake()->optional()->sentence(),
        ];
    }
}
