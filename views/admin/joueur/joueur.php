<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['username'])){
        header("Location: /");  
    }
    require_once 'views/layouts/headerConnecter.php';
    require_once 'models/Database.php';
    $bdd = Database::getConnection();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/chapter/joueur.css">    
    <title>Liste des joueurs</title>
</head>
<body>
    <div class="joueur">
        <div class="container">
            <div class="page-header">
                <i class="fas fa-users-cog header-icon"></i>
                <h1>Liste des joueurs</h1>
            </div>

            <div class="players-grid">
                <?php
                $rep = $bdd->query("SELECT * FROM utilisateur");
                while ($row = $rep->fetch()) {
                    if ($row['perm_user'] == 1) {
                        ?>
                        <div class="player-card admin">
                            <div class="player-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="player-info">
                                <h3>Admin #<?= $row['id'] ?>: <?= htmlspecialchars($row['name']) ?></h3>
                                <span class="badge">Administrateur</span>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="player-card">
                            <div class="player-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="player-info">
                                <h3>Joueur #<?= $row['id'] ?>: <?= htmlspecialchars($row['name']) ?></h3>
                            </div>
                            <form method="post" action="/admin/delete"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                            </form>
                        </div>
                        <?php
                    }
                }
                $rep->closeCursor();
                ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>
