<?php

namespace Database\Factories;

use App\Models\ProviderPersonalDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderPersonalDetail>
 */
class ProviderPersonalDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->stateAbbr(),
            'zip_code' => fake()->optional()->postcode(),
            'country' => 'US',
            'languages' => fake()->optional()->randomElements(['English', 'Spanish', 'French'], 2),
            'previous_names' => fake()->optional()->randomElements(['Jane Smith', 'J. Doe'], 1),
        ];
    }
}
