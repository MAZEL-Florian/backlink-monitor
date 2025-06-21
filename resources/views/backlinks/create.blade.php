@extends('layouts.app')

@section('title', 'Ajouter un Backlink')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex items-center space-x-2 mb-2">
            <a href="{{ route('backlinks.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Ajouter un Backlink</h1>
        </div>
        <p class="text-gray-600">Ajoutez un nouveau backlink à surveiller</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('backlinks.store') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">Projet *</label>
                <select name="project_id" 
                        id="project_id" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('project_id') border-red-300 @enderror"
                        required>
                    <option value="">Sélectionnez un projet</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $selectedProject?->id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }} ({{ $project->domain }})
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="source_url" class="block text-sm font-medium text-gray-700">URL Source *</label>
                <input type="url" 
                       name="source_url" 
                       id="source_url" 
                       value="{{ old('source_url') }}"
                       placeholder="https://example.com/page-avec-le-lien"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('source_url') border-red-300 @enderror"
                       required>
                @error('source_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">URL de la page qui contient le lien vers votre site</p>
            </div>

            <div>
                <label for="target_url" class="block text-sm font-medium text-gray-700">URL Cible *</label>
                <input type="url" 
                       name="target_url" 
                       id="target_url" 
                       value="{{ old('target_url') }}"
                       placeholder="https://monsite.com/page-cible"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('target_url') border-red-300 @enderror"
                       required>
                @error('target_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">URL de votre site vers laquelle pointe le backlink</p>
            </div>

            <div>
                <label for="anchor_text" class="block text-sm font-medium text-gray-700">Texte d'Ancrage</label>
                <input type="text" 
                       name="anchor_text" 
                       id="anchor_text" 
                       value="{{ old('anchor_text') }}"
                       placeholder="Texte du lien"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('anchor_text') border-red-300 @enderror">
                @error('anchor_text')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Le texte cliquable du lien (sera détecté automatiquement si laissé vide)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="domain_authority" class="block text-sm font-medium text-gray-700">Domain Authority (DA)</label>
                    <input type="number" 
                           name="domain_authority" 
                           id="domain_authority" 
                           value="{{ old('domain_authority') }}"
                           min="0" 
                           max="100"
                           placeholder="0-100"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('domain_authority') border-red-300 @enderror">
                    @error('domain_authority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="page_authority" class="block text-sm font-medium text-gray-700">Page Authority (PA)</label>
                    <input type="number" 
                           name="page_authority" 
                           id="page_authority" 
                           value="{{ old('page_authority') }}"
                           min="0" 
                           max="100"
                           placeholder="0-100"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('page_authority') border-red-300 @enderror">
                    @error('page_authority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_dofollow" 
                           id="is_dofollow" 
                           value="1"
                           {{ old('is_dofollow', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_dofollow" class="ml-2 block text-sm text-gray-900">
                        Lien Dofollow
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Décochez si le lien est en nofollow (sera vérifié automatiquement)</p>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-300 @enderror"
                          placeholder="Notes optionnelles sur ce backlink...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-4">
                <a href="{{ route('backlinks.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Ajouter le Backlink
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-fill target URL based on selected project
document.getElementById('project_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const projectDomain = selectedOption.text.match(/$$(.*?)$$/)[1];
        document.getElementById('target_url').value = projectDomain;
    }
});
</script>
@endsection
