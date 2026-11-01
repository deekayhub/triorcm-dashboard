<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderApplication>
 */
class ProviderApplicationFactory extends Factory
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
            'application_number' => 'APP-' . fake()->unique()->numerify('##########'),
            'type' => fake()->randomElement([
                'initial',
                'recredentialing',
                'modification',
            ]),
            'status' => fake()->randomElement([
                'draft',
                'submitted',
                'in_review',
                'approved',
                'denied',
                'withdrawn',
            ]),
            'submitted_by' => null,
            'submitted_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => fake()->optional()->sentence(),
            'effective_date' => fake()->optional()->date('Y-m-d', '-1 year'),
            'expiration_date' => fake()->optional()->date('Y-m-d', '+1 year'),
        ];
    }

    /**
     * Indicate that the application is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Indicate that the application is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => fake()->dateTimeBetween('-1 year', '-6 months')->format('Y-m-d H:i:s'),
            'reviewed_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
        ]);
    }
}
