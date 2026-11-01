<?php

namespace Database\Factories;

use App\Models\ProviderHospitalAffiliation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderHospitalAffiliation>
 */
class ProviderHospitalAffiliationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_name' => fake()->randomElement(['General Hospital', 'City Medical Center', 'University Medical Center']),
            'department' => fake()->optional()->randomElement(['Cardiology', 'Emergency', 'Surgery']),
            'affiliation_type' => fake()->randomElement(['admitting', 'courtesy', 'consulting', 'active', 'affiliate', 'other']),
            'start_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['active', 'inactive', 'terminated']),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'contact_email' => fake()->optional()->safeEmail(),
            'evidence_document_id' => null,
        ];
    }
}
