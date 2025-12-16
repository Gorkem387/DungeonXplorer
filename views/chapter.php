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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chapter->getTitle()); ?></title>
    <link rel="stylesheet" href="/public/css/chapter.css">
</head>
<body>
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
</div>
<?php unset($_SESSION['level_up_notification']); endif; ?>

    <div class="chapter-container">
        <h1><?php echo htmlspecialchars($chapter->getTitle()); ?></h1>
        
        <?php if ($chapter->getImage()): ?>
            <img src="/public/img/<?php echo htmlspecialchars($chapter->getImage()); ?>" 
                 alt="<?php echo htmlspecialchars($chapter->getTitle()); ?>">
        <?php endif; ?>
        
        <div class="content">
            <?php echo nl2br(htmlspecialchars($chapter->getContent())); ?>
        </div>
        
        <?php if (isset($encounter) && $encounter): ?>         
            <form method="POST" action="/combat/start/<?php echo $_SESSION['current_hero_id']; ?>" class="combat-start-form">
                <input type="hidden" name="chapter_id" value="<?php echo $chapter->getId(); ?>">
                <button type="submit" class="btn-custom" style="width: 100%; max-width: 400px; margin: 1.5rem auto 0; display: block;">
                    <i class="fa-solid fa-dragon"></i>
                    Affronter le monstre
                </button>
            </form>
            <?php $_SESSION['currentChapterId'] = $chapter->getId();?>
            
        <?php elseif (!empty($chapter->getLinks())): ?>
            <h2>Choisissez votre chemin :</h2>
            <ul class="choices">
            <?php $_SESSION['currentChapterId'] = $chapter->getId();
                foreach ($chapter->getLinks() as $link): ?>
                    <li>
                        <form method="post" action="/chapitre/next">
                            <input type="hidden" name="id" value="<?php echo $link['next_chapter_id']?>">
                            <button type="submit" class="btn btn-modify"><?php echo htmlspecialchars($link['description'])?></button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><strong>Fin du chapitre</strong></p>
            <a href="/">Retour à l'accueil</a>
        <?php endif; ?>
    </div>
</body>
</html>

<?php require_once 'views/layouts/footer.php'; ?>