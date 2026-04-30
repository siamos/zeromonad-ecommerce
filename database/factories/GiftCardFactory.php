<?php

namespace Database\Factories;

use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCard>
 */
class GiftCardFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomElement([10, 25, 50, 100, 150, 200]);

        return [
            'code' => GiftCard::generateCode(),
            'original_amount' => $amount,
            'remaining_balance' => $amount,
            'is_active' => true,
            'expires_at' => fake()->optional(0.5)->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    public function spent(): static
    {
        return $this->state(fn () => ['remaining_balance' => 0, 'is_active' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}
