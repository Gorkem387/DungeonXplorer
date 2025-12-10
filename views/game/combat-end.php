<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['username'])){
        require_once 'views/layouts/headerConnecter.php';
    } else {
        require_once 'views/layouts/header.php';
    } 
?>

<link rel="stylesheet" href="/public/css/combat.css">
 
<?php if (isset($_SESSION['level_up_notification'])): 
    $lvl = $_SESSION['level_up_notification'];
?>
<style>
    .level-up-notif { max-width:900px;margin:18px auto;padding:16px;border-radius:8px;background:linear-gradient(90deg,#22c55e,#10b981);color:#fff;box-shadow:0 6px 18px rgba(16,185,129,0.15);display:flex;align-items:center;gap:16px }
    .level-up-notif .icon {font-size:28px}
    .level-up-notif .content {flex:1}
    .level-up-notif .content b{display:inline-block;margin-right:8px}
    .level-up-notif .stats {font-size:0.95em;opacity:0.95}
</style>
<div class="level-up-notif" role="status">
    <div class="icon">🎉</div>
    <div class="content">
        <div><strong>Montée de niveau !</strong> Votre héros est passé du niveau <b><?php echo htmlspecialchars($lvl['old_level']); ?></b> au niveau <b><?php echo htmlspecialchars($lvl['new_level']); ?></b>.</div>
        <div class="stats">+<?php echo htmlspecialchars($lvl['pv_gained']); ?> PV, +<?php echo htmlspecialchars($lvl['mana_gained']); ?> Mana, +<?php echo htmlspecialchars($lvl['strength_gained']); ?> Force, +<?php echo htmlspecialchars($lvl['initiative_gained']); ?> Initiative</div>
    </div>
    <div><a href="/timeline/<?php echo htmlspecialchars($lvl['hero_id']); ?>" class="btn-custom" style="background:rgba(255,255,255,0.12);color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none">Voir l'historique</a></div>
</div>
<?php unset($_SESSION['level_up_notification']); endif; ?>
<div class="combat-end-screen">
    <div class="container">
        
        <?php if ($resultat['winner'] === 'hero'): ?>
            <div class="result-card victory">
                <div class="result-icon">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h1 class="result-title">Victoire !</h1>
                <p class="result-text"><?= htmlspecialchars($resultat['hero_name']) ?> a triomphé contre <?= htmlspecialchars($resultat['monster_name']) ?> !</p>
                
                <div class="rewards">
                    <h3><i class="fa-solid fa-gift"></i> Récompenses</h3>
                    <div class="reward-item">
                        <i class="fa-solid fa-star"></i>
                        <span>+<?= $resultat['xp'] ?> XP</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="result-card defeat">
                <div class="result-icon">
                    <i class="fa-solid fa-skull"></i>
                </div>
                <h1 class="result-title">Défaite</h1>
                <p class="result-text"><?= htmlspecialchars($resultat['hero_name']) ?> a été vaincu par <?= htmlspecialchars($resultat['monster_name']) ?>...</p>
                <p class="result-text" style="margin-top: 20px; color: #fbbf24;">
                    <i class="fa-solid fa-heart"></i> Vous vous réveillez avec 20 PV
                </p>
            </div>
        <?php endif; ?>
        
        <div class="combat-actions-end">
            <?php if ($resultat['winner'] === 'hero'): ?>
                <?php if ($resultat['has_next_chapter']): ?>
                    <a href="/chapter/next" class="btn-custom btn-success">
                        <i class="fa-solid fa-forward"></i> Continuer l'aventure
                    </a>
                    <a href="/profil" class="btn-custom">
                        <i class="fa-solid fa-home"></i> Retour au profil
                    </a>
                <?php else: ?>
                    <a href="/profil" class="btn-custom btn-success">
                        <i class="fa-solid fa-home"></i> Retour au profil
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/profil" class="btn-custom btn-danger">
                    <i class="fa-solid fa-rotate-right"></i> Retour au profil
                </a>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>