<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Backlink;
use App\Jobs\CheckBacklinkJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BacklinkController extends Controller
{
    public function index(Request $request)
    {
        $query = Backlink::whereHas('project', function($q) {
            $q->where('user_id', Auth::id());
        })->with(['project', 'latestCheck']);

        // Filtres
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'dofollow') {
                $query->where('is_dofollow', true);
            } elseif ($request->type === 'nofollow') {
                $query->where('is_dofollow', false);
            }
        }

        if ($request->filled('domain')) {
            $query->where('source_url', 'like', '%' . $request->domain . '%');
        }

        $backlinks = $query->latest()->paginate(20);
        $projects = Auth::user()->projects;

        return view('backlinks.index', compact('backlinks', 'projects'));
    }

    public function create(Request $request)
    {
        $projects = Auth::user()->projects;
        $selectedProject = $request->project_id ? 
            Project::find($request->project_id) : null;

        return view('backlinks.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'source_url' => 'required|url|max:500',
            'target_url' => 'required|url|max:500',
            'anchor_text' => 'nullable|string|max:255',
            'domain_authority' => 'nullable|integer|min:0|max:100',
            'page_authority' => 'nullable|integer|min:0|max:100',
            'is_dofollow' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        // Vérifier si le backlink existe déjà
        $existingBacklink = Backlink::where('project_id', $validated['project_id'])
            ->where('source_url', $validated['source_url'])
            ->first();

        if ($existingBacklink) {
            return back()->withErrors(['source_url' => 'Ce backlink existe déjà pour ce projet.']);
        }

        $validated['first_found_at'] = now();
        $backlink = Backlink::create($validated);

        // Lancer la vérification immédiate
        CheckBacklinkJob::dispatch($backlink);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Backlink ajouté avec succès ! Vérification en cours...');
    }

    public function show(Backlink $backlink)
    {
        $this->authorize('view', $backlink->project);

        $checks = $backlink->checks()
            ->latest('checked_at')
            ->paginate(20);

        return view('backlinks.show', compact('backlink', 'checks'));
    }

    public function edit(Backlink $backlink)
    {
        $this->authorize('update', $backlink->project);
        return view('backlinks.edit', compact('backlink'));
    }

    public function update(Request $request, Backlink $backlink)
    {
        $this->authorize('update', $backlink->project);

        $validated = $request->validate([
            'source_url' => 'required|url|max:500',
            'target_url' => 'required|url|max:500',
            'anchor_text' => 'nullable|string|max:255',
            'domain_authority' => 'nullable|integer|min:0|max:100',
            'page_authority' => 'nullable|integer|min:0|max:100',
            'is_dofollow' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $backlink->update($validated);

        return redirect()->route('backlinks.show', $backlink)
            ->with('success', 'Backlink mis à jour avec succès !');
    }

    public function destroy(Backlink $backlink)
    {
        $this->authorize('delete', $backlink->project);
        
        $backlink->delete();

        return redirect()->route('backlinks.index')
            ->with('success', 'Backlink supprimé avec succès !');
    }

    public function check(Backlink $backlink)
    {
        $this->authorize('view', $backlink->project);

        CheckBacklinkJob::dispatch($backlink);

        return back()->with('success', 'Vérification du backlink lancée !');
    }

    public function bulkCheck(Request $request)
    {
        $validated = $request->validate([
            'backlink_ids' => 'required|array',
            'backlink_ids.*' => 'exists:backlinks,id',
        ]);

        $backlinks = Backlink::whereIn('id', $validated['backlink_ids'])
            ->whereHas('project', function($q) {
                $q->where('user_id', Auth::id());
            })->get();

        foreach ($backlinks as $backlink) {
            CheckBacklinkJob::dispatch($backlink);
        }

        return back()->with('success', 'Vérification de ' . $backlinks->count() . ' backlinks lancée !');
    }
}
