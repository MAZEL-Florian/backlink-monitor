@extends('layouts.app')

@section('title', 'Projets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Projets</h1>
            <p class="mt-2 text-gray-600">Gérez vos sites web et leurs backlinks</p>
        </div>
        <div class="flex space-x-3">
            <button id="bulk-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 hidden" onclick="confirmBulkDelete()">
                <svg class="w-5 h-5 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
                Supprimer (<span id="selected-count">0</span>)
            </button>
            <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center" data-ui-element="true">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Nouveau Projet
            </a>
        </div>
    </div>

    @if($projects->count() > 0)
        <form id="bulk-delete-form" action="{{ route('projects.bulk-delete') }}" method="POST">
            @csrf
            @method('DELETE')
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($projects as $project)
                    <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow relative" data-ui-element="true">
                        <div class="absolute top-3 left-3 z-10">
                            <input type="checkbox" 
                                   name="project_ids[]" 
                                   value="{{ $project->id }}" 
                                   class="project-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   onchange="updateBulkDeleteButton()">
                        </div>
                        
                        <div class="p-6 pt-12">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                        {{ $project->name }}
                                    </a>
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $project->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $project->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-4">{{ $project->domain }}</p>
                            
                            @if($project->description)
                                <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $project->description }}</p>
                            @endif

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">{{ $project->backlinks_count }}</p>
                                    <p class="text-xs text-gray-500">Total Backlinks</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-emerald-600">{{ $project->active_backlinks_count }}</p>
                                    <p class="text-xs text-gray-500">Actifs</p>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <a href="{{ route('projects.show', $project) }}" class="flex-1 bg-blue-100 text-blue-700 text-center py-2 px-3 rounded-md text-sm hover:bg-blue-200 transition-colors" data-ui-element="true">
                                    Voir Détails
                                </a>
                                <a href="{{ route('backlinks.create', ['project_id' => $project->id]) }}" class="flex-1 bg-emerald-100 text-emerald-700 text-center py-2 px-3 rounded-md text-sm hover:bg-emerald-200 transition-colors" data-ui-element="true">
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
        </form>

        {{ $projects->links() }}
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun projet</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier projet pour surveiller vos backlinks.</p>
            <div class="mt-6">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700" data-ui-element="true">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Nouveau Projet
                </a>
            </div>
        </div>
    @endif
</div>

<script>
function updateBulkDeleteButton() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectedCount = document.getElementById('selected-count');
    
    if (checkboxes.length > 0) {
        bulkDeleteBtn.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkDeleteBtn.classList.add('hidden');
    }
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const count = checkboxes.length;
    
    if (count === 0) return;
    
    const message = count === 1 
        ? 'Êtes-vous sûr de vouloir supprimer ce projet ? Cette action supprimera également tous les backlinks associés et ne peut pas être annulée.'
        : `Êtes-vous sûr de vouloir supprimer ces ${count} projets ? Cette action supprimera également tous les backlinks associés et ne peut pas être annulée.`;
    
    if (confirm(message)) {
        document.getElementById('bulk-delete-form').submit();
    }
}


</script>
@endsection
