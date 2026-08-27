<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'message' => fake()->optional(0.7)->paragraph(),
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
        ]);
    }

    public function reviewed(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id ?? User::factory(),
        ]);
    }
}
