<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['hero_id'])) {
    header("Location: /hero");
    exit();
}

$inventoryItems = Inventory::findById($_SESSION['hero_id']);

foreach ($inventoryItems as $inventoryItem): ?>
<div class="list-group-item d-flex justify-content-between align-items-center">
    <div>
        <img src="./public/<?php $inventoryItem['item']['name']?>" alt="<?php $inventoryItem['item']['name']?>">
        <?php $inventoryItem['item']['name'] ?>x<?php $inventoryItem['quantity']?>
    </div>
</div>

<?php endforeach; ?>