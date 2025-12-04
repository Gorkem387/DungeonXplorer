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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/chapter/modifyChapter.css">    
    <title>Modification d'un chapitre</title>
</head>
<body>
    <div class="form-wrapper">
        <div class="form-container">
            <div class="form-header">
                <div class="header-icon">✏️</div>
                <h1>Modification du chapitre <?php echo $_SESSION['id']?></h1>
            </div>
            
            <div class="form-content">
                <form action="/admin/chapter/modify/modify" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">📝</div>
                            <h2>Description</h2>
                        </div>
                        <div class="current-value">
                            <strong>Description actuelle :</strong> <?php echo htmlspecialchars($_SESSION['desc'])?>
                        </div>
                        <input type="text" id="desc" name="desc" placeholder="Entrez une nouvelle description..." value="<?php echo htmlspecialchars($_SESSION['desc'])?>">
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">⬅️</div>
                            <h2>Chapitres précédents</h2>
                        </div>
                        <div class="links-grid">
                            <?php
                            $currentPrev = [];
                            $prevQuery = $bdd->query("SELECT chapter_id, description FROM Links WHERE next_chapter_id = " . $_SESSION['id']);
                            while ($link = $prevQuery->fetch(PDO::FETCH_ASSOC)) {
                                $currentPrev[$link['chapter_id']] = $link['description'];
                            }
                            
                            $rep = $bdd->query("SELECT * FROM Chapter WHERE id != " . $_SESSION['id']);
                            while ($row = $rep->fetch()) {
                                $isChecked = isset($currentPrev[$row['id']]) ? 'checked' : '';
                                $cardClass = isset($currentPrev[$row['id']]) ? 'selected' : '';
                                $linkName = isset($currentPrev[$row['id']]) ? $currentPrev[$row['id']] : '';
                            ?>
                                <div class="link-option-card <?= $cardClass ?>">
                                    <div class="link-card-header">
                                        <input type="checkbox" 
                                               id="precedent-<?= $row['id'] ?>" 
                                               name="precedent[<?= $row['id'] ?>][selected]" 
                                               value="<?= $row['id'] ?>"
                                               <?= $isChecked ?>>
                                        <span class="chapter-badge">Chapitre <?= $row['id'] ?></span>
                                    </div>
                                    <input type="text" 
                                           name="precedent[<?= $row['id'] ?>][name]" 
                                           placeholder="Nom du lien..." 
                                           class="link-name-input"
                                           value="<?= htmlspecialchars($linkName) ?>">
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
                            $currentNext = [];
                            $nextQuery = $bdd->query("SELECT next_chapter_id, description FROM Links WHERE chapter_id = " . $_SESSION['id']);
                            while ($link = $nextQuery->fetch(PDO::FETCH_ASSOC)) {
                                $currentNext[$link['next_chapter_id']] = $link['description'];
                            }
                            
                            $rep = $bdd->query("SELECT * FROM Chapter WHERE id != " . $_SESSION['id']);
                            while ($row = $rep->fetch()) {
                                $isChecked = isset($currentNext[$row['id']]) ? 'checked' : '';
                                $cardClass = isset($currentNext[$row['id']]) ? 'selected' : '';
                                $linkName = isset($currentNext[$row['id']]) ? $currentNext[$row['id']] : '';
                            ?>
                                <div class="link-option-card <?= $cardClass ?>">
                                    <div class="link-card-header">
                                        <input type="checkbox" 
                                               id="prochain-<?= $row['id'] ?>" 
                                               name="prochain[<?= $row['id'] ?>][selected]" 
                                               value="<?= $row['id'] ?>"
                                               <?= $isChecked ?>>
                                        <span class="chapter-badge">Chapitre <?= $row['id'] ?></span>
                                    </div>
                                    <input type="text" 
                                           name="prochain[<?= $row['id'] ?>][name]" 
                                           placeholder="Nom du lien..." 
                                           class="link-name-input"
                                           value="<?= htmlspecialchars($linkName) ?>">
                                </div>
                            <?php
                            }
                            $rep->closeCursor();
                            ?>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="submit-btn">Modifier le chapitre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.link-option-card input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.link-option-card');
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        });
    </script>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>
