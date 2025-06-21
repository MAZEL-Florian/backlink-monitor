@extends('layouts.app')

@section('title', 'Créer un Projet')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Créer un Nouveau Projet</h1>
        <p class="mt-2 text-gray-600">Ajoutez un site web à surveiller</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du Projet *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
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
                       value="{{ old('domain') }}"
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
                          placeholder="Description optionnelle du projet...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between pt-4">
                <a href="{{ route('projects.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Créer le Projet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
