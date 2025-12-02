<?php 
require_once 'models/Inventory.php';

echo "show items";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//if (!isset($_SESSION['hero_id'])) {
///    exit();
//}

$inventoryItems = Inventory::getHeroItems(27);//$_SESSION['hero_id']);
//print_r($inventoryItems); //test
if ($inventoryItems == null) { echo "inventoryItems = null";}
foreach ($inventoryItems as $inventoryItem): ?>
<div class="list-group-item d-flex justify-content-between align-items-center">
    <div>
        <img src="./public/<?php echo $inventoryItem[0]?>" alt="<?php echo $inventoryItem[0]?>">
        <?php echo $inventoryItem[0] ?>x<?php echo $inventoryItem[1]?>
    </div>
</div>

<?php endforeach; ?>