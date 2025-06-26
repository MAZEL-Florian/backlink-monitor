<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Jobs\CheckBacklinkJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()
            ->withCount(['backlinks', 'activeBacklinks'])
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['domain'] = $this->cleanDomain($validated['domain']);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Projet créé avec succès !');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $backlinks = $project->backlinks()
            ->with('latestCheck')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => $project->backlinks()->count(),
            'active' => $project->activeBacklinks()->count(),
            'inactive' => $project->inactiveBacklinks()->count(),
            'dofollow' => $project->backlinks()->where('is_dofollow', true)->count(),
            'nofollow' => $project->backlinks()->where('is_dofollow', false)->count(),
        ];

        return view('projects.show', compact('project', 'backlinks', 'stats'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|url|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['domain'] = $this->cleanDomain($validated['domain']);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet mis à jour avec succès !');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès !');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        $projects = Project::whereIn('id', $validated['project_ids'])
            ->where('user_id', Auth::id())
            ->get();

        if ($projects->isEmpty()) {
            return back()->with('error', 'Aucun projet trouvé à supprimer.');
        }

        $count = $projects->count();
        
        foreach ($projects as $project) {
            $project->delete();
        }

        $message = $count === 1 
            ? 'Projet supprimé avec succès !'
            : "{$count} projets supprimés avec succès !";

        return redirect()->route('projects.index')->with('success', $message);
    }

    public function bulkDeleteBacklinks(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'backlink_ids' => 'required|array',
            'backlink_ids.*' => 'exists:backlinks,id',
        ]);

        $backlinks = $project->backlinks()
            ->whereIn('id', $validated['backlink_ids'])
            ->get();

        if ($backlinks->isEmpty()) {
            return back()->with('error', 'Aucun backlink trouvé à supprimer.');
        }

        $count = $backlinks->count();
        
        foreach ($backlinks as $backlink) {
            $backlink->delete();
        }

        $message = $count === 1 
            ? 'Backlink supprimé avec succès !'
            : "{$count} backlinks supprimés avec succès !";

        return back()->with('success', $message);
    }

   public function checkAllBacklinks(Project $project)
{
    $this->authorize('view', $project);

    $backlinks = $project->backlinks;
    
    if ($backlinks->isEmpty()) {
        return back()->with('error', 'Aucun backlink à vérifier pour ce projet.');
    }

    $batchId = 'batch_' . uniqid() . '_' . time();
    
    $cacheKey = "batch_check_results_{$batchId}";
    Cache::put($cacheKey, [
        'user_id' => $project->user_id,
        'batch_id' => $batchId,
        'check_time' => now(),
        'results' => [],
        'completed_count' => 0,
        'total_count' => $backlinks->count(),
    ], now()->addHours(2));

    foreach ($backlinks as $backlink) {
        CheckBacklinkJob::dispatch($backlink, false, 'bulk', $batchId);
    }

    return back()->with('success', "Vérification de {$backlinks->count()} backlinks lancée ! Vous recevrez un rapport par email une fois terminé.");
}

    private function cleanDomain($url)
    {
        $parsed = parse_url($url);
        return $parsed['scheme'] . '://' . $parsed['host'];
    }
}
