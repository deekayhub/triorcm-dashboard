<?php

namespace Database\Factories;

use App\Models\ProviderReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderReference>
 */
class ProviderReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_name' => fake()->name(),
            'organization' => fake()->company(),
            'designation' => fake()->randomElement(['Medical Director', 'Chief of Staff', 'Department Chair', 'Attending Physician']),
            'relationship' => fake()->randomElement(['Supervisor', 'Colleague', 'Department Chair', 'Program Director']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'reference_date' => fake()->optional()->dateTimeBetween('-5 years', 'now')?->format('Y-m-d'),
            'verification_status' => fake()->randomElement(['unverified', 'pending', 'verified', 'failed']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
