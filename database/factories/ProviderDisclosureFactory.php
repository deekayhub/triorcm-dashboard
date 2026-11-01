<?php

namespace Database\Factories;

use App\Models\DisclosureQuestion;
use App\Models\Provider;
use App\Models\ProviderDisclosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderDisclosure>
 */
class ProviderDisclosureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $response = fake()->randomElement(['yes', 'no', 'na']);

        return [
            'provider_id' => Provider::factory(),
            'disclosure_question_id' => DisclosureQuestion::factory(),
            'response' => $response,
            'explanation' => $response === 'yes' ? fake()->sentence() : null,
            'review_status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'review_status' => 'approved',
            'reviewed_at' => now(),
        ]);
    }
}
