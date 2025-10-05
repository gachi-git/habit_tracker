<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\habits>
 */
class HabitsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => null,
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'target_frequency' => $this->faker->numberBetween(1, 5),
            'target_unit' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'is_active' => true,
        ];
    }
}
