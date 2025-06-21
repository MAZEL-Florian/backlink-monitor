@extends('layouts.app')

@section('title', 'Modifier le Projet')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex items-center space-x-2 mb-2">
            <a href="{{ route('projects.show', $project) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Modifier le Projet</h1>
        </div>
        <p class="text-gray-600">Modifiez les informations de votre projet</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du Projet *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $project->name) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700">Domaine *</label>
                <input type="url" 
                       name="domain" 
                       id="domain" 
                       value="{{ old('domain', $project->domain) }}"
                       placeholder="https://example.com"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('domain') border-red-300 @enderror"
                       required>
                @error('domain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">URL complète du site web (ex: https://monsite.com)</p>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror"
                          placeholder="Description optionnelle du projet...">{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', $project->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Projet actif
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Les projets inactifs ne seront pas vérifiés automatiquement</p>
            </div>

            <div class="flex justify-between pt-4">
                <div class="flex space-x-3">
                    <a href="{{ route('projects.show', $project) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Mettre à jour
                    </button>
                </div>
                <button type="button" 
                        onclick="confirmDelete()" 
                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Supprimer
                </button>
            </div>
        </form>

        <!-- Delete Form (hidden) -->
        <form id="delete-form" action="{{ route('projects.destroy', $project) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce projet ? Cette action supprimera également tous les backlinks associés et ne peut pas être annulée.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
