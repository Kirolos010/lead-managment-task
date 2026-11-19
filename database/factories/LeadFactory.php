<?php

namespace Database\Factories;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('01#########'),
            'source' => fake()->randomElement(['facebook','website','instagram','linkedin']),
            'priority' => LeadPriority::cases()[array_rand(LeadPriority::cases())],
            'status' => LeadStatus::NEW,
            'next_follow_up_at' => now()->addDays(1),
        ];
    }
}
