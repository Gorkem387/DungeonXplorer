<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['username'])){
        header("Location: /");  
    }
    require_once 'views/layouts/headerConnecter.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
    <script defer src="/public/js/script.js"></script>
</head>
<body class="profil">
    <h1>Mon profil</h1>

    <h2>Liste de mes personnages</h2>

    <div class="card-container">
    <?php foreach ($heros as $hero): ?>
    <div class="perso-card">
        <img src="<?= htmlspecialchars($hero['image']) ?>" alt="Image du hero" class="perso-card-img">
        <div class="perso-card-contenu">
            <div class="perso-card-nom"><?= htmlspecialchars($hero['name']) ?></div>
            <div style="display:flex;gap:10px;flex-direction:column;">
                <form method="POST" action="chapitre/start" style="margin:0;">
                    <input type="hidden" name="hero_id" value="<?= $hero['id'] ?>">
                    <button type="submit" class="btn-custom" style="width:100%;margin:0;">
                        <i class="fa-solid fa-dragon"></i> Partir à l'aventure
                    </button>
                </form>
                <a href="/timeline/<?= $hero['id'] ?>" class="btn-custom" style="text-align:center;text-decoration:none;display:block;margin:0;">
                    <i class="fa-solid fa-chart-line"></i> Ma Progression
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>


    <div id="info" class="info-perso">
        <div class="info-perso-contenu">
            <button id="btnFermerInfo" class="info-perso-fermer">&times;</button>
            <h3 class="info-perso-titre">Détails du hero</h3>
                <div class="cadre-info">
                    
                </div>
        </div>
    </div>
    
    <div class="btnAddChar">
    <a href="/hero" class="btn-custom">
        <i class="fa-solid fa-user-plus"></i> Créer un hero
    </a>
    <a href="/leaderboard" class="btn-custom">
        <i class="fa-solid fa-trophy"></i> Classement des Héros
    </a>
    <button id ="btnRemoveChar" class="btn-custom"><i class="fa-solid fa-user-minus"></i> Supprimer personnage</button>
    </div>


    
</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>