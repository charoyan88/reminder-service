<?php

namespace Database\Factories;

use App\Models\ReminderIntervalConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderIntervalConfigFactory extends Factory
{
    protected $model = ReminderIntervalConfig::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'reminder_type' => $this->faker->randomElement(['pre_expiration', 'post_expiration']),
            'days' => $this->faker->numberBetween(1, 30),
            'is_default' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function preExpiration(): ReminderIntervalConfigFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'reminder_type' => 'pre_expiration',
            ];
        });
    }

    public function postExpiration(): ReminderIntervalConfigFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'reminder_type' => 'post_expiration',
            ];
        });
    }

    public function default(): ReminderIntervalConfigFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }

    public function inactive(): ReminderIntervalConfigFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
} 