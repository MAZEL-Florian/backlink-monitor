@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Vue d'ensemble de vos backlinks et projets</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Projets</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_projects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-emerald-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Backlinks</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_backlinks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-emerald-600 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Backlinks Actifs</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['active_backlinks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-rose-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Backlinks Inactifs</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['inactive_backlinks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des Backlinks (30 derniers jours)</h3>
            <canvas id="evolutionChart" width="400" height="200"></canvas>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Distribution par Statut</h3>
            <canvas id="statusChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Projets Récents</h3>
                    <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700">
                        Nouveau Projet
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentProjects as $project)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                        {{ $project->name }}
                                    </a>
                                </h4>
                                <p class="text-sm text-gray-500">{{ $project->domain }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $project->backlinks_count }} backlinks</p>
                                <p class="text-sm text-emerald-600">{{ $project->active_backlinks_count }} actifs</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-gray-500">Aucun projet trouvé</p>
                        <a href="{{ route('projects.create') }}" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                            Créer votre premier projet
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Vérifications Récentes</h3>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentChecks as $check)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $check->backlink->project->name }}
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ parse_url($check->backlink->source_url, PHP_URL_HOST) }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $check->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $check->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $check->checked_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-gray-500">Aucune vérification récente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($recentBacklinks->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Backlinks Récents avec Uptime</h3>
                    <a href="{{ route('backlinks.index') }}" class="text-blue-600 hover:text-blue-900 text-sm">
                        Voir tous les backlinks →
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($recentBacklinks as $backlink)
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">
                                    <a href="{{ route('backlinks.show', $backlink) }}" class="hover:text-blue-600">
                                        {{ $backlink->source_domain }}
                                    </a>
                                </h4>
                                <p class="text-sm text-gray-500">{{ $backlink->project->name }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $backlink->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                @if(isset($backlink->uptime_data))
                                    <span class="text-sm font-medium text-blue-600">{{ $backlink->uptime_data['uptime_percentage'] }}%</span>
                                @endif
                            </div>
                        </div>
                        
                        @if(isset($backlink->uptime_data))
                            <div class="mt-3">
                                <div class="flex items-center space-x-1 uptime-timeline" data-preserve="true">
                                    @foreach(array_slice($backlink->uptime_data['data'], -14) as $day)
                                        <div class="w-2 h-6 rounded-sm timeline-bar {{ $day['has_check'] ? ($day['is_active'] ? 'bg-emerald-500' : 'bg-rose-500') : 'bg-gray-200' }}"
                                             title="{{ $day['date'] }}: {{ $day['has_check'] ? ($day['is_active'] ? 'Active' : 'Inactive') : 'No check' }}"
                                             data-preserve="true">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>14 derniers jours</span>
                                    <span>{{ $backlink->uptime_data['active_days'] }}/{{ $backlink->uptime_data['total_days'] }} jours actifs</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            @if($recentBacklinks->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $recentBacklinks->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: @json($evolutionData['labels']),
            datasets: [{
                label: 'Actifs',
                data: @json($evolutionData['active']),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.1
            }, {
                label: 'Inactifs',
                data: @json($evolutionData['inactive']),
                borderColor: 'rgb(244, 63, 94)',
                backgroundColor: 'rgba(244, 63, 94, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusDistribution);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => `HTTP ${item.status_code || 'Error'}`),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    'rgb(16, 185, 129)',
                    'rgb(244, 63, 94)',
                    'rgb(245, 158, 11)',
                    'rgb(156, 163, 175)',
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection
