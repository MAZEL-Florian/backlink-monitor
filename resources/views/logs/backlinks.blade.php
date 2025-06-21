@extends('layouts.app')

@section('title', 'Logs des Backlinks')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Logs des Vérifications</h1>
            <p class="mt-2 text-gray-600">Consultez les logs détaillés des vérifications de backlinks</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="location.reload()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                Actualiser
            </button>
            <form action="{{ route('logs.clear') }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir effacer tous les logs ?')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L9 10.586l-2.293 2.293a1 1 0 101.414 1.414L10 11.414l2.293 2.293a1 1 0 001.414-1.414L11.414 10l2.293-2.293z" clip-rule="evenodd"/>
                    </svg>
                    Effacer les Logs
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Logs en Temps Réel</h3>
            <p class="text-sm text-gray-500">Les 100 dernières entrées de logs liées aux backlinks</p>
        </div>
        
        @if(count($logs) > 0)
            <div class="max-h-96 overflow-y-auto">
                <div class="p-4 font-mono text-sm">
                    @foreach($logs as $log)
                        <div class="mb-2 p-2 rounded {{ 
                            strpos($log, 'ERROR') !== false ? 'bg-red-50 text-red-800' : 
                            (strpos($log, 'WARNING') !== false ? 'bg-yellow-50 text-yellow-800' : 
                            (strpos($log, 'INFO') !== false ? 'bg-blue-50 text-blue-800' : 'bg-gray-50 text-gray-800'))
                        }}">
                            <div class="break-all">{{ $log }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun log trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">Aucun log de vérification de backlink n'a été trouvé.</p>
                <div class="mt-6">
                    <a href="{{ route('backlinks.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Aller aux Backlinks
                    </a>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Comment lire les logs</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong class="text-red-600">ERROR</strong> : Erreurs critiques lors des vérifications</li>
                        <li><strong class="text-yellow-600">WARNING</strong> : Avertissements (liens non trouvés, etc.)</li>
                        <li><strong class="text-blue-600">INFO</strong> : Informations générales sur les vérifications</li>
                        <li><strong class="text-gray-600">DEBUG</strong> : Détails techniques pour le débogage</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endsection
