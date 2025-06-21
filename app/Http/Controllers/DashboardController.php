<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Backlink;
use App\Models\BacklinkCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
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

        $recentProjects = $user->projects()
            ->withCount(['backlinks', 'activeBacklinks'])
            ->latest()
            ->take(5)
            ->get();

        $recentChecks = BacklinkCheck::whereHas('backlink.project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['backlink.project'])
        ->latest('checked_at')
        ->take(10)
        ->get();

        $recentBacklinks = Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['project'])
        ->latest()
        ->paginate(10);

        foreach ($recentBacklinks as $backlink) {
            $backlink->uptime_data = $this->getUptimeData($backlink, 14);
        }

        $evolutionData = $this->getBacklinkEvolution($user);

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
            'recentBacklinks',
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
                return Carbon::parse($date)->format('d/m');
            }),
            'active' => $data->pluck('active'),
            'inactive' => $data->pluck('inactive')
        ];
    }

    private function getUptimeData(Backlink $backlink, int $days = 14): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        $checks = $backlink->checks()
            ->where('checked_at', '>=', $startDate)
            ->where('checked_at', '<=', $endDate)
            ->orderBy('checked_at')
            ->get();

        $checksGrouped = $checks->groupBy(function($check) {
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
