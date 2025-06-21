<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport de Vérification Automatique</title>
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
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .summary-card.success { border-left-color: #28a745; }
        .summary-card.warning { border-left-color: #ffc107; }
        .summary-card.danger { border-left-color: #dc3545; }
        .summary-number {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-label {
            color: #666;
            font-size: 0.9em;
        }
        .section {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .issue-item {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .issue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .issue-title {
            font-weight: bold;
            color: #495057;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-reactivated { background: #d4edda; color: #155724; }
        .status-deactivated { background: #f8d7da; color: #721c24; }
        .status-error { background: #f5c6cb; color: #721c24; }
        .issue-details {
            font-size: 0.9em;
            color: #666;
        }
        .issue-details div {
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .domain-cell {
            max-width: 200px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Rapport de Vérification Automatique</h1>
        <p>Bonjour {{ $user->name }},</p>
        <p>Voici le résultat de la vérification automatique du {{ $results['check_time']->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card success">
            <div class="summary-number">{{ $results['active_count'] }}</div>
            <div class="summary-label">Backlinks Actifs</div>
        </div>
        <div class="summary-card danger">
            <div class="summary-number">{{ $results['inactive_count'] }}</div>
            <div class="summary-label">Backlinks Inactifs</div>
        </div>
        <div class="summary-card warning">
            <div class="summary-number">{{ count($results['status_changes']) }}</div>
            <div class="summary-label">Changements de Statut</div>
        </div>
        <div class="summary-card danger">
            <div class="summary-number">{{ count($results['errors']) }}</div>
            <div class="summary-label">Erreurs de Vérification</div>
        </div>
        <div class="summary-card">
            <div class="summary-number">{{ $results['total_checked'] }}</div>
            <div class="summary-label">Total Vérifiés</div>
        </div>
    </div>

    @if(count($results['status_changes']) > 0 || count($results['errors']) > 0)
        @if(count($results['status_changes']) > 0)
            <div class="section">
                <div class="section-header">
                    🔄 Changements de Statut ({{ count($results['status_changes']) }})
                </div>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Domaine Source</th>
                                <th>Ancre</th>
                                <th>Statut</th>
                                <th>Code HTTP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['status_changes'] as $change)
                                <tr>
                                    <td><strong>{{ $change['project_name'] }}</strong></td>
                                    <td class="domain-cell">{{ $change['source_domain'] }}</td>
                                    <td>{{ $change['anchor_text'] ?: 'N/A' }}</td>
                                    <td>
                                        @if($change['is_active'])
                                            <span class="status-badge status-reactivated">✅ Réactivé</span>
                                        @else
                                            <span class="status-badge status-deactivated">❌ Désactivé</span>
                                        @endif
                                    </td>
                                    <td>{{ $change['status_code'] ?: 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(count($results['errors']) > 0)
            <div class="section">
                <div class="section-header">
                    💥 Erreurs de Vérification ({{ count($results['errors']) }})
                </div>
                <div class="section-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Domaine Source</th>
                                <th>Erreur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['errors'] as $error)
                                <tr>
                                    <td><strong>{{ $error['project_name'] }}</strong></td>
                                    <td class="domain-cell">{{ $error['source_domain'] }}</td>
                                    <td>{{ $error['error_message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="section">
            <div class="section-header">
                💡 Actions Recommandées
            </div>
            <div class="section-content">
                <ul>
                    @if(count($results['status_changes']) > 0)
                        <li>Vérifiez les backlinks qui ont changé de statut</li>
                        <li>Contactez les propriétaires des sites pour les liens désactivés</li>
                    @endif
                    @if(count($results['errors']) > 0)
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
            <p>Tous vos {{ $results['active_count'] }} backlinks actifs fonctionnent correctement.</p>
            <p>Aucun problème détecté lors de cette vérification automatique.</p>
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
        <p>
            <small>Prochaine vérification automatique dans 1 heure.</small>
        </p>
    </div>
</body>
</html>
