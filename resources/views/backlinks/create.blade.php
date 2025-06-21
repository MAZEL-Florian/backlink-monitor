@extends('layouts.app')

@section('title', 'Ajouter des Backlinks')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="flex items-center space-x-2 mb-2">
            <a href="{{ route('backlinks.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Ajouter des Backlinks</h1>
        </div>
        <p class="text-gray-600">Ajoutez plusieurs backlinks en une seule fois</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('backlinks.store') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">Projet *</label>
                <select name="project_id" id="project_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('project_id') border-red-300 @enderror"
                    required>
                    <option value="">Sélectionnez un projet</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $selectedProject?->id) == $project->id ?
                        'selected' : '' }}>
                        {{ $project->name }} ({{ $project->domain }})
                    </option>
                    @endforeach
                </select>
                @error('project_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="source_urls" class="block text-sm font-medium text-gray-700">URLs Sources *</label>
                <textarea name="source_urls" id="source_urls" rows="10"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('source_urls') border-red-300 @enderror"
                    placeholder="Insérez une URL par ligne, par exemple :
https://example.com/page-avec-lien-1
https://autre-site.com/article-avec-lien
https://blog.exemple.fr/post-backlink" required>{{ old('source_urls') }}</textarea>
                @error('source_urls')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="mt-2 text-sm text-gray-500">
                    <p><strong>Instructions :</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Insérez une URL par ligne</li>
                        <li>Chaque URL doit être complète (avec http:// ou https://)</li>
                        <li>Les URLs cibles, textes d'ancrage et types de liens seront détectés automatiquement</li>
                        <li>Vous pouvez ajouter autant d'URLs que nécessaire</li>
                    </ul>
                </div>
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm text-blue-800">
                            <span id="url-count">0</span> URL(s) détectée(s)
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (optionnel)</label>
                <textarea name="notes" id="notes" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-300 @enderror"
                    placeholder="Notes communes pour tous ces backlinks...">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Ces notes seront appliquées à tous les backlinks créés</p>
            </div>

            <div class="flex justify-between pt-4">
                <a href="{{ route('backlinks.index') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                    id="submit-btn">
                    Ajouter les Backlinks
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('source_urls');
    const urlCount = document.getElementById('url-count');
    const submitBtn = document.getElementById('submit-btn');
    const projectSelect = document.getElementById('project_id');

    function updateUrlCount() {
        const text = textarea.value.trim();
        if (!text) {
            urlCount.textContent = '0';
            submitBtn.textContent = 'Ajouter les Backlinks';
            return;
        }

        const urls = text.split('\n').filter(line => {
            const trimmed = line.trim();
            return trimmed && (trimmed.startsWith('http://') || trimmed.startsWith('https://'));
        });

        urlCount.textContent = urls.length;
        submitBtn.textContent = urls.length > 1 
            ? `Ajouter ${urls.length} Backlinks` 
            : 'Ajouter le Backlink';
    }

    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const match = selectedOption.text.match(/$$(.*?)$$/);
            if (match) {
                const projectDomain = match[1];
                console.log('Projet sélectionné:', projectDomain);
            }
        }
    });

    textarea.addEventListener('input', updateUrlCount);
    textarea.addEventListener('paste', function() {
        setTimeout(updateUrlCount, 100);
    });

    updateUrlCount();
});
</script>
@endsection