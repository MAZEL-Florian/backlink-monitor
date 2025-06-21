<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'domain' => $this->faker->url(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
