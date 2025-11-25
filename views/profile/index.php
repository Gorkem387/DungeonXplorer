<?php require_once 'views/layouts/header.php'; ?>

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
    <div class="perso-card">
        <img src="public/img/Berserker.jpg" alt="Image du personnage" class="perso-card-img">
        <div class="perso-card-contenu">
            <div class="perso-card-nom">Nom personnage</div>
            <button id="btnVoirDetails" class="perso-card-button">Voir détails</button>
        </div>
    </div>

    <div id="info" class="info-perso">
        <div class="info-perso-contenu">
            <button id="btnFermerInfo" class="info-perso-fermer">&times;</button>
            <h3 class="info-perso-titre">Détails du personnage</h3>
                <div class="cadre-info">
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Nom :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Classe :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Progression :</span>
                        Chapitre ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Nombre de PV :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Initiative :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Force :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Mana :</span>
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Equipements :</span>
                        ...
                    </div>
                </div>
        </div>
    </div>

</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>