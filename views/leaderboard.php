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
    <title>Classement - DungeonXplorer</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .leaderboard-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .leaderboard-title {
            text-align: center;
            color: #fff;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .leaderboard-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .leaderboard-table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #667eea;
        }

        .leaderboard-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .leaderboard-table tbody tr:hover {
            background-color: #f5f5f5;
            transition: background-color 0.3s ease;
        }

        .rank-badge {
            display: inline-block;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            color: white;
            font-size: 1.1em;
        }

        .rank-1 {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
        }

        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
            color: #333;
        }

        .rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #daa520 100%);
        }

        .rank-other {
            background: #667eea;
        }

        .hero-name {
            font-weight: bold;
            color: #333;
        }

        .class-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            color: #fff;
            background: #333; /* default dark background for contrast */
            box-shadow: inset 0 -2px 0 rgba(0,0,0,0.15);
        }

        .class-warrior {
            background: #e74c3c;
        }

        .class-mage {
            background: #3498db;
        }

        .class-rogue {
            background: #2ecc71;
        }

        .class-paladin {
            background: #f39c12;
        }

        /* default class for unknown or custom classes */
        .class-inconnu, .class-default {
            background: #6b7280;
            color: #fff;
        }

        .level-stat {
            color: #667eea;
            font-weight: bold;
            font-size: 1.1em;
        }

        .xp-stat {
            color: #16a085;
        }

        .chapters-stat {
            color: #8e44ad;
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

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <?php require 'layouts/headerConnecter.php'; ?>

    <div class="leaderboard-container">
        <h1 class="leaderboard-title">⚔️ Classement des Héros</h1>

        <?php if (empty($leaderboard)): ?>
            <div class="empty-message">
                <p>Aucun héros n'a encore été créé. Soyez le premier!</p>
            </div>
        <?php else: ?>
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Héros</th>
                        <th>Joueur</th>
                        <th>Niveau</th>
                        <th>Expérience</th>
                        <th>Chapitres Complétés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $hero): ?>
                        <?php
                            $rank = $index + 1;
                            $rankClass = 'rank-' . $rank;
                            if ($rank > 3) {
                                $rankClass = 'rank-other';
                            }
                        ?>
                        <tr>
                            <td>
                                <span class="rank-badge <?php echo $rankClass; ?>">
                                    <?php 
                                        if ($rank == 1) echo '🥇';
                                        elseif ($rank == 2) echo '🥈';
                                        elseif ($rank == 3) echo '🥉';
                                        else echo $rank;
                                    ?>
                                </span>
                            </td>
                            <td class="hero-name"><?php echo htmlspecialchars($hero['name']); ?></td>
                            <td><?php echo htmlspecialchars($hero['username'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="class-badge class-<?php echo strtolower(str_replace(' ', '-', $hero['class_name'])); ?>">
                                    <?php echo htmlspecialchars($hero['class_name']); ?>
                                </span>
                            </td>
                            <td><span class="level-stat">Lvl <?php echo $hero['current_level']; ?></span></td>
                            <td><span class="xp-stat"><?php echo number_format($hero['xp']); ?> XP</span></td>
                            <td><span class="chapters-stat"><?php echo $hero['chapters_completed']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="/profil" class="back-button">← Retour au Profil</a>
    </div>

    <?php require 'layouts/footer.php'; ?>
</body>
</html>
