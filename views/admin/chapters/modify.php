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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/chapter/modifyChapter.css">    
    <link rel="stylesheet" href="/public/css/item/item.css">    
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
                            <div class="section-icon">🎒</div>
                            <h2>Items du chapitre</h2>
                        </div>
                        <div class="item-list">
                            <?php
                            $allItemsQuery = $bdd->query("SELECT id, name, description, item_type, max_quantity FROM Items");
                            $allItems = $allItemsQuery->fetchAll(PDO::FETCH_ASSOC);
                            
                            $chapterItemsQuery = $bdd->prepare("SELECT item_id, quantity FROM Chapter_Item WHERE chapter_id = ?");
                            $chapterItemsQuery->execute([$_SESSION['id']]);
                            $chapterItems = [];
                            while ($row = $chapterItemsQuery->fetch(PDO::FETCH_ASSOC)) {
                                $chapterItems[$row['item_id']] = $row['quantity'];
                            }
                            foreach ($allItems as $item) {
                                $currentQuantity = isset($chapterItems[$item['id']]) ? $chapterItems[$item['id']] : 0;
                            ?>        
                            <div class="item-card">
                                <div class="item-image-container">
                                    <img src="/public/img/Items/<?= htmlspecialchars($item['name']) ?>.jpg" 
                                         alt="Image de <?= htmlspecialchars($item['name']) ?>"
                                         onerror="this.src='/public/img/Items/default.jpg'">
                                </div>
                                <div class="item-info">
                                    <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-type"><?= htmlspecialchars($item['item_type']) ?></span>
                                </div>
                                <div class="item-quantity">
                                    <label for="item-<?= $item['id'] ?>">Quantité:</label>
                                    <input type="number" 
                                           id="item-<?= $item['id'] ?>" 
                                           name="items[<?= $item['id'] ?>]" 
                                           min="0" 
                                           max="<?= $item['max_quantity'] ?>" 
                                           value="<?= $currentQuantity ?>"
                                           class="quantity-input">
                                    <span class="max-quantity">/ <?= $item['max_quantity'] ?></span>
                                </div>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
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
        <div id="itemModal" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h2 class="modal-title">Ajouter un item</h2>
                <div class="available-items" id="availableItems">
                    <?php
                        $allItemsQuery = $bdd->query("SELECT i.id, i.name, i.max_quantity FROM Items i WHERE i.id NOT IN (SELECT item_id FROM Chapter_Item WHERE chapter_id = " . $_SESSION['id'] . ")");
                        $hasItems = false;
                        while ($item = $allItemsQuery->fetch(PDO::FETCH_ASSOC)) {
                            $hasItems = true;
                    ?>
                        <div class="available-item" data-item-id="<?=$item['id']?>" data-item-name="<?=htmlspecialchars($item['name'])?>" data-max-quantity="<?=$item['max_quantity']?>">
                            <img src="/public/img/Items/<?=$item['name']?>.jpg" alt="<?=$item['name']?>">
                            <div class="available-item-name"><?=$item['name']?></div>
                            <button onclick="addItemToChapter(this)">Ajouter</button>
                        </div>
                    <?php
                        }
                        if (!$hasItems) {
                            echo '<div class="no-items-message">Tous les items sont déjà dans ce chapitre</div>';
                        }
                        $allItemsQuery->closeCursor();
                    ?>
                </div>
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

        function changeQuantity(button, delta) {
            const itemCard = button.closest('.item-card');
            const quantityDisplay = itemCard.querySelector('.quantity-display');
            const hiddenInput = itemCard.querySelector('.item-quantity-input');
            const maxQuantity = parseInt(itemCard.dataset.maxQuantity);
            
            let currentQuantity = parseInt(hiddenInput.value);
            let newQuantity = currentQuantity + delta;
            
            // Vérifier les limites
            if (newQuantity < 0) newQuantity = 0;
            if (newQuantity > maxQuantity) newQuantity = maxQuantity;
            
            // Si la quantité atteint 0, supprimer la carte
            if (newQuantity === 0) {
                itemCard.style.animation = 'fadeOut 0.3s';
                setTimeout(() => {
                    itemCard.remove();
                    // Rendre l'item disponible dans le modal
                    updateAvailableItems();
                }, 300);
            } else {
                // Mettre à jour l'affichage
                if (quantityDisplay) {
                    quantityDisplay.textContent = newQuantity + '/' + maxQuantity;
                }
                hiddenInput.value = newQuantity;
            }
        }

        function openModal() {
            document.getElementById('itemModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('itemModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('itemModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function addItemToChapter(button) {
            const availableItem = button.closest('.available-item');
            const itemId = availableItem.dataset.itemId;
            const itemName = availableItem.dataset.itemName;
            const maxQuantity = parseInt(availableItem.dataset.maxQuantity);
            
            const itemList = document.getElementById('itemList');
            const newCard = document.createElement('div');
            newCard.className = 'item-card';
            newCard.dataset.itemId = itemId;
            newCard.dataset.originalQuantity = '0';
            newCard.dataset.maxQuantity = maxQuantity;
            newCard.style.animation = 'fadeIn 0.3s';
            
            const showQuantity = maxQuantity > 1;
            const initialQuantity = showQuantity ? 1 : 1;
            
            newCard.innerHTML = `
                <img src="/public/img/Items/${itemName}.jpg" alt="Image de ${itemName}">
                <div class="item-name">${itemName}</div>
                ${showQuantity ? `
                <div class="item-quantity">
                    <button type="button" class="minus" onclick="changeQuantity(this, -1)">-</button>
                    <span class="quantity-display">${initialQuantity}/${maxQuantity}</span>
                    <button type="button" class="plus" onclick="changeQuantity(this, 1)">+</button>
                </div>
                ` : ''}
                <input type="hidden" name="items[${itemId}]" value="${initialQuantity}" class="item-quantity-input">
            `;
            
            itemList.appendChild(newCard);
            
            availableItem.style.animation = 'fadeOut 0.3s';
            setTimeout(() => {
                availableItem.remove();
                
                const remainingItems = document.querySelectorAll('.available-item');
                if (remainingItems.length === 0) {
                    document.getElementById('availableItems').innerHTML = '<div class="no-items-message">Tous les items sont déjà dans ce chapitre</div>';
                }
            }, 300);
        }



    </script>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>
