<?php

namespace Tests\Feature;

use App\Http\Controllers\BacklinkController;
use App\Jobs\CheckBacklinkJob;
use App\Mail\BacklinkStatusChanged;
use App\Models\Backlink;
use App\Models\BacklinkCheck;
use App\Models\Project;
use App\Models\User;
use App\Services\BacklinkCheckerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class BacklinkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;
    protected $backlink;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
        $this->backlink = Backlink::factory()->create(['project_id' => $this->project->id]);
    }

    /** @test */
    public function it_displays_backlinks_index_for_authenticated_user()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('backlinks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backlinks.index');
        $response->assertViewHas('backlinks');
        $response->assertViewHas('projects');
    }

    /** @test */
    public function it_filters_backlinks_by_project()
    {
        $this->actingAs($this->user);
        
        $project2 = Project::factory()->create(['user_id' => $this->user->id]);
        $backlink2 = Backlink::factory()->create(['project_id' => $project2->id]);

        $response = $this->get(route('backlinks.index', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($this->backlink));
        $this->assertFalse($backlinks->contains($backlink2));
    }

    /** @test */
    public function it_filters_backlinks_by_status()
    {
        $this->actingAs($this->user);
        
        $activeBacklink = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'is_active' => true
        ]);
        
        $inactiveBacklink = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'is_active' => false
        ]);

        // Test active filter
        $response = $this->get(route('backlinks.index', ['status' => 'active']));
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($activeBacklink));
        $this->assertFalse($backlinks->contains($inactiveBacklink));

        // Test inactive filter
        $response = $this->get(route('backlinks.index', ['status' => 'inactive']));
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($inactiveBacklink));
        $this->assertFalse($backlinks->contains($activeBacklink));
    }

    /** @test */
    public function it_filters_backlinks_by_type()
    {
        $this->actingAs($this->user);
        
        $dofollowBacklink = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'is_dofollow' => true
        ]);
        
        $nofollowBacklink = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'is_dofollow' => false
        ]);

        // Test dofollow filter
        $response = $this->get(route('backlinks.index', ['type' => 'dofollow']));
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($dofollowBacklink));
        $this->assertFalse($backlinks->contains($nofollowBacklink));

        // Test nofollow filter
        $response = $this->get(route('backlinks.index', ['type' => 'nofollow']));
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($nofollowBacklink));
        $this->assertFalse($backlinks->contains($dofollowBacklink));
    }

    /** @test */
    public function it_filters_backlinks_by_domain()
    {
        $this->actingAs($this->user);
        
        $backlink1 = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'source_url' => 'https://example.com/page1'
        ]);
        
        $backlink2 = Backlink::factory()->create([
            'project_id' => $this->project->id,
            'source_url' => 'https://other-domain.com/page1'
        ]);

        $response = $this->get(route('backlinks.index', ['domain' => 'example.com']));
        
        $backlinks = $response->viewData('backlinks');
        $this->assertTrue($backlinks->contains($backlink1));
        $this->assertFalse($backlinks->contains($backlink2));
    }

    /** @test */
    public function it_shows_create_backlink_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('backlinks.create'));

        $response->assertStatus(200);
        $response->assertViewIs('backlinks.create');
        $response->assertViewHas('projects');
    }

    /** @test */
    public function it_shows_create_form_with_selected_project()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('backlinks.create', ['project_id' => $this->project->id]));

        $response->assertStatus(200);
        $response->assertViewHas('selectedProject', $this->project);
    }

    /** @test */
    public function it_stores_new_backlinks_successfully()
    {
        $this->actingAs($this->user);
        Queue::fake();

        $urls = "https://example.com/page1\nhttps://example.com/page2\nhttps://example.com/page3";
        
        $response = $this->post(route('backlinks.store'), [
            'project_id' => $this->project->id,
            'source_urls' => $urls,
            'notes' => 'Test notes'
        ]);

        $response->assertRedirect(route('projects.show', $this->project));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('backlinks', [
            'project_id' => $this->project->id,
            'source_url' => 'https://example.com/page1',
            'notes' => 'Test notes'
        ]);

        Queue::assertPushed(CheckBacklinkJob::class, 3);
    }

    /** @test */
    public function it_validates_required_fields_when_storing()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('backlinks.store'), []);

        $response->assertSessionHasErrors(['project_id', 'source_urls']);
    }

    /** @test */
    public function it_rejects_invalid_urls_when_storing()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('backlinks.store'), [
            'project_id' => $this->project->id,
            'source_urls' => "invalid-url\nnot-a-url-either"
        ]);

        $response->assertSessionHasErrors(['source_urls']);
    }

    /** @test */
    public function it_skips_duplicate_backlinks_when_storing()
    {
        $this->actingAs($this->user);
        Queue::fake();

        $existingUrl = 'https://example.com/existing';
        Backlink::factory()->create([
            'project_id' => $this->project->id,
            'source_url' => $existingUrl
        ]);

        $urls = $existingUrl . "\nhttps://example.com/new";

        $response = $this->post(route('backlinks.store'), [
            'project_id' => $this->project->id,
            'source_urls' => $urls
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContains('1 backlink(s) créé(s)', session('success'));
        $this->assertStringContains('1 backlink(s) ignoré(s)', session('success'));
    }

    /** @test */
    public function it_shows_single_backlink()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('backlinks.show', $this->backlink));

        $response->assertStatus(200);
        $response->assertViewIs('backlinks.show');
        $response->assertViewHas('backlink', $this->backlink);
        $response->assertViewHas('checks');
        $response->assertViewHas('uptimeData');
    }

    /** @test */
    public function it_shows_edit_backlink_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('backlinks.edit', $this->backlink));

        $response->assertStatus(200);
        $response->assertViewIs('backlinks.edit');
        $response->assertViewHas('backlink', $this->backlink);
        $response->assertViewHas('uptimeData');
    }

    /** @test */
    public function it_updates_backlink_successfully()
    {
        $this->actingAs($this->user);

        $newData = [
            'source_url' => 'https://updated-example.com',
            'target_url' => 'https://updated-target.com',
            'anchor_text' => 'Updated anchor',
            'domain_authority' => 50,
            'page_authority' => 30,
            'notes' => 'Updated notes'
        ];

        $response = $this->put(route('backlinks.update', $this->backlink), $newData);

        $response->assertRedirect(route('backlinks.show', $this->backlink));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('backlinks', array_merge(
            ['id' => $this->backlink->id],
            $newData
        ));
    }

    /** @test */
    public function it_validates_update_data()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('backlinks.update', $this->backlink), [
            'source_url' => 'invalid-url',
            'target_url' => '',
            'domain_authority' => 150, // Invalid: > 100
            'page_authority' => -10,   // Invalid: < 0
        ]);

        $response->assertSessionHasErrors([
            'source_url',
            'target_url',
            'domain_authority',
            'page_authority'
        ]);
    }

    /** @test */
    public function it_deletes_backlink_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('backlinks.destroy', $this->backlink));

        $response->assertRedirect(route('backlinks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('backlinks', ['id' => $this->backlink->id]);
    }

    /** @test */
    public function it_performs_bulk_delete()
    {
        $this->actingAs($this->user);
        
        $backlink2 = Backlink::factory()->create(['project_id' => $this->project->id]);
        $backlink3 = Backlink::factory()->create(['project_id' => $this->project->id]);

        $response = $this->delete(route('backlinks.bulk-delete'), [
            'backlink_ids' => [$this->backlink->id, $backlink2->id]
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContains('2 backlinks supprimés', session('success'));

        $this->assertDatabaseMissing('backlinks', ['id' => $this->backlink->id]);
        $this->assertDatabaseMissing('backlinks', ['id' => $backlink2->id]);
        $this->assertDatabaseHas('backlinks', ['id' => $backlink3->id]);
    }

    /** @test */
    public function it_validates_bulk_delete_data()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('backlinks.bulk-delete'), []);

        $response->assertSessionHasErrors(['backlink_ids']);
    }

    /** @test */
    public function it_checks_single_backlink_manually()
    {
        $this->actingAs($this->user);
        Queue::fake();

        $response = $this->post(route('backlinks.check', $this->backlink));

        $response->assertSessionHas('success');
        $this->assertStringContains('Vérification lancée', session('success'));

        Queue::assertPushed(CheckBacklinkJob::class, function ($job) {
            return $job->backlink->id === $this->backlink->id && 
                   $job->checkType === 'manual';
        });
    }

    /** @test */
    public function it_performs_bulk_check()
    {
        $this->actingAs($this->user);
        Queue::fake();
        Cache::fake();
        
        $backlink2 = Backlink::factory()->create(['project_id' => $this->project->id]);

        $response = $this->post(route('backlinks.bulk-check'), [
            'backlink_ids' => [$this->backlink->id, $backlink2->id]
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContains('Vérification de 2 backlinks lancée', session('success'));

        Queue::assertPushed(CheckBacklinkJob::class, 2);
        
        // Vérifier que le cache batch a été créé
        $this->assertTrue(Cache::has("batch_check_results_batch_{$this->user->id}_" . time() . "_" . rand(1000, 9999)));
    }

    /** @test */
    public function it_validates_bulk_check_data()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('backlinks.bulk-check'), []);

        $response->assertSessionHasErrors(['backlink_ids']);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_other_users_backlinks()
    {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $otherBacklink = Backlink::factory()->create(['project_id' => $otherProject->id]);

        $this->actingAs($this->user);

        // Test show
        $response = $this->get(route('backlinks.show', $otherBacklink));
        $response->assertStatus(403);

        // Test edit
        $response = $this->get(route('backlinks.edit', $otherBacklink));
        $response->assertStatus(403);

        // Test update
        $response = $this->put(route('backlinks.update', $otherBacklink), [
            'source_url' => 'https://example.com'
        ]);
        $response->assertStatus(403);

        // Test delete
        $response = $this->delete(route('backlinks.destroy', $otherBacklink));
        $response->assertStatus(403);

        // Test check
        $response = $this->post(route('backlinks.check', $otherBacklink));
        $response->assertStatus(403);
    }

    /** @test */
    public function it_calculates_uptime_data_correctly()
    {
        $this->actingAs($this->user);
        
        // Créer des checks sur plusieurs jours
        $threeDaysAgo = now()->subDays(3);
        $twoDaysAgo = now()->subDays(2);
        $yesterday = now()->subDays(1);

        BacklinkCheck::factory()->create([
            'backlink_id' => $this->backlink->id,
            'is_active' => true,
            'checked_at' => $threeDaysAgo,
        ]);

        BacklinkCheck::factory()->create([
            'backlink_id' => $this->backlink->id,
            'is_active' => false,
            'checked_at' => $twoDaysAgo,
        ]);

        BacklinkCheck::factory()->create([
            'backlink_id' => $this->backlink->id,
            'is_active' => true,
            'checked_at' => $yesterday,
        ]);

        $response = $this->get(route('backlinks.show', $this->backlink) . '?uptime_days=7');

        $response->assertStatus(200);
        $uptimeData = $response->viewData('uptimeData');
        
        $this->assertArrayHasKey('uptime_percentage', $uptimeData);
        $this->assertArrayHasKey('total_days', $uptimeData);
        $this->assertArrayHasKey('active_days', $uptimeData);
        $this->assertArrayHasKey('data', $uptimeData);
        
        // 2 jours actifs sur 3 jours avec des checks = 66.7%
        $this->assertEquals(66.7, $uptimeData['uptime_percentage']);
        $this->assertEquals(3, $uptimeData['total_days']);
        $this->assertEquals(2, $uptimeData['active_days']);
    }

    /** @test */
    public function parseUrls_method_works_correctly()
    {
        $controller = new BacklinkController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('parseUrls');
        $method->setAccessible(true);

        $input = "https://example.com/page1\nhttps://example.com/page2\ninvalid-url\n\nhttps://example.com/page3";
        
        $result = $method->invoke($controller, $input);

        $this->assertCount(3, $result);
        $this->assertContains('https://example.com/page1', $result);
        $this->assertContains('https://example.com/page2', $result);
        $this->assertContains('https://example.com/page3', $result);
    }

    /** @test */
    public function parseUrls_removes_duplicates()
    {
        $controller = new BacklinkController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('parseUrls');
        $method->setAccessible(true);

        $input = "https://example.com/page1\nhttps://example.com/page1\nhttps://example.com/page2";
        
        $result = $method->invoke($controller, $input);

        $this->assertCount(2, $result);
        $this->assertContains('https://example.com/page1', $result);
        $this->assertContains('https://example.com/page2', $result);
    }

    /** @test */
    public function guest_users_cannot_access_backlink_routes()
    {
        $response = $this->get(route('backlinks.index'));
        $response->assertRedirect('/login');

        $response = $this->get(route('backlinks.create'));
        $response->assertRedirect('/login');

        $response = $this->post(route('backlinks.store'));
        $response->assertRedirect('/login');

        $response = $this->get(route('backlinks.show', $this->backlink));
        $response->assertRedirect('/login');
    }
}