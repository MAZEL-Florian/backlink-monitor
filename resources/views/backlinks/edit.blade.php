@extends('layouts.app')

@section('title', 'Modifier le Backlink')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex items-center space-x-2 mb-2">
            <a href="{{ route('backlinks.show', $backlink) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Modifier le Backlink</h1>
        </div>
        <p class="text-gray-600">Modifiez les informations de ce backlink</p>
    </div>

    <div class="mb-8">
        @include('components.uptime-timeline', ['uptimeData' => $uptimeData])
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('backlinks.update', $backlink) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">Projet *</label>
                <select name="project_id" id="project_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('project_id') border-red-300 @enderror"
                    required disabled>
                    <option value="{{ $backlink->project->id }}" selected>
                        {{ $backlink->project->name }} ({{ $backlink->project->domain }})
                    </option>
                </select>
                <input type="hidden" name="project_id" value="{{ $backlink->project->id }}">
                @error('project_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="source_url" class="block text-sm font-medium text-gray-700">URL Source *</label>
                <textarea name="source_url" id="source_url" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('source_url') border-red-300 @enderror"
                    placeholder="https://example.com/page-avec-le-lien"
                    required>{{ old('source_url', $backlink->source_url) }}</textarea>
                @error('source_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="mt-2 text-sm text-gray-500">
                    <p><strong>Instructions :</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>URL de la page qui contient le lien vers votre site</li>
                        <li>L'URL doit être complète (avec http:// ou https://)</li>
                        <li>L'URL cible et le texte d'ancrage seront détectés automatiquement</li>
                    </ul>
                </div>
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm text-blue-800" id="url-status">
                            URL valide détectée
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (optionnel)</label>
                <textarea name="notes" id="notes" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-300 @enderror"
                    placeholder="Notes pour ce backlink...">{{ old('notes', $backlink->notes) }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Notes personnelles pour ce backlink</p>
            </div>

            <div class="flex justify-between pt-4">
                <div class="flex space-x-3">
                    <a href="{{ route('backlinks.show', $backlink) }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                        id="submit-btn">
                        Mettre à jour le Backlink
                    </button>
                </div>
                <button type="button" onclick="confirmDelete()"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Supprimer
                </button>
            </div>
        </form>

        <form id="delete-form" action="{{ route('backlinks.destroy', $backlink) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('source_url');
    const urlStatus = document.getElementById('url-status');
    const submitBtn = document.getElementById('submit-btn');

    function validateUrl() {
        const url = textarea.value.trim();
        const isValid = url && (url.startsWith('http://') || url.startsWith('https://'));
        
        if (isValid) {
            urlStatus.textContent = 'URL valide détectée';
            urlStatus.parentElement.parentElement.className = 'mt-2 p-3 bg-green-50 border border-green-200 rounded-md';
            urlStatus.parentElement.querySelector('svg').className = 'w-5 h-5 text-green-600 mr-2';
            urlStatus.className = 'text-sm text-green-800';
            submitBtn.textContent = 'Mettre à jour le Backlink';
            submitBtn.disabled = false;
            submitBtn.className = 'bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700';
        } else {
            urlStatus.textContent = 'URL invalide - doit commencer par http:// ou https://';
            urlStatus.parentElement.parentElement.className = 'mt-2 p-3 bg-red-50 border border-red-200 rounded-md';
            urlStatus.parentElement.querySelector('svg').className = 'w-5 h-5 text-red-600 mr-2';
            urlStatus.className = 'text-sm text-red-800';
            submitBtn.textContent = 'URL invalide';
            submitBtn.disabled = true;
            submitBtn.className = 'bg-gray-400 text-white px-4 py-2 rounded-md cursor-not-allowed';
        }
    }

    textarea.addEventListener('input', validateUrl);
    textarea.addEventListener('paste', function() {
        setTimeout(validateUrl, 100);
    });

    validateUrl();
});

function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce backlink ? Cette action ne peut pas être annulée.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection