@extends('layouts.app')

@section('title', 'Journaux des Vérifications')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Journaux des Vérifications</h1>
            <p class="mt-2 text-gray-600">Consultez les logs détaillés des vérifications de backlinks</p>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">Projet</label>
                <select name="project_id" id="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les projets</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="backlink_id" class="block text-sm font-medium text-gray-700">Backlink</label>
                <select name="backlink_id" id="backlink_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les backlinks</option>
                    @foreach($backlinks as $backlink)
                        <option value="{{ $backlink->id }}" {{ request('backlink_id') == $backlink->id ? 'selected' : '' }}>
                            {{ $backlink->source_domain }} → {{ $backlink->project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="level" class="block text-sm font-medium text-gray-700">Niveau</label>
                <select name="level" id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les niveaux</option>
                    <option value="info" {{ request('level') == 'info' ? 'selected' : '' }}>Information</option>
                    <option value="warning" {{ request('level') == 'warning' ? 'selected' : '' }}>Avertissement</option>
                    <option value="error" {{ request('level') == 'error' ? 'selected' : '' }}>Erreur</option>
                    <option value="debug" {{ request('level') == 'debug' ? 'selected' : '' }}>Debug</option>
                </select>
            </div>

            <div class="md:col-span-3 flex justify-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Filtrer
                </button>
                <a href="{{ route('backlink-logs.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    @if($logs->count() > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Backlink
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Projet
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Niveau
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('backlinks.show', $log->backlink) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                        {{ $log->backlink->source_domain }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.show', $log->backlink->project) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                        {{ $log->backlink->project->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->level_class }}">
                                        {{ ucfirst($log->level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log->message }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-white shadow rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun journal trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Aucun log ne correspond à vos critères de recherche.</p>
        </div>
    @endif
</div>
@endsection
