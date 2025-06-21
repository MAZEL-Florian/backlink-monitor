@extends('layouts.app')

@section('title', 'Détails du Backlink')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center space-x-2 mb-2">
                <a href="{{ route('backlinks.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Détails du Backlink</h1>
            </div>
            <p class="text-gray-600">{{ $backlink->source_domain }}</p>
        </div>
        <div class="flex space-x-3">
            <form action="{{ route('backlinks.check', $backlink) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    Vérifier Maintenant
                </button>
            </form>
            <a href="{{ route('backlinks.edit', $backlink) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <div class="mb-8">
        @include('components.uptime-timeline', ['uptimeData' => $uptimeData])
    </div>

    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informations du Backlink</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Projet</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('projects.show', $backlink->project) }}" class="text-blue-600 hover:text-blue-900">
                            {{ $backlink->project->name }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Statut Actuel</dt>
                    <dd class="mt-1">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $backlink->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            @if($backlink->status_code)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->status_badge_class }}">
                                    HTTP {{ $backlink->status_code }}
                                </span>
                            @endif
                        </div>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">URL Source</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ $backlink->source_url }}" target="_blank" class="text-blue-600 hover:text-blue-900 break-all">
                            {{ $backlink->source_url }}
                            <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                            </svg>
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">URL Cible</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ $backlink->target_url }}" target="_blank" class="text-blue-600 hover:text-blue-900 break-all">
                            {{ $backlink->target_url }}
                            <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                            </svg>
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Texte d'Ancrage</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backlink->anchor_text ?: 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type de Lien</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backlink->is_dofollow ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $backlink->is_dofollow ? 'Dofollow' : 'Nofollow' }}
                        </span>
                    </dd>
                </div>
                @if($backlink->domain_authority)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Domain Authority</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $backlink->domain_authority }}/100</dd>
                    </div>
                @endif
                @if($backlink->page_authority)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Page Authority</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $backlink->page_authority }}/100</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Première Découverte</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backlink->first_found_at ? $backlink->first_found_at : 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dernière Vérification</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backlink->last_checked_at ? $backlink->last_checked_at : 'Jamais' }}</dd>
                </div>
                @if($backlink->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $backlink->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Historique des Vérifications</h3>
        </div>
        @if($checks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code HTTP
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Temps de Réponse
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ancre Détectée
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($checks as $check)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $check->checked_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $check->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $check->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($check->status_code)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $check->status_code == 200 ? 'bg-green-100 text-green-800' : ($check->status_code == 404 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $check->status_code }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Erreur</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $check->is_dofollow ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $check->is_dofollow ? 'Dofollow' : 'Nofollow' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $check->response_time ? $check->response_time . 'ms' : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $check->anchor_text ?: 'N/A' }}
                                    @if(isset($check->exact_match))
                                        @if($check->exact_match)
                                            <span class="ml-1 text-xs text-green-600" title="Correspondance exacte avec l'URL cible">
                                                <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="ml-1 text-xs text-yellow-600" title="Correspondance approximative avec l'URL cible">
                                                <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($checks->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $checks->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune vérification</h3>
                <p class="mt-1 text-sm text-gray-500">Ce backlink n'a pas encore été vérifié.</p>
                <div class="mt-6">
                    <form action="{{ route('backlinks.check', $backlink) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Lancer la première vérification
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
