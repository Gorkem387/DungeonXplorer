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
                    <a href="/chapter/<?= $resultat['next_chapter_id'] ?>" class="btn-custom btn-success">
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