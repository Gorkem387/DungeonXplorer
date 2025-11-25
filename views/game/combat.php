<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center mb-4">⚔️ Combat ⚔️</h1>
    
    <div class="combat-log">
        <?php foreach ($resultat['log'] as $message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <?php if ($resultat['winner'] === 'hero'): ?>
            <a href="/game/continue" class="btn-custom">Continuer l'aventure</a>
        <?php else: ?>
            <a href="/game/restart" class="btn-custom">Recommencer</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>