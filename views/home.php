<?php require_once 'views/layouts/header.php'; ?>

<div class="hero">
    <div class="container">
        <h1><i class="fa-solid fa-hat-wizard"></i> DungeonXplorer</h1>
        <p class="subtitle">Plongez dans l'univers des livres dont vous êtes le héros</p>
    </div>
</div>

<div class="container">
    <div class="edito">
        <h2>Bienvenue Aventurier !</h2>
        <p>
            Bienvenue sur <strong>DungeonXplorer</strong>, l'univers de dark fantasy où se mêlent aventure, stratégie et immersion totale dans les récits interactifs.
        </p>
        <p>
            Ce projet est né de la volonté de l'association <em>Le Val des Données Perdues</em> de raviver l'expérience unique des livres dont vous êtes le héros. Notre vision : offrir à la communauté un espace où chacun peut incarner un personnage et plonger dans des quêtes épiques et personnalisées.
        </p>
        <p>
            Dans sa première version, DungeonXplorer permettra aux joueurs de créer un personnage parmi trois classes emblématiques — <strong>guerrier, voleur, magicien</strong> — et d'évoluer dans un scénario captivant, tout en assurant à chacun la possibilité de conserver sa progression.
        </p>
        <p>
            Nous sommes enthousiastes de partager avec vous cette application et espérons qu'elle saura vous plonger au cœur des mystères du Val Perdu !
        </p>
    </div>
</div>

<div class="cta-section">
    <a href="/register" class="btn-custom">
        <i class="fa-solid fa-user-plus"></i> Créer un compte
    </a>
    <a href="/login" class="btn-custom">
        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
    </a>
</div>

<div class="container features">
    <div class="row">
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fa-solid fa-user-shield"></i>
                <h3>Créez votre héros</h3>
                <p>Choisissez parmi 3 classes de personnages et façonnez votre propre légende.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fa-solid fa-book-open"></i>
                <h3>Vivez l'aventure</h3>
                <p>Explorez un univers riche en choix et conséquences dans un récit interactif.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fa-solid fa-floppy-disk"></i>
                <h3>Sauvegardez</h3>
                <p>Votre progression est conservée pour reprendre l'aventure à tout moment.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>