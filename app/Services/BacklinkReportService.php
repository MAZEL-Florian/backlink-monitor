<?php

namespace App\Services;

use App\Models\User;
use App\Models\Backlink;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class BacklinkReportService
{
    public function generateReportData(User $user): array
    {
        $stats = [
            'total_backlinks' => $this->getTotalBacklinks($user),
            'active_backlinks' => $this->getActiveBacklinks($user),
            'inactive_backlinks' => $this->getInactiveBacklinks($user),
            'error_backlinks' => $this->getErrorBacklinks($user),
        ];

        $inactiveBacklinks = Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('is_active', false)
        ->where('status_code', '!=', null)
        ->with(['project', 'latestCheck'])
        ->orderBy('last_checked_at', 'desc')
        ->get();

        $errorBacklinks = Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereHas('latestCheck', function($q) {
            $q->whereNotNull('error_message');
        })
        ->with(['project', 'latestCheck'])
        ->orderBy('last_checked_at', 'desc')
        ->get();

        $projectSummary = Project::where('user_id', $user->id)
            ->withCount([
                'backlinks',
                'backlinks as active_backlinks_count' => function($q) {
                    $q->where('is_active', true);
                },
                'backlinks as inactive_backlinks_count' => function($q) {
                    $q->where('is_active', false);
                }
            ])
            ->having('backlinks_count', '>', 0)
            ->orderBy('name')
            ->get();

        return [
            'stats' => $stats,
            'inactive_backlinks' => $inactiveBacklinks,
            'error_backlinks' => $errorBacklinks,
            'project_summary' => $projectSummary,
        ];
    }

    private function getTotalBacklinks(User $user): int
    {
        return Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();
    }

    private function getActiveBacklinks(User $user): int
    {
        return Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('is_active', true)->count();
    }

    private function getInactiveBacklinks(User $user): int
    {
        return Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('is_active', false)->count();
    }

    private function getErrorBacklinks(User $user): int
    {
        return Backlink::whereHas('project', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereHas('latestCheck', function($q) {
            $q->whereNotNull('error_message');
        })->count();
    }
}
