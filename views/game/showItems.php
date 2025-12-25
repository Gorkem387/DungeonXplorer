<link rel="stylesheet" href="/public/css/item/item.css">

<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['current_hero_id'])) {
    echo json_encode(['error' => 'Aucun héros en combat']);
    exit;
}

require_once 'models/Database.php';
$bdd = Database::getConnection();

$heroId = $_SESSION['current_hero_id'];
$equippedWeapon = $_SESSION['combat']['equipped_weapon'] ?? null;

$query = $bdd->prepare("
    SELECT i.*, hi.quantity 
    FROM Inventory hi
    JOIN Items i ON hi.item_id = i.id
    WHERE hi.hero_id = ? AND hi.quantity > 0 AND i.is_armor = 0
    ORDER BY i.name
");
$query->execute([$heroId]);
$items = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    echo '<div class="empty-inventory">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #666; margin-bottom: 1rem;"></i>
            <p>Votre inventaire est vide</p>
          </div>';
    exit;
}

$weapons = [];
$consumables = [];

foreach ($items as $item) {
    if ($item['damage_multiplier'] > 1.0) {
        $weapons[] = $item;
    } else {
        $consumables[] = $item;
    }
}
?>

<?php if (!empty($weapons)): ?>
<div class="inventory-section">
    <div class="inventory-section-title">
        <i class="fa-solid fa-sword"></i> Armes
    </div>
    <div class="inventory-grid">
        <?php foreach ($weapons as $item): 
            $isEquipped = ($equippedWeapon && $equippedWeapon['id'] == $item['id']);
            $needsAmmo = $item['requires_ammo'] > 0;
            $tooltipText = htmlspecialchars($item['description']);
            if ($item['damage_multiplier'] > 1.0) {
                $tooltipText .= "\nMultiplicateur: x" . $item['damage_multiplier'];
            }
            if ($item['dodge_modifier'] != 0) {
                $tooltipText .= "\nÉsquive monstre: " . ($item['dodge_modifier'] > 0 ? '+' : '') . $item['dodge_modifier'] . '%';
            }
            if ($needsAmmo) {
                $tooltipText .= "\nNécessite des munitions";
            }
        ?>
        <div class="inventory-item-card <?= $isEquipped ? 'equipped' : '' ?>" 
             data-item-id="<?= $item['id'] ?>"
             data-action="equip"
             onclick="useItem(<?= $item['id'] ?>, 'equip')">
            <?php if ($isEquipped): ?>
            <span class="item-badge">ÉQUIPÉ</span>
            <?php endif; ?>
            <div class="item-image">
                <img src="/public/img/Items/<?= htmlspecialchars($item['name']) ?>.jpg" 
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     onerror="this.src='/public/img/Items/default.jpg'">
            </div>
            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="item-quantity">x<?= $item['quantity'] ?></div>
            <div class="item-tooltip"><?= nl2br($tooltipText) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($consumables)): ?>
<div class="inventory-section">
    <div class="inventory-section-title">
        <i class="fa-solid fa-flask"></i> Consommables
    </div>
    <div class="inventory-grid">
        <?php foreach ($consumables as $item): 
            $isDisabled = false;
            $tooltipText = htmlspecialchars($item['description']);
            
            if ($item['name'] == 'Flèche' || stripos($item['name'], 'flèche') !== false) {
                if (!$equippedWeapon || $equippedWeapon['requires_ammo'] == 0) {
                    $isDisabled = true;
                    $tooltipText .= "\n⚠️ Nécessite un arc équipé";
                } else {
                    $tooltipText .= "\nDégâts d'arc: x" . $equippedWeapon['damage_multiplier'];
                }
            }
            
            if (stripos($item['name'], 'fiole') !== false || stripos($item['name'], 'poison') !== false) {
                if (!$equippedWeapon || $equippedWeapon['requires_ammo'] == 0) {
                    $isDisabled = true;
                    $tooltipText .= "\n⚠️ Nécessite un arc équipé + une flèche";
                } else {
                    $tooltipText .= "\nDégâts: " . $item['damage'] . " (imparable!)";
                    $tooltipText .= "\nConsomme: 1 flèche + 1 fiole";
                }
            }
            
            if ($item['is_heal']) {
                $tooltipText .= "\nRestaure: " . $item['damage'] . " PV";
            }
        ?>
        <div class="inventory-item-card <?= $isDisabled ? 'disabled' : '' ?>" 
             data-item-id="<?= $item['id'] ?>"
             data-action="use"
             <?= !$isDisabled ? 'onclick="useItem(' . $item['id'] . ', \'use\')"' : '' ?>>
            <div class="item-image">
                <img src="/public/img/Items/<?= htmlspecialchars($item['name']) ?>.jpg" 
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     onerror="this.src='/public/img/Items/default.jpg'">
            </div>
            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="item-quantity">x<?= $item['quantity'] ?></div>
            <div class="item-tooltip"><?= nl2br($tooltipText) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
