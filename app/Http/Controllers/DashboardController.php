<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Backlink;
use App\Models\BacklinkCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistiques générales
        $stats = [
            'total_projects' => $user->projects()->count(),
            'total_backlinks' => Backlink::whereHas('project', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'active_backlinks' => Backlink::whereHas('project', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('is_active', true)->count(),
            'inactive_backlinks' => Backlink::whereHas('project', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('is_active', false)->count(),
        ];

        // Projets récents
        $recentProjects = $user->projects()
            ->withCount(['backlinks', 'activeBacklinks'])
            ->latest()
            ->take(5)
            ->get();

        // Backlinks récemment vérifiés
        $recentChecks = BacklinkCheck::whereHas('backlink.project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['backlink.project'])
        ->latest('checked_at')
        ->take(10)
        ->get();

        // Évolution des backlinks (30 derniers jours)
        $evolutionData = $this->getBacklinkEvolution($user);

        // Distribution par statut
        $statusDistribution = Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->select('status_code', DB::raw('count(*) as count'))
        ->groupBy('status_code')
        ->get();

        return view('dashboard', compact(
            'stats', 
            'recentProjects', 
            'recentChecks', 
            'evolutionData',
            'statusDistribution'
        ));
    }

    private function getBacklinkEvolution($user)
    {
        $data = BacklinkCheck::whereHas('backlink.project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('checked_at', '>=', now()->subDays(30))
        ->select(
            DB::raw('DATE(checked_at) as date'),
            DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active'),
            DB::raw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m');
            }),
            'active' => $data->pluck('active'),
            'inactive' => $data->pluck('inactive')
        ];
    }
}
