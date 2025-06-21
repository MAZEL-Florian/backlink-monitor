<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    private function cleanDomain($url)
    {
        $parsed = parse_url($url);
        return $parsed['scheme'] . '://' . $parsed['host'];
    }
}
