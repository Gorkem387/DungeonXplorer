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
        <div clas="perso-card-contenu">
            <div class="perso-card-nom">Nom personnage</div>
            <button class="perso-card-button" onclick="ouvrirInfo()">Voir détails</div>
        </div>
    </div>

    <div id="info" class="info-perso">
        <div class="info-perso-contenu">
            <button class="info-perso-fermer" onclick="fermerInfo()">&times;</button>
            <h3 class="info-perso-titre">Détails du personnage</h3>

        </div>
    </div>

</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>