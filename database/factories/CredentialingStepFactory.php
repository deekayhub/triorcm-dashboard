<?php

namespace Database\Factories;

use App\Models\CredentialingStep;
use App\Models\ProviderApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CredentialingStep>
 */
class CredentialingStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->optional()->dateTimeBetween('-1 year', 'now');

        return [
            'provider_application_id' => ProviderApplication::factory(),
            'name' => fake()->randomElement([
                'Primary Source Verification',
                'Education Verification',
                'Work History Verification',
                'Malpractice Verification',
                'Sanctions Check',
                'License Verification',
                'DEA Registration',
                'Board Certification',
            ]),
            'order' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement([
                'pending',
                'in_progress',
                'completed',
                'skipped',
            ]),
            'assigned_to' => null,
            'started_at' => $startedAt?->format('Y-m-d H:i:s'),
            'completed_at' => fake()->optional()->dateTimeBetween($startedAt, 'now')?->format('Y-m-d H:i:s'),
            'completed_by' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the step is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Indicate that the step is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
        ]);
    }
}
