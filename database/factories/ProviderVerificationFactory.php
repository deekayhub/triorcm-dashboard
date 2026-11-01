<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderVerification>
 */
class ProviderVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'provider_application_id' => null,
            'type' => fake()->randomElement([
                'primary_source',
                'education',
                'training',
                'work_history',
                'malpractice',
                'sanctions',
                'other',
            ]),
            'verification_source' => fake()->optional()->sentence(),
            'status' => fake()->randomElement([
                'pending',
                'in_progress',
                'verified',
                'unable_to_verify',
                'discrepancy',
            ]),
            'requested_at' => fake()->date('Y-m-d', '-2 years'),
            'received_at' => fake()->optional()->date('Y-m-d', '-1 year'),
            'verified_at' => fake()->optional()->date('Y-m-d', '-1 year'),
            'verified_by' => null,
            'notes' => fake()->optional()->sentence(),
            'findings' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the verification is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => fake()->date('Y-m-d', '-1 year'),
        ]);
    }

    /**
     * Indicate that the verification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'verified_at' => null,
        ]);
    }
}
