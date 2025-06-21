<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport Backlinks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .section {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        .section-content {
            padding: 20px;
        }
        .backlink-item {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .backlink-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .backlink-title {
            font-weight: bold;
            color: #495057;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-error { background: #f5c6cb; color: #721c24; }
        .backlink-details {
            font-size: 0.9em;
            color: #666;
        }
        .backlink-details div {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9em;
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
        .no-issues {
            text-align: center;
            padding: 40px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔗 Rapport de Surveillance des Backlinks</h1>
        <p>Bonjour {{ $user->name }},</p>
        <p>Voici votre rapport de surveillance des backlinks du {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-number">{{ $reportData['stats']['active_backlinks'] }}</div>
            <div class="stat-label">Backlinks Actifs</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-number">{{ $reportData['stats']['inactive_backlinks'] }}</div>
            <div class="stat-label">Backlinks Inactifs</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-number">{{ $reportData['stats']['error_backlinks'] }}</div>
            <div class="stat-label">Erreurs de Vérification</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $reportData['stats']['total_backlinks'] }}</div>
            <div class="stat-label">Total Backlinks</div>
        </div>
    </div>

    @if($reportData['inactive_backlinks']->count() > 0 || $reportData['error_backlinks']->count() > 0)
        @if($reportData['inactive_backlinks']->count() > 0)
            <div class="section">
                <div class="section-header">
                    ❌ Backlinks Inactifs ({{ $reportData['inactive_backlinks']->count() }})
                </div>
                <div class="section-content">
                    @foreach($reportData['inactive_backlinks'] as $backlink)
                        <div class="backlink-item">
                            <div class="backlink-header">
                                <div class="backlink-title">{{ $backlink->project->name }}</div>
                                <span class="status-badge status-inactive">Inactif</span>
                            </div>
                            <div class="backlink-details">
                                <div><strong>Source:</strong> {{ $backlink->source_url }}</div>
                                <div><strong>Cible:</strong> {{ $backlink->target_url }}</div>
                                <div><strong>Domaine:</strong> {{ $backlink->source_domain }}</div>
                                @if($backlink->anchor_text)
                                    <div><strong>Ancre:</strong> {{ $backlink->anchor_text }}</div>
                                @endif
                                @if($backlink->status_code)
                                    <div><strong>Code HTTP:</strong> {{ $backlink->status_code }}</div>
                                @endif
                                <div><strong>Dernière vérification:</strong> {{ $backlink->last_checked_at ? $backlink->last_checked_at : 'Jamais' }}</div>
                                @if($backlink->latestCheck && $backlink->latestCheck->error_message)
                                    <div><strong>Erreur:</strong> {{ $backlink->latestCheck->error_message }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($reportData['error_backlinks']->count() > 0)
            <div class="section">
                <div class="section-header">
                    💥 Erreurs de Vérification ({{ $reportData['error_backlinks']->count() }})
                </div>
                <div class="section-content">
                    @foreach($reportData['error_backlinks'] as $backlink)
                        <div class="backlink-item">
                            <div class="backlink-header">
                                <div class="backlink-title">{{ $backlink->project->name }}</div>
                                <span class="status-badge status-error">Erreur</span>
                            </div>
                            <div class="backlink-details">
                                <div><strong>Source:</strong> {{ $backlink->source_url }}</div>
                                <div><strong>Cible:</strong> {{ $backlink->target_url }}</div>
                                <div><strong>Domaine:</strong> {{ $backlink->source_domain }}</div>
                                @if($backlink->latestCheck)
                                    <div><strong>Code HTTP:</strong> {{ $backlink->latestCheck->status_code ?? 'N/A' }}</div>
                                    <div><strong>Erreur:</strong> {{ $backlink->latestCheck->error_message }}</div>
                                    <div><strong>Dernière tentative:</strong> {{ $backlink->latestCheck->checked_at }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="section">
            <div class="section-header">
                💡 Actions Recommandées
            </div>
            <div class="section-content">
                <ul>
                    @if($reportData['inactive_backlinks']->count() > 0)
                        <li>Vérifiez si les liens inactifs ont été supprimés ou modifiés sur les pages sources</li>
                        <li>Contactez les propriétaires des sites pour restaurer les liens manquants</li>
                    @endif
                    @if($reportData['error_backlinks']->count() > 0)
                        <li>Vérifiez si les URLs sources sont toujours accessibles</li>
                        <li>Contrôlez si les sites ne bloquent pas les robots de vérification</li>
                    @endif
                    <li>Consultez votre tableau de bord pour plus de détails</li>
                </ul>
            </div>
        </div>
    @else
        <div class="no-issues">
            <h2>🎉 Excellente nouvelle !</h2>
            <p>Tous vos backlinks fonctionnent correctement.</p>
            <p>Aucun problème détecté lors de la dernière vérification.</p>
        </div>
    @endif

    @if($reportData['project_summary']->count() > 0)
        <div class="section">
            <div class="section-header">
                📊 Résumé par Projet
            </div>
            <div class="section-content">
                @foreach($reportData['project_summary'] as $project)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee;">
                        <div>
                            <strong>{{ $project->name }}</strong>
                            <br>
                            <small style="color: #666;">{{ $project->domain }}</small>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #28a745;">{{ $project->active_backlinks_count }} actifs</span>
                            /
                            <span style="color: #dc3545;">{{ $project->inactive_backlinks_count }} inactifs</span>
                            <br>
                            <small style="color: #666;">{{ $project->backlinks_count }} total</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="footer">
        <p>
            <a href="{{ route('dashboard') }}" class="btn">Voir le Tableau de Bord</a>
            <a href="{{ route('backlinks.index') }}" class="btn">Gérer les Backlinks</a>
        </p>
        <p>
            Ce rapport a été généré automatiquement par BacklinkMonitor.<br>
            Pour modifier vos préférences de notification, rendez-vous dans votre <a href="{{ route('profile.edit') }}">profil</a>.
        </p>
    </div>
</body>
</html>
