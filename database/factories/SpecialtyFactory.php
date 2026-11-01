<?php

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specialty>
 */
class SpecialtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Cardiology',
                'Pediatrics',
                'Family Medicine',
                'Internal Medicine',
                'Surgery',
                'Psychiatry',
                'Dermatology',
                'Neurology',
                'Orthopedics',
                'Emergency Medicine',
                'Anesthesiology',
                'Radiology',
                'Pathology',
                'Obstetrics and Gynecology',
                'Ophthalmology',
                'Otolaryngology',
                'Urology',
                'Oncology',
                'Gastroenterology',
                'Pulmonology',
            ]),
            'code' => fake()->optional()->bothify('???###'),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
