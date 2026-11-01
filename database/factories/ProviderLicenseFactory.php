<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderLicense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderLicense>
 */
class ProviderLicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-5 years', 'now');
        $expirationDate = fake()->dateTimeBetween($issueDate, '+5 years');

        return [
            'provider_id' => Provider::factory(),
            'license_number' => fake()->numerify('LIC#######'),
            'license_type' => fake()->randomElement([
                'md',
                'do',
                'np',
                'pa',
                'rn',
                'other',
            ]),
            'state' => fake()->randomElement([
                'CA',
                'TX',
                'NY',
                'FL',
                'IL',
            ]),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $expirationDate->format('Y-m-d'),
            'status' => fake()->randomElement([
                'active',
                'expired',
                'suspended',
                'revoked',
            ]),
            'issuing_board' => fake()->optional()->company(),
        ];
    }

    /**
     * Indicate that the license is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the license is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiration_date' => fake()->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d'),
        ]);
    }
}
