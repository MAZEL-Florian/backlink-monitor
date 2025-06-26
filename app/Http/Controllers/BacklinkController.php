<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Backlink;
use App\Models\BacklinkCheck;
use App\Jobs\CheckBacklinkJob;
use App\Mail\BacklinkStatusChanged;
use App\Services\BacklinkCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class BacklinkController extends Controller
{
    public function index(Request $request)
    {
        $query = Backlink::whereHas('project', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['project', 'latestCheck']);

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
            'source_urls' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        $sourceUrls = $this->parseUrls($validated['source_urls']);

        if (empty($sourceUrls)) {
            return back()->withErrors(['source_urls' => 'Aucune URL valide détectée.'])->withInput();
        }

        $createdCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($sourceUrls as $sourceUrl) {
            try {
                $existingBacklink = Backlink::where('project_id', $validated['project_id'])
                    ->where('source_url', $sourceUrl)
                    ->first();

                if ($existingBacklink) {
                    $skippedCount++;
                    continue;
                }

                $backlink = Backlink::create([
                    'project_id' => $validated['project_id'],
                    'source_url' => $sourceUrl,
                    'target_url' => $project->domain,
                    'anchor_text' => null,
                    'domain_authority' => null,
                    'page_authority' => null,
                    'is_dofollow' => true,
                    'is_active' => false,
                    'first_found_at' => now(),
                    'notes' => $validated['notes'],
                ]);

                CheckBacklinkJob::dispatch($backlink, false, 'creation');
                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Erreur pour {$sourceUrl}: " . $e->getMessage();
                Log::error("Erreur création backlink", [
                    'source_url' => $sourceUrl,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $message = "✅ {$createdCount} backlink(s) créé(s) avec succès !";

        if ($skippedCount > 0) {
            $message .= " 🔄 {$skippedCount} backlink(s) ignoré(s) (déjà existants).";
        }

        if (!empty($errors)) {
            $message .= " ⚠️ " . count($errors) . " erreur(s) rencontrée(s).";
        }

        $message .= " 🔍 Vérifications en cours...";

        if ($createdCount > 0) {
            return redirect()->route('projects.show', $project)->with('success', $message);
        } else {
            return back()->withErrors(['source_urls' => 'Aucun backlink n\'a pu être créé.'])->withInput();
        }
    }

    public function show(Backlink $backlink, Request $request)
    {
        $this->authorize('view', $backlink->project);

        $checks = $backlink->checks()
            ->latest('checked_at')
            ->paginate(20);

        $uptimeDays = $request->get('uptime_days', 30);
        $uptimeData = $this->getUptimeData($backlink, $uptimeDays);

        return view('backlinks.show', compact('backlink', 'checks', 'uptimeData'));
    }

    public function edit(Backlink $backlink, Request $request)
    {
        $this->authorize('update', $backlink->project);

        $uptimeDays = $request->get('uptime_days', 30);
        $uptimeData = $this->getUptimeData($backlink, $uptimeDays);

        return view('backlinks.edit', compact('backlink', 'uptimeData'));
    }

    public function update(Request $request, Backlink $backlink)
    {
        $this->authorize('update', $backlink->project);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'source_url' => 'required|url|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $project = Project::where('id', $validated['project_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$project) {
            return back()->withErrors(['project_id' => 'Projet non trouvé ou non autorisé.']);
        }

        if ($backlink->source_url !== $validated['source_url']) {
            $existingBacklink = Backlink::where('project_id', $validated['project_id'])
                ->where('source_url', $validated['source_url'])
                ->where('id', '!=', $backlink->id)
                ->first();

            if ($existingBacklink) {
                return back()->withErrors(['source_url' => 'Cette URL source existe déjà pour ce projet.'])->withInput();
            }
        }

        try {
            $backlink->update([
                'project_id' => $validated['project_id'],
                'source_url' => $validated['source_url'],
                'target_url' => $project->domain,
                'notes' => $validated['notes'],
            ]);

            if ($backlink->wasChanged('source_url')) {
                CheckBacklinkJob::dispatch($backlink, false, 'update');
                $message = 'Backlink mis à jour avec succès ! Une vérification a été lancée pour la nouvelle URL.';
            } else {
                $message = 'Backlink mis à jour avec succès !';
            }

            return redirect()->route('backlinks.show', $backlink)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du backlink", [
                'backlink_id' => $backlink->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Une erreur est survenue lors de la mise à jour.'])->withInput();
        }
    }

    public function destroy(Backlink $backlink)
    {
        $this->authorize('delete', $backlink->project);

        $backlink->delete();

        return redirect()->route('backlinks.index')
            ->with('success', 'Backlink supprimé avec succès !');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'backlink_ids' => 'required|array',
            'backlink_ids.*' => 'exists:backlinks,id',
        ]);

        $backlinks = Backlink::whereIn('id', $validated['backlink_ids'])
            ->whereHas('project', function ($q) {
                $q->where('user_id', Auth::id());
            })->get();

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

    public function check(Backlink $backlink, BacklinkCheckerService $checker)
    {
        $this->authorize('view', $backlink->project);

        try {
            Log::info("Vérification manuelle du backlink {$backlink->id}");

            //             $wasActive = $backlink->is_active;
            //             $result = $checker->check($backlink);

            //             if (!$result['is_dofollow']) {
            //                 $result['is_active'] = false;
            //             }

            //             $check = BacklinkCheck::createFromBacklink($backlink, $result, 'manual');

            //             $backlink->update([
            //                 'status_code' => $result['status_code'],
            //                 'is_active' => $result['is_active'],
            //                 'is_dofollow' => $result['is_dofollow'],
            //                 'anchor_text' => $result['anchor_text'] ?? $backlink->anchor_text,
            //                 'target_url' => $result['target_url'] ?? $backlink->target_url,
            //                 'last_checked_at' => now(),
            //             ]);
            //   if ($wasActive !== $result['is_active']) {
            //             try {
            //                 Mail::to(Auth::user()->email)->send(
            //                     new BacklinkStatusChanged($backlink, $wasActive, $result['is_active'])
            //                 );

            //                 Log::info("Email de changement de statut envoyé (vérification manuelle)", [
            //                     'backlink_id' => $backlink->id,
            //                     'user_email' => Auth::user()->email,
            //                     'was_active' => $wasActive,
            //                     'is_active' => $result['is_active']
            //                 ]);
            //             } catch (\Exception $e) {
            //                 Log::warning("Erreur lors de l'envoi de l'email", [
            //                     'backlink_id' => $backlink->id,
            //                     'error' => $e->getMessage()
            //                 ]);
            //             }
            //         }

            //             Log::info("Vérification manuelle terminée", [
            //                 'backlink_id' => $backlink->id,
            //                 'check_id' => $check->id,
            //                 'is_active' => $result['is_active'],
            //                 'status_code' => $result['status_code']
            //             ]);

            //             $message = $result['is_active']
            //                 ? '✅ Backlink vérifié : ACTIF'
            //                 : '❌ Backlink vérifié : INACTIF';

            //             if (isset($result['status_code'])) {
            //                 $message .= " (HTTP {$result['status_code']})";
            //             }
            CheckBacklinkJob::dispatch($backlink, false, 'manual', null);
            return back()->with('success', '🔍 Vérification lancée ! Vous recevrez une notification en cas de changement de statut.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification manuelle du backlink ' . $backlink->id . ': ' . $e->getMessage());

            $errorResult = [
                'status_code' => null,
                'is_active' => false,
                'is_dofollow' => $backlink->is_dofollow,
                'anchor_text' => $backlink->anchor_text,
                'response_time' => null,
                'error_message' => $e->getMessage(),
                'exact_match' => false,
                'target_url' => $backlink->target_url,
                'user_agent' => null,
                'redirects' => [],
                'content_length' => null,
                'content_type' => null,
                'raw_response' => null,
            ];

            if (!$errorResult['is_dofollow']) {
                $errorResult['is_active'] = false;
            }

            BacklinkCheck::createFromBacklink($backlink, $errorResult, 'manual');
            if ($backlink->is_active) {
                try {
                    Mail::to(Auth::user()->email)->send(
                        new BacklinkStatusChanged($backlink, true, false)
                    );
                } catch (\Exception $mailException) {
                    Log::warning("Erreur lors de l'envoi de l'email d'erreur", [
                        'backlink_id' => $backlink->id,
                        'error' => $mailException->getMessage()
                    ]);
                }
            }
            return back()->with('error', 'Erreur lors de la vérification : ' . $e->getMessage());
        }
    }

    public function bulkCheck(Request $request)
    {
        $validated = $request->validate([
            'backlink_ids' => 'required|array',
            'backlink_ids.*' => 'exists:backlinks,id',
        ]);

        $backlinks = Backlink::whereIn('id', $validated['backlink_ids'])
            ->whereHas('project', function ($q) {
                $q->where('user_id', Auth::id());
            })->get();

        $batchId = 'batch_' . Auth::id() . '_' . time() . '_' . rand(1000, 9999);

        $cacheKey = "batch_check_results_{$batchId}";
        Cache::put($cacheKey, [
            'user_id' => Auth::id(),
            'batch_id' => $batchId,
            'check_time' => now(),
            'results' => [],
            'completed_count' => 0,
            'total_count' => $backlinks->count(),
        ], now()->addHours(2));

        foreach ($backlinks as $backlink) {
            CheckBacklinkJob::dispatch($backlink, false, 'bulk', $batchId);
        }

        return back()->with('success', 'Vérification de ' . $backlinks->count() . ' backlinks lancée ! Vous recevrez un rapport par email.');
    }

    private function parseUrls(string $input): array
    {
        $lines = explode("\n", $input);
        $urls = [];

        foreach ($lines as $line) {
            $url = trim($line);

            if (!empty($url) && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $urls[] = $url;
                }
            }
        }

        return array_unique($urls);
    }

    private function getUptimeData(Backlink $backlink, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $checks = $backlink->checks()
            ->where('checked_at', '>=', $startDate)
            ->where('checked_at', '<=', $endDate)
            ->orderBy('checked_at')
            ->get();

        $checksGrouped = $checks->groupBy(function ($check) {
            $checkedAt = $check->checked_at;

            if (is_string($checkedAt)) {
                $checkedAt = Carbon::parse($checkedAt);
            }

            return $checkedAt->format('Y-m-d');
        });

        $uptimeData = [];
        $totalDays = 0;
        $activeDays = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dayChecks = $checksGrouped->get($dateKey, collect());

            $lastCheck = $dayChecks->last();

            $isActive = $lastCheck ? $lastCheck->is_active : null;

            $uptimeData[] = [
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('M j'),
                'is_active' => $isActive,
                'has_check' => $lastCheck !== null,
                'status_code' => $lastCheck ? $lastCheck->status_code : null,
                'check_type' => $lastCheck ? $lastCheck->check_type : null,
            ];

            if ($lastCheck !== null) {
                $totalDays++;
                if ($isActive) {
                    $activeDays++;
                }
            }
        }

        $uptimePercentage = $totalDays > 0 ? round(($activeDays / $totalDays) * 100, 1) : 0;

        return [
            'data' => $uptimeData,
            'uptime_percentage' => $uptimePercentage,
            'total_days' => $totalDays,
            'active_days' => $activeDays,
            'start_date' => $startDate->format('M j'),
            'end_date' => $endDate->format('M j'),
            'period_days' => $days,
        ];
    }
}
