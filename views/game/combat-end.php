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
                <p class="result-text">Vous avez triomphé dans ce combat épique !</p>
                
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
                <p class="result-text">Vous avez été vaincu... Mais ne baissez pas les bras !</p>
            </div>
        <?php endif; ?>
        
        <div class="combat-summary">
            <h3><i class="fa-solid fa-scroll"></i> Résumé du combat</h3>
            <div class="summary-log">
                <?php foreach ($resultat['log'] as $message): ?>
                    <p><?= htmlspecialchars($message) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="combat-actions-end">
            <?php if ($resultat['winner'] === 'hero'): ?>
                <?php if ($resultat['is_encounter'] && isset($resultat['next_chapter'])): ?>
                    <a href="/chapter/<?= $resultat['next_chapter'] ?>" class="btn-custom btn-success">
                        <i class="fa-solid fa-forward"></i> Continuer l'aventure
                    </a>
                <?php else: ?>
                    <a href="/profil" class="btn-custom btn-success">
                        <i class="fa-solid fa-forward"></i> Continuer l'aventure
                    </a>
                <?php endif; ?>
                <a href="/profil" class="btn-custom">
                    <i class="fa-solid fa-home"></i> Retour au profil
                </a>
            <?php else: ?>
                <a href="/profil" class="btn-custom btn-danger">
                    <i class="fa-solid fa-rotate-right"></i> Choisir un autre héros
                </a>
                <a href="/profil" class="btn-custom">
                    <i class="fa-solid fa-home"></i> Retour au profil
                </a>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>