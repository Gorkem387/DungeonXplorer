<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chapter->getTitle()); ?></title>
    <link rel="stylesheet" href="/public/css/chapter.css">
</head>
<body>
    <div class="chapter-container">
        <h1><?php echo htmlspecialchars($chapter->getTitle()); ?></h1>
        
        <img src="/<?php echo htmlspecialchars($chapter->getImage()); ?>" 
             alt="<?php echo htmlspecialchars($chapter->getTitle()); ?>" 
             style="max-width: 100%; height: auto;">
        
        <p><?php echo nl2br(htmlspecialchars($chapter->getDescription())); ?></p>
        
        <h2>Choisissez votre chemin:</h2>
        <ul>
            <?php foreach ($chapter->getChoices() as $choice): ?>
                <li>
                    <a href="/chapter/<?php echo $choice['chapter']; ?>">
                        <?php echo htmlspecialchars($choice['text']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>