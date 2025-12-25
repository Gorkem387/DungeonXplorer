<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['username'])){
            require_once 'views/layouts/headerConnecter.php';
        }
else{
    require_once 'views/layouts/header.php';
} 

    $hero = $_SESSION['combat']['hero'];
    $monster = $_SESSION['combat']['monster'];

    $heroMaxPv = $hero['max_pv'] ?? 100;
    $heroMaxMana = $hero['max_mana'] ?? 100;
    $monsterMaxPv = $monster['max_pv'] ?? 100;

    $coutSort = 20;
    $isMagicien = ($hero['class_id'] ?? '') === 2;
    $isDefending = $combat['defending'] ?? false;
?>

<link rel="stylesheet" href="/public/css/combat.css">

<div class="combat-arena" data-is-defending="<?= $isDefending ? 'true' : 'false' ?>">
    <div class="container-fluid">
        
        <div id="hero-name-data" style="display: none;"><?= htmlspecialchars($hero['name']) ?></div>
        <div id="max-hp-data" style="display: none;"><?= $heroMaxPv ?></div>
        <div id="max-mana-data" style="display: none;"><?= $heroMaxMana ?></div>
        <div id="monster-max-hp-data" style="display: none;"><?= $monsterMaxPv ?></div>
        
        <h1 class="combat-title">Combat Tour n°<?= $combat['turn'] ?? 1 ?></h1>
        
        <div class="combat-layout">
            
            <div class="combatant hero-side">
                <div class="combatant-card">
                    <div class="combatant-header">
                        <h2><?= htmlspecialchars($hero['name']) ?></h2>
                        <span class="class-badge"><?= htmlspecialchars($hero['class_name'] ?? 'Guerrier') ?></span>
                    </div>
                    
                    <div class="stats-bars">
                        <div class="stat-bar">
                            <div class="stat-label">
                                <i class="fa-solid fa-heart"></i> PV
                            </div>
                            <div class="progress">
                                <div class="progress-bar hp-bar" id="hero-hp-bar" style="width: <?= min(100, ($hero['pv'] / $heroMaxPv) * 100) ?>%">
                                    <span id="hero-hp-text"><?= $hero['pv'] ?> / <?= $heroMaxPv ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($isMagicien): ?>
                        <div class="stat-bar">
                            <div class="stat-label">
                                <i class="fa-solid fa-fire"></i> Mana
                            </div>
                            <div class="progress">
                                <div class="progress-bar mana-bar" id="hero-mana-bar" style="width: <?= min(100, ($hero['mana'] / $heroMaxMana) * 100) ?>%">
                                    <span id="hero-mana-text"><?= $hero['mana'] ?> / <?= $heroMaxMana ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="combatant-image" style="position: relative;">
                        <img src="<?= htmlspecialchars($hero['image'] ?? '/public/img/classes/default.png') ?>" alt="Héros">
                    </div>

                    <div class="stat-info-grid">
                        <div class="stat-info">
                            <i class="fa-solid fa-dumbbell"></i> Force: <strong><?= $hero['strength'] ?></strong>
                        </div>
                        <div class="stat-info">
                            <i class="fa-solid fa-bolt"></i> Initiative: <strong><?= $hero['initiative'] ?></strong>
                        </div>
                    </div>
                    
                </div>
                <div class="equipment-slots">
                    <?php 
                    $slots = [1 => 'Tête', 2 => 'Torse', 3 => 'Jambes', 4 => 'Arme'];
                    foreach ($slots as $id => $label): 
                        $equippedItem = $_SESSION['permanent_equipment'][$id] ?? null;
                        $activeClass = $equippedItem ? 'active-slot' : '';
                        $displayName = $equippedItem ? $equippedItem['name'] : 'Vide';
                    ?>
                        <div class="slot <?= $activeClass ?>" id="slot-<?= $id ?>">
                            <?= $label ?>: <span class="slot-name"><?= htmlspecialchars($displayName) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="combat-center">
                <div id="action-display" style="text-align: center; min-height: 150px; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 20px 0; font-size: 1.5rem; font-weight: bold;">
                    <?php if ($isDefending): ?>
                        <p style="color: #4a7a66;">Vous êtes en position défensive !</p>
                    <?php else: ?>
                        <p style="color: #ffd700;">Choisissez votre action</p>
                    <?php endif; ?>
                </div>

                <div class="combat-actions">
                    
                    <div class="action-buttons">
                        <button type="button" class="action-btn item-btn" id="inventory-btn" onclick="openInventoryModal()">
                            <i class="fa-solid fa-briefcase"></i>
                            <span class="action-name">Inventaire</span>
                            <span class="action-desc">Utiliser un objet</span>
                        </button>
                        
                        <button type="button" class="action-btn attack-btn" onclick="performAction('attack')" <?= $isDefending ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-sword"></i>
                            <span class="action-name">Attaque Basique</span>
                            <span class="action-desc">Physique</span>
                        </button>
                        
                        <?php if ($isMagicien): ?>
                        <button type="button" class="action-btn magic-btn" id="spell-btn" onclick="performAction('spell')" <?= $hero['mana'] < $coutSort || $isDefending ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span class="action-name">Sort Magique</span>
                            <span class="action-desc">Coût: <?= $coutSort ?> mana</span>
                        </button>
                        <?php endif; ?>

                        <button type="button" class="action-btn defend-btn" id="defend-btn" onclick="performAction('defend')" <?= $isDefending ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-shield-halved"></i>
                            <span class="action-name">Défendre</span>
                            <span class="action-desc">+5 Défense</span>
                        </button>
                    </div>
                </div>
                
            </div>
            
            <div class="combatant monster-side">
                <div class="combatant-card">
                    <div class="combatant-header monster-header">
                        <h2><?= htmlspecialchars($monster['name']) ?></h2>
                        <span class="level-badge">Monstre</span>
                    </div>
                    
                    <div class="stats-bars">
                        <div class="stat-bar">
                            <div class="stat-label">
                                <i class="fa-solid fa-heart"></i> PV
                            </div>
                            <div class="progress">
                                <div class="progress-bar hp-bar enemy" id="monster-hp-bar" style="width: <?= min(100, ($monster['pv'] / $monsterMaxPv) * 100) ?>%">
                                    <span id="monster-hp-text"><?= $monster['pv'] ?> / <?= $monsterMaxPv ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="combatant-image">
                        <img src="<?= htmlspecialchars($monster['img'] ?? '/public/img/classes/default_monster.png') ?>" alt="Monstre">
                    </div>
                    
                    <div class="stat-info-grid">
                        <div class="stat-info">
                            <i class="fa-solid fa-dumbbell"></i> Force: <strong><?= $monster['strength'] ?></strong>
                        </div>
                        <div class="stat-info">
                            <i class="fa-solid fa-bolt"></i> Initiative: <strong><?= $monster['initiative'] ?></strong>
                        </div>
                    </div>
                    
                    <div class="xp-reward">
                        <i class="fa-solid fa-star"></i> Récompense: <strong><?= $monster['xp'] ?> XP</strong>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<div id="inventory-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Inventaire de <?= htmlspecialchars($hero['name']) ?></h2>
            <span class="close-btn" onclick="closeInventoryModal()">&times;</span>
        </div>
        <div id="inventory-content-list" class="inventory-list">
            </div>
    </div>
</div>

<script src="/public/js/combat.js"></script>

<?php require_once 'views/layouts/footer.php'; ?>