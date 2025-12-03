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
    <title>Chapitre <?php echo htmlspecialchars($chapter->getId()); ?></title>
    <link rel="stylesheet" href="/public/css/chapter.css">
</head>
<body>
    <div class="chapter-container">
        <h1>Chapitre <?php echo htmlspecialchars($chapter->getId()); ?></h1>
        
        <?php if ($chapter->getImage()): ?>
            <img src="/public/img/<?php echo htmlspecialchars($chapter->getImage()); ?>" 
                 alt="Chapitre <?php echo htmlspecialchars($chapter->getId()); ?>">
        <?php endif; ?>
        
        <div class="content">
            <?php echo nl2br(htmlspecialchars($chapter->getContent())); ?>
        </div>
        
        <?php if (isset($encounter) && $encounter): ?>         
            <form method="POST" action="/combat/start/<?php echo $_SESSION['current_hero_id']; ?>">
                <input type="hidden" name="chapter_id" value="<?php echo $chapter->getId(); ?>">
                <button type="submit" class="encounter-button">
                    <i class="fa-solid fa-dragon"></i>
                    Affronter le monstre
                </button>
            </form>
            
        <?php elseif (!empty($chapter->getLinks())): ?>
            <h2>Choisissez votre chemin :</h2>
            <ul class="choices">
                <?php foreach ($chapter->getLinks() as $link): ?>
                    <li>
                        <form method="post" action="/chapitre/next">
                            <input type="hidden" name="id" value="<?php echo $link['next_chapter_id']?>">
                            <button type="submit" class="btn btn-modify"><?php echo htmlspecialchars($link['description'])?></button>
                        </form>
                        <!--
                            <a href="/chapter/<?php// echo $link['next_chapter_id']; ?>">
                            <?php //echo htmlspecialchars($link['description']); ?>
                        </a>---->
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