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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/chapter/addChapter.css">    
</head>
<body>
    <div class="form-wrapper">
        <div class="form-container">
            <div class="form-header">
                <div class="header-icon">📖</div>
                <h1>Création de votre chapitre</h1>
            </div>
            
            <div class="form-content">
                <form action="/admin/chapter/add/add" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">✍️</div>
                            <h2>Description</h2>
                        </div>
                        <textarea id="desc" name="desc" rows="4" placeholder="Racontez l'essence de votre chapitre..." maxlength="255"></textarea>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">🖼️</div>
                            <h2>Image d'illustration</h2>
                        </div>
                        <div class="file-upload-area">
                            <input type="file" id="image" name="image" accept="image/*">
                            <div class="file-upload-icon">📁</div>
                            <div class="file-upload-text">Glissez votre image ici</div>
                            <div class="file-upload-hint">ou cliquez pour parcourir</div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">⬅️</div>
                            <h2>Chapitres précédents</h2>
                        </div>
                        <div class="links-grid">
                            <?php
                            $rep = $bdd->query("SELECT * FROM Chapter;");
                            while ($row = $rep->fetch()) {
                            ?>
                                <div class="link-option-card">
                                    <div class="link-card-header">
                                        <input type="checkbox" id="precedent-<?= $row['id'] ?>" name="precedent[<?= $row['id'] ?>][selected]" value="<?= $row['id'] ?>">
                                        <span class="chapter-badge">Chapitre <?= $row['id'] ?></span>
                                    </div>
                                    <input type="text" name="precedent[<?= $row['id'] ?>][name]" placeholder="Nom du lien vers ce chapitre..." class="link-name-input">
                                </div>
                            <?php
                            }
                            $rep->closeCursor();
                            ?>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">➡️</div>
                            <h2>Chapitres suivants</h2>
                        </div>
                        <div class="links-grid">
                            <?php
                            $rep = $bdd->query("SELECT * FROM Chapter;");
                            while ($row = $rep->fetch()) {
                            ?>
                                <div class="link-option-card">
                                    <div class="link-card-header">
                                        <input type="checkbox" id="prochain-<?= $row['id'] ?>" name="prochain[<?= $row['id'] ?>][selected]" value="<?= $row['id'] ?>">
                                        <span class="chapter-badge">Chapitre <?= $row['id'] ?></span>
                                    </div>
                                    <input type="text" name="prochain[<?= $row['id'] ?>][name]" placeholder="Nom du lien vers ce chapitre..." class="link-name-input">
                                </div>
                            <?php
                            }
                            $rep->closeCursor();
                            ?>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="submit-btn">Créer le chapitre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>
