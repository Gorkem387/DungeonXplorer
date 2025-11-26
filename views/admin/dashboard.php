<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['username'])){
        require_once 'views/layouts/headerConnecter.php';
    } else {
        require_once 'views/layouts/header.php';
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/chapter/dashboard.css">    
    <title>Dashboard Admin</title>
</head>
<body>
    <div class="dash">
        <div class="container">
            <div class="page-header">
                <div class="header-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Dashboard Administrateur</h1>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <i class="fas fa-users"></i>
                    <h2>Liste des joueurs</h2>
                    <a href="/admin/joueur">Accéder</a>
                </div>
                <div class="dashboard-card">
                    <i class="fas fa-book"></i>
                    <h2>Modifier les chapitres</h2>
                    <a href="/admin/chapter">Accéder</a>
                </div>
                <div class="dashboard-card">
                    <i class="fas fa-dragon"></i>
                    <h2>Modifier les monstres</h2>
                    <a href="#">Bientôt disponible</a>
                </div>
                <div class="dashboard-card">
                    <i class="fas fa-gem"></i>
                    <h2>Modifier les trésors</h2>
                    <a href="#">Bientôt disponible</a>
                </div>
            </div>

            <div class="play-section">
                <a href="/profile" class="play-btn">
                    <i class="fas fa-play"></i>
                    Jouer au jeu
                </a>
            </div>
        </div>
    </div>
    <?php require_once 'views/layouts/footer.php'; ?>
</body>
</html>
