<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\habit_records>
 */
class HabitRecordsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'habit_id' => \App\Models\Habits::factory(),
            'recorded_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'completed' => $this->faker->boolean(70), // 70%の確率で完了
            'duration_minutes' => $this->faker->optional(0.6)->numberBetween(5, 120), // 60%の確率で時間記録
            'note' => $this->faker->optional(0.3)->sentence(), // 30%の確率でメモ
        ];
    }
}
