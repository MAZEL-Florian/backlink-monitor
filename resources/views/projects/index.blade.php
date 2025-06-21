@extends('layouts.app')

@section('title', 'Projets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Projets</h1>
            <p class="mt-2 text-gray-600">Gérez vos sites web et leurs backlinks</p>
        </div>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Nouveau Projet
        </a>
    </div>

    <!-- Projects Grid -->
    @if($projects->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($projects as $project)
                <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                    {{ $project->name }}
                                </a>
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $project->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $project->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4">{{ $project->domain }}</p>
                        
                        @if($project->description)
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $project->description }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ $project->backlinks_count }}</p>
                                <p class="text-xs text-gray-500">Total Backlinks</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">{{ $project->active_backlinks_count }}</p>
                                <p class="text-xs text-gray-500">Actifs</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('projects.show', $project) }}" class="flex-1 bg-blue-50 text-blue-700 text-center py-2 px-3 rounded-md text-sm hover:bg-blue-100">
                                Voir Détails
                            </a>
                            <a href="{{ route('backlinks.create', ['project_id' => $project->id]) }}" class="flex-1 bg-green-50 text-green-700 text-center py-2 px-3 rounded-md text-sm hover:bg-green-100">
                                Ajouter Backlink
                            </a>
                        </div>

                        @if($project->last_checked_at)
                            <p class="text-xs text-gray-400 mt-3">
                                Dernière vérification: {{ $project->last_checked_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        {{ $projects->links() }}
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun projet</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier projet pour surveiller vos backlinks.</p>
            <div class="mt-6">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Nouveau Projet
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
