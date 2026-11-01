<?php

namespace Database\Factories;

use App\Models\ProviderLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderLocation>
 */
class ProviderLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_name' => fake()->randomElement(['Main Office', 'Downtown Clinic', 'North Campus']),
            'practice_name' => fake()->optional()->company(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->postcode(),
            'country' => 'US',
            'phone' => fake()->optional()->phoneNumber(),
            'fax' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'office_hours' => null,
            'is_primary' => fake()->boolean(20),
            'is_billing_address' => fake()->boolean(30),
            'effective_start_date' => fake()->optional()->date(),
            'effective_end_date' => fake()->optional()->date(),
            'is_active' => true,
        ];
    }
}
