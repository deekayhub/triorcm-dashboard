<?php

namespace Database\Factories;

use App\Models\ProviderEducation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderEducation>
 */
class ProviderEducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_name' => fake()->company(),
            'degree' => fake()->randomElement(['MD', 'DO', 'BS', 'MS', 'PhD']),
            'education_type' => fake()->randomElement(['medical_school', 'residency', 'fellowship', 'internship', 'other_training']),
            'specialty' => fake()->optional()->randomElement(['Cardiology', 'Pediatrics', 'Family Medicine', 'Internal Medicine', 'Surgery']),
            'start_date' => fake()->optional()->date(),
            'completion_date' => fake()->optional()->date(),
            'country' => 'US',
            'state' => fake()->optional()->stateAbbr(),
            'city' => fake()->optional()->city(),
            'verification_status' => fake()->randomElement(['unverified', 'pending', 'verified', 'failed']),
            'document_id' => null,
        ];
    }
}
