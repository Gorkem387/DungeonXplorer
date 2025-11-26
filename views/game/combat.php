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
?>

<link rel="stylesheet" href="/public/css/combat.css">

<div class="combat-arena">
    <div class="container-fluid">
        
        <h1 class="combat-title">Combat<?= $combat['turn'] ?></h1>
        
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
                                <div class="progress-bar hp-bar" style="width: <?= ($hero['pv'] / 100) * 100 ?>%">
                                    <?= $hero['pv'] ?> / 200
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-bar">
                            <div class="stat-label">
                                <i class="fa-solid fa-fire"></i> Mana
                            </div>
                            <div class="progress">
                                <div class="progress-bar mana-bar" style="width: <?= ($hero['mana'] / 100) * 100 ?>%">
                                    <?= $hero['mana'] ?> / 50
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="combatant-image">
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
            </div>
            
            <div class="combat-center">
                
                <div class="combat-log">
                    <h3><i class="fa-solid fa-scroll"></i> Historique du combat</h3>
                    <div class="log-content">
                        <?php foreach ($log as $message): ?>
                            <p><?= htmlspecialchars($message) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="combat-actions">
                    <!-- <h3><i class="fa-solid fa-hand-fist"></i> Vos Actions</h3> -->
                    
                    <div class="action-buttons">
                        <form method="POST" action="/combat/attack" style="display: inline-block;">
                            <button type="submit" class="action-btn attack-btn">
                                <i class="fa-solid fa-sword"></i>
                                <span class="action-name">Attaque Basique</span>
                                <span class="action-desc">Attaque physique basique</span>
                            </button>
                        </form>
                        
                        <form method="POST" action="/combat/attack" style="display: inline-block;">
                            <button type="submit" class="action-btn attack-btn">
                                <i class="fa-solid fa-sword"></i>
                                <span class="action-name">Attaque Basique</span>
                                <span class="action-desc">Attaque physique basique</span>
                            </button>
                        </form>
                        <!--
                        <form method="POST" action="/combat/magic" style="display: inline-block;">
                            <button type="submit" class="action-btn magic-btn" <?= $hero['mana'] < 20 ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                <span class="action-name">Sort</span>
                                <span class="action-desc">Coût: 20 mana</span>
                            </button>
                        </form>-->
                    </div>
                </div>
                
            </div>
            
            <div class="combatant monster-side">
                <div class="combatant-card">
                    <div class="combatant-header monster-header">
                        <h2><?= htmlspecialchars($monster['name']) ?></h2>
                        <span class="level-badge">Niveau <?= $monster['name'] ?></span>
                    </div>
                    
                    <div class="stats-bars">
                        <div class="stat-bar">
                            <div class="stat-label">
                                <i class="fa-solid fa-heart"></i> PV
                            </div>
                            <div class="progress">
                                <div class="progress-bar hp-bar enemy" style="width: <?= ($monster['pv'] / 100) * 100 ?>%">
                                    <?= $monster['pv'] ?> / 200
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="combatant-image">
                        <img src="/public/img/Evil warrior.jpg" alt="Monstre">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logContent = document.querySelector('.log-content');
        if (logContent) {
            logContent.scrollTop = logContent.scrollHeight;
        }
    });
</script>

<?php require_once 'views/layouts/footer.php'; ?>