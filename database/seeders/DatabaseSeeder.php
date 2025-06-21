<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Backlink;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $projects = Project::factory(3)->create([
            'user_id' => $user->id,
        ]);

        foreach ($projects as $project) {
            Backlink::factory(10)->create([
                'project_id' => $project->id,
            ]);
        }
    }
}
