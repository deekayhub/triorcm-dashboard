<?php

namespace Database\Factories;

use App\Models\ProviderInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderInsurance>
 */
class ProviderInsuranceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carrier' => fake()->randomElement(['Aetna', 'Blue Cross Blue Shield', 'Cigna', 'UnitedHealthcare', 'Humana']),
            'policy_number' => fake()->bothify('POL##########'),
            'coverage_type' => fake()->randomElement(['claims_made', 'occurrence']),
            'per_claim_amount' => fake()->randomElement([1000000, 2000000, 3000000]),
            'aggregate_amount' => fake()->randomElement([3000000, 5000000, 7000000]),
            'effective_date' => fake()->date(),
            'expiration_date' => fake()->date(),
            'agent_contact' => fake()->optional()->name(),
            'agent_phone' => fake()->optional()->phoneNumber(),
            'document_id' => null,
            'verification_status' => fake()->randomElement(['unverified', 'pending', 'verified', 'failed']),
        ];
    }
}
