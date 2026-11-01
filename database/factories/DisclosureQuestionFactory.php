<?php

namespace Database\Factories;

use App\Models\DisclosureQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisclosureQuestion>
 */
class DisclosureQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement([
                'Professional Conduct',
                'Licensure & Certification',
                'Criminal & Legal',
                'Malpractice & Claims',
                'Health & Impairment',
            ]),
            'question' => rtrim(fake()->sentence(), '.').'?',
            'response_type' => 'yes_no_na',
            'is_required' => true,
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    public function optional(): static
    {
        return $this->state(fn () => ['is_required' => false]);
    }
}
