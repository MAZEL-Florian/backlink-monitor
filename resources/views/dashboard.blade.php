@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Vue d'ensemble de vos backlinks et projets</p>
    </div>

    <!-- Stats Cards -->
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
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
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
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
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
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
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
        <!-- Evolution Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des Backlinks (30 derniers jours)</h3>
            <canvas id="evolutionChart" width="400" height="200"></canvas>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Distribution par Statut</h3>
            <canvas id="statusChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Projects -->
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
                                <p class="text-sm text-green-600">{{ $project->active_backlinks_count }} actifs</p>
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

        <!-- Recent Checks -->
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $check->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $check->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $check->checked_at }}
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
</div>

@push('scripts')
<script>
    // Evolution Chart
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: @json($evolutionData['labels']),
            datasets: [{
                label: 'Actifs',
                data: @json($evolutionData['active']),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Inactifs',
                data: @json($evolutionData['inactive']),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
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

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusDistribution);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => `HTTP ${item.status_code || 'Error'}`),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    'rgb(34, 197, 94)',   // 200 - Green
                    'rgb(239, 68, 68)',   // 404 - Red
                    'rgb(251, 191, 36)',  // 301/302 - Yellow
                    'rgb(156, 163, 175)', // Others - Gray
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
