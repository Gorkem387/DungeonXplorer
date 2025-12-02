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

    <?php foreach ($personnages as $personnage): ?>
    <div class="perso-card">
        <img src="/public/img/<?= htmlspecialchars($personnage['image']) ?>" alt="Image du personnage" class="perso-card-img">
        <div class="perso-card-contenu">
            <div class="perso-card-nom"><?= htmlspecialchars($personnage['name']) ?></div>
            <button id="btnVoirDetails" class="btn-custom" onclick="openInfo(<?= $personnage['id'] ?>)">Voir détails</button>
        </div>
    </div>
    <?php endforeach; ?>

    <div id="info" class="info-perso">
        <div class="info-perso-contenu">
            <button id="btnFermerInfo" class="info-perso-fermer">&times;</button>
            <h3 class="info-perso-titre">Détails du personnage</h3>
                <div class="cadre-info">
                    
                </div>
        </div>
    </div>

</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>