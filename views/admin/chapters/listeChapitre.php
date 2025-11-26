<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'models/Database.php';
    $bdd = Database::getConnection();
    if (isset($_SESSION['username'])){
        require_once 'views/layouts/headerConnecter.php';
    }
    else{
        require_once 'views/layouts/header.php';
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/chapter/listChapter.css">    
    <title>Ajout de chapitres</title>
</head>
<body>
    <div class="chapter-list-page">
        <div class="container">
            <div class="page-header">
                <div class="header-icon">📚</div>
                <h1>Liste des chapitres</h1>
            </div>

            <div class="chapters-grid">
                <?php
                $rep = $bdd->query("SELECT * FROM Chapter;");
                $hasChapters = false;
                while ($row = $rep->fetch()) {
                    $hasChapters = true;
                ?>
                    <div class="chapter-card">
                        <div class="chapter-header">
                            <span class="chapter-icon">📖</span>
                            <span class="chapter-id">Chapitre <?= $row['id'] ?></span>
                        </div>
                        <div class="chapter-content">
                            <?= htmlspecialchars($row['content']) ?>
                        </div>
                        <div class="chapter-actions">
                            <form method="post" action="/admin/chapter/modify">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-modify">✏️ Modifier</button>
                            </form>
                            <form method="post" action="/admin/chapter/delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce chapitre ? Cette action est irréversible.')">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-delete">🗑️ Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php
                }
                $rep->closeCursor();
                
                if (!$hasChapters) {
                ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-text">Aucun chapitre pour le moment</div>
                    </div>
                <?php
                }
                ?>
            </div>

            <div class="add-chapter-section">
                <div class="add-icon">➕</div>
                <form method="post" action="/admin/chapter/add">
                    <button type="submit" class="btn-add">Ajouter un chapitre</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>
