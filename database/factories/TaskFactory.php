<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'description' => fake()->text(200),
            'start' => fake()->dateTimeBetween('now', '+1 week'),
            'deadline' => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'address' => fake()->address(),
            'notes' => fake()->optional()->text(100),
        ];
    }
}

