<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderDocument>
 */
class ProviderDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-2 years', 'now');
        $expirationDate = fake()->dateTimeBetween($issueDate, '+2 years');

        return [
            'provider_id' => Provider::factory(),
            'type' => fake()->randomElement(['cv', 'license', 'dea', 'malpractice', 'education', 'certification', 'other']),
            'name' => fake()->randomElement(['Medical License', 'CV', 'DEA Registration', 'Malpractice Insurance', 'Board Certification']),
            'file_path' => 'provider-documents/1/fake-file.pdf',
            'file_name' => 'fake-file.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10_000, 5_000_000),
            'checksum' => fake()->sha256(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $expirationDate->format('Y-m-d'),
            'version' => 1,
            'version_group' => (string) fake()->uuid(),
            'is_current' => true,
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the document is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => false,
            'archived_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
}
