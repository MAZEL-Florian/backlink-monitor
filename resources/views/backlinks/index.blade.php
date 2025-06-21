@extends('layouts.app')

@section('title', 'Backlinks')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Backlinks</h1>
            <p class="mt-2 text-gray-600">Surveillez tous vos backlinks</p>
        </div>
        <a href="{{ route('backlinks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Ajouter Backlink
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous</option>
                    <option value="dofollow" {{ request('type') == 'dofollow' ? 'selected' : '' }}>Dofollow</option>
                    <option value="nofollow" {{ request('type') == 'nofollow' ? 'selected' : '' }}>Nofollow</option>
                </select>
            </div>

            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700">Domaine</label>
                <input type="text" 
                       name="domain" 
                       id="domain" 
                       value="{{ request('domain') }}"
                       placeholder="example.com"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-4 flex justify-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Filtrer
                </button>
                <a href="{{ route('backlinks.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Backlinks Table -->
    @if($backlinks->count() > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Source
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Projet
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ancre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dernière Vérif.
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($backlinks as $backlink)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $backlink->source_domain }}</p>
                                        <p class="text-sm text-gray-500 truncate max-w-xs">{{ $backlink->source_url }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.show', $backlink->project) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                        {{ $backlink->project->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $backlink->anchor_text ?: 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $backlink->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        @if($backlink->status_code)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->status_badge_class }}">
                                                {{ $backlink->status_code }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->is_dofollow ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $backlink->is_dofollow ? 'Dofollow' : 'Nofollow' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $backlink->last_checked_at ? $backlink->last_checked_at : 'Jamais' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('backlinks.show', $backlink) }}" class="text-blue-600 hover:text-blue-900">
                                            Voir
                                        </a>
                                        <form action="{{ route('backlinks.check', $backlink) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                Vérifier
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $backlinks->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun backlink</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par ajouter des backlinks à surveiller.</p>
            <div class="mt-6">
                <a href="{{ route('backlinks.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Ajouter Backlink
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
