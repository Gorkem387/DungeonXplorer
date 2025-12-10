<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

if (!isset($_SESSION['username'])) {
    header("Location: /login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progression - DungeonXplorer</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .progression-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .progression-title {
            text-align: center;
            color: #333;
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .timeline-item {
            margin-bottom: 30px;
            position: relative;
        }

        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: 0;
            margin-right: 52%;
            text-align: right;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-left: 52%;
            margin-right: 0;
            text-align: left;
        }

        .timeline-marker {
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background: white;
            border: 4px solid #667eea;
            border-radius: 50%;
            z-index: 1;
        }

        .timeline-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .timeline-item:nth-child(even) .timeline-content {
            border-left: none;
            border-right: 4px solid #667eea;
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .level-up-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .level-info {
            font-size: 1.2em;
            color: #333;
            margin: 10px 0;
        }

        .old-level {
            color: #e74c3c;
        }

        .new-level {
            color: #2ecc71;
        }

        .timeline-date {
            color: #999;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            background: #f5f5f5;
            border-radius: 8px;
            color: #666;
            font-size: 1.2em;
        }

        .back-button {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: #764ba2;
        }

        @media (max-width: 768px) {
            .timeline::before {
                left: 20px;
            }

            .timeline-marker {
                left: 20px;
            }

            .timeline-item:nth-child(odd) .timeline-content,
            .timeline-item:nth-child(even) .timeline-content {
                margin-left: 60px;
                margin-right: 0;
                text-align: left;
            }

            .timeline-item:nth-child(even) .timeline-content {
                border-left: 4px solid #667eea;
                border-right: none;
            }
        }
    </style>
</head>
<body>
    <?php require 'layouts/headerConnecter.php'; ?>

    <div class="progression-container">
        <h1 class="progression-title">📈 Chronologie de Progression</h1>

        <?php if (empty($timeline)): ?>
            <div class="empty-message">
                <p>Aucune montée de niveau enregistrée. Progressez dans le jeu pour voir votre historique!</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($timeline as $entry): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="level-up-badge">⭐ MONTÉE DE NIVEAU</div>
                            <div class="level-info">
                                Niveau <span class="old-level"><?php echo $entry['old_level']; ?></span>
                                <span style="color: #999;">→</span>
                                Niveau <span class="new-level"><?php echo $entry['new_level']; ?></span>
                            </div>
                            <div class="timeline-date">
                                📅 <?php echo date('d/m/Y à H:i', strtotime($entry['level_up_date'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="/profil" class="back-button">← Retour au Profil</a>
    </div>

    <?php require 'layouts/footer.php'; ?>
</body>
</html>
