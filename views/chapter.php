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
                 alt="Chapitre <?php echo htmlspecialchars($chapter->getId()); ?>" 
                 style="max-width: 100%; height: auto;">
        <?php endif; ?>
        
        <div class="content">
            <?php echo nl2br(htmlspecialchars($chapter->getContent())); ?>
        </div>
        
        <?php if (!empty($chapter->getLinks())): ?>
            <h2>Choisissez votre chemin :</h2>
            <ul class="choices">
                <?php foreach ($chapter->getLinks() as $link): ?>
                    <li>
                        <a href="/chapter/<?php echo $link['next_chapter_id']; ?>">
                            <?php echo htmlspecialchars($link['description']); ?>
                        </a>
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