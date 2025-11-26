<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center mb-4">📜 Liste de vos héros</h1>

    <?php if (empty($heroes)): ?>
        <p class="text-center">Aucun héros trouvé pour le moment.</p>
        <div class="text-center mt-4">
            <a href="/hero/submit" class="btn btn-primary">Créer un héros</a>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($heroes as $hero): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($hero['name']) ?></strong><br>
                        PV: <?= $hero['pv'] ?> – Force: <?= $hero['strength'] ?> – XP: <?= $hero['xp'] ?>
                    </div>
                    <div>
                        <a href="/combat/start/<?= $hero['id'] ?>" class="btn btn-success btn-sm">
                            Choisir
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="/hero/submit" class="btn btn-primary">Créer un nouveau héros</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
