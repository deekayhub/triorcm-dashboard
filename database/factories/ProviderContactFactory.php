<?php

namespace Database\Factories;

use App\Models\ProviderContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderContact>
 */
class ProviderContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_name' => fake()->name(),
            'job_title' => fake()->randomElement(['Credentialing Specialist', 'Office Manager', 'Billing Coordinator']),
            'organization' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'contact_type' => fake()->randomElement(['credentialing', 'billing', 'practice_manager', 'other']),
            'preferred_method' => fake()->randomElement(['email', 'phone', 'mail']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
