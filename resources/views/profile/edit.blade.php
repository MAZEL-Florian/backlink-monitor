@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Profil</h1>
        <p class="mt-2 text-gray-600">Gérez vos informations personnelles</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $user->email) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="email_notifications" 
                           id="email_notifications" 
                           value="1"
                           {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="email_notifications" class="ml-2 block text-sm text-gray-900">
                        Recevoir les notifications par email
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Vous recevrez des alertes lorsque vos backlinks changent de statut</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
