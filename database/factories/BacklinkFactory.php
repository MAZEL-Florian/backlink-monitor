<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class BacklinkFactory extends Factory
{
    public function definition(): array
    {
        $statusCodes = [200, 404, 301, 302, 500];
        $statusCode = $this->faker->randomElement($statusCodes);
        
        return [
            'project_id' => Project::factory(),
            'source_url' => $this->faker->url(),
            'target_url' => $this->faker->url(),
            'anchor_text' => $this->faker->words(3, true),
            'domain_authority' => $this->faker->numberBetween(1, 100),
            'page_authority' => $this->faker->numberBetween(1, 100),
            'is_active' => $statusCode === 200,
            'is_dofollow' => $this->faker->boolean(70),
            'status_code' => $statusCode,
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'first_found_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
