<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport de Vérification Backlinks</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .summary-item { display: inline-block; margin: 10px 20px; text-align: center; }
        .summary-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .summary-label { font-size: 0.9em; color: #666; }
        .backlink-list { margin-bottom: 20px; }
        .backlink-item { background: white; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 10px; }
        .backlink-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .project-name { font-weight: bold; color: #007bff; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-changed { background: #fff3cd; color: #856404; }
        .backlink-details { font-size: 0.9em; color: #666; }
        .error-message { color: #dc3545; font-style: italic; margin-top: 5px; }
        .footer { text-align: center; color: #666; font-size: 0.9em; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Rapport de Vérification Backlinks</h1>
        <p>Résultats de la vérification effectuée le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-number">{{ count($results) }}</div>
            <div class="summary-label">Total vérifié</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ collect($results)->where('is_active', true)->count() }}</div>
            <div class="summary-label">Actifs</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ collect($results)->where('is_active', false)->count() }}</div>
            <div class="summary-label">Inactifs</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ collect($results)->where('status_changed', true)->count() }}</div>
            <div class="summary-label">Changements</div>
        </div>
    </div>

    <div class="backlink-list">
        <h3>Détails des Vérifications</h3>
        
        @foreach($results as $result)
            <div class="backlink-item">
                <div class="backlink-header">
                    <span class="project-name">{{ $result['project_name'] }}</span>
                    <div>
                        @if($result['status_changed'])
                            <span class="status-badge status-changed">Changement</span>
                        @endif
                        <span class="status-badge {{ $result['is_active'] ? 'status-active' : 'status-inactive' }}">
                            {{ $result['is_active'] ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
                
                <div class="backlink-details">
                    <div><strong>Source:</strong> {{ $result['source_domain'] }}</div>
                    @if($result['anchor_text'])
                        <div><strong>Ancrage:</strong> {{ $result['anchor_text'] }}</div>
                    @endif
                    @if($result['status_code'])
                        <div><strong>Code HTTP:</strong> {{ $result['status_code'] }}</div>
                    @endif
                    @if($result['response_time'])
                        <div><strong>Temps de réponse:</strong> {{ $result['response_time'] }}ms</div>
                    @endif
                    
                    @if($result['status_changed'])
                        <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px;">
                            <strong>Changement de statut:</strong> 
                            {{ $result['was_active'] ? 'Actif' : 'Inactif' }} 
                            → 
                            {{ $result['is_active'] ? 'Actif' : 'Inactif' }}
                        </div>
                    @endif
                    
                    @if($result['error_message'])
                        <div class="error-message">
                            <strong>Erreur:</strong> {{ $result['error_message'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>
            <a href="{{ route('backlinks.index') }}" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                Voir tous les Backlinks
            </a>
        </p>
        <p>
            Cette notification a été envoyée automatiquement par BacklinkMonitor.<br>
            Pour modifier vos préférences, rendez-vous dans votre <a href="{{ route('profile.edit') }}">profil</a>.
        </p>
    </div>
</body>
</html>