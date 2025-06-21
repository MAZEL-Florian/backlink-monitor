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
        // Créer un utilisateur de test
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Créer des projets de test
        $projects = Project::factory(3)->create([
            'user_id' => $user->id,
        ]);

        // Créer des backlinks de test
        foreach ($projects as $project) {
            Backlink::factory(10)->create([
                'project_id' => $project->id,
            ]);
        }
    }
}
