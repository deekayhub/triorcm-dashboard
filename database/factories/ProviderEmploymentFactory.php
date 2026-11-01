<?php

namespace Database\Factories;

use App\Models\ProviderEmployment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderEmployment>
 */
class ProviderEmploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-10 years', '-1 year');
        $endDate = fake()->dateTimeBetween($startDate, 'now');

        return [
            'employer' => fake()->randomElement(['General Hospital', 'City Medical Center', 'University Medical Center']),
            'position' => fake()->randomElement(['Physician', 'Senior Physician', 'Medical Director']),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'locum', 'contract', 'other']),
            'address' => fake()->optional()->streetAddress(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_current' => fake()->boolean(30),
            'gap_explanation' => fake()->optional()->sentence(),
            'verification_status' => fake()->randomElement(['unverified', 'pending', 'verified', 'failed']),
        ];
    }
}
