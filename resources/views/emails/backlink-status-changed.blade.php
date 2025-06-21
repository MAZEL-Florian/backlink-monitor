<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Changement de Statut Backlink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: {{ $isActive ? '#d4edda' : '#f8d7da' }};
            color: {{ $isActive ? '#155724' : '#721c24' }};
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            color: #6c757d;
            word-break: break-all;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            @if($isActive)
                ✅ Backlink Réactivé
            @else
                ❌ Backlink Désactivé
            @endif
        </h1>
        <p>
            @if($isActive)
                Bonne nouvelle ! Un de vos backlinks est de nouveau actif.
            @else
                Attention ! Un de vos backlinks n'est plus actif.
            @endif
        </p>
    </div>

    <div class="content">
        <h3>Détails du Backlink</h3>
        
        <div class="detail-row">
            <span class="label">Projet:</span>
            <span class="value">{{ $backlink->project->name }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">Domaine Source:</span>
            <span class="value">{{ $backlink->source_domain }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">URL Source:</span>
            <span class="value">{{ $backlink->source_url }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">URL Cible:</span>
            <span class="value">{{ $backlink->target_url }}</span>
        </div>
        
        @if($backlink->anchor_text)
            <div class="detail-row">
                <span class="label">Texte d'Ancrage:</span>
                <span class="value">{{ $backlink->anchor_text }}</span>
            </div>
        @endif
        
        <div class="detail-row">
            <span class="label">Statut Précédent:</span>
            <span class="value">
                <span class="status-badge {{ $wasActive ? 'status-active' : 'status-inactive' }}">
                    {{ $wasActive ? 'Actif' : 'Inactif' }}
                </span>
            </span>
        </div>
        
        <div class="detail-row">
            <span class="label">Statut Actuel:</span>
            <span class="value">
                <span class="status-badge {{ $isActive ? 'status-active' : 'status-inactive' }}">
                    {{ $isActive ? 'Actif' : 'Inactif' }}
                </span>
            </span>
        </div>
        
        @if($backlink->status_code)
            <div class="detail-row">
                <span class="label">Code HTTP:</span>
                <span class="value">{{ $backlink->status_code }}</span>
            </div>
        @endif
        
        <div class="detail-row">
            <span class="label">Dernière Vérification:</span>
            <span class="value">{{ $backlink->last_checked_at->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    @if(!$isActive)
        <div class="content">
            <h3>Actions Recommandées</h3>
            <ul>
                <li>Vérifiez si la page source est toujours accessible</li>
                <li>Contrôlez si le lien a été supprimé ou modifié</li>
                <li>Contactez le propriétaire du site si nécessaire</li>
                <li>Vérifiez votre tableau de bord pour plus de détails</li>
            </ul>
        </div>
    @endif

    <div class="footer">
        <p>
            <a href="{{ route('backlinks.show', $backlink) }}" class="btn">Voir les Détails</a>
            <a href="{{ route('dashboard') }}" class="btn">Tableau de Bord</a>
        </p>
        <p>
            Cette notification a été envoyée automatiquement par BacklinkMonitor.<br>
            Pour modifier vos préférences, rendez-vous dans votre <a href="{{ route('profile.edit') }}">profil</a>.
        </p>
    </div>
</body>
</html>
