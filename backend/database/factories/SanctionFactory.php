<?php

namespace Database\Factories;

use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sanction>
 */
class SanctionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'issued_by' => User::factory(),
            'type' => '7-day',
            'reason' => 'Прекршување на правилата.',
            'expires_at' => now()->addDays(7),
            'acknowledged_at' => null,
        ];
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'warning',
            'expires_at' => null,
        ]);
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'permanent_ban',
            'expires_at' => null,
        ]);
    }

    public function custom(int $days = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'custom',
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => '7-day',
            'expires_at' => now()->subDay(),
        ]);
    }
}
