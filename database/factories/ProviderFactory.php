<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $npi = '1000000000';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static::$npi = (string) ((int) static::$npi + 1);

        return [
            'display_name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->randomElement([null, fake()->firstName()]),
            'email' => fake()->unique()->safeEmail(),
            'npi' => static::$npi,
            'phone' => fake()->phoneNumber(),
            'fax' => fake()->randomElement([null, fake()->phoneNumber()]),
            'dob' => fake()->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'provider_type' => fake()->randomElement(['physician', 'nurse_practitioner', 'physician_assistant', 'other']),
            'specialty' => fake()->randomElement(['Cardiology', 'Pediatrics', 'Family Medicine', 'Internal Medicine']),
            'taxonomy_code' => fake()->optional()->bothify('???#######'),
            'status' => fake()->randomElement(['active', 'inactive', 'pending', 'suspended']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
