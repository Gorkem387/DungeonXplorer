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
            <form method="GET" action="/chapter/1">
                <input type="hidden" name="hero_id" value="<?= $hero['id'] ?>">
                <button type="submit" class="btn-custom">
                    <i class="fa-solid fa-dragon"></i> Partir à l'aventure
                </button>
            </form>
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
    </div>

</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>