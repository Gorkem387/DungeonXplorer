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
require_once 'models/Database.php';
$bdd = Database::getConnection();
$query = "SELECT * FROM Class";
$stmt = $bdd->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/login.css">    
    <link rel="stylesheet" href="/public/css/createHero.css">
    <title>Création de votre hero</title>
</head>
<body>
    <div class="hero-creation-container">
        <div class="creation-header">
            <h1><i class="fas fa-user-plus"></i> Créez votre Héros</h1>
            <p class="subtitle">Forgez votre légende dans les terres obscures</p>
        </div>

        <form action="/hero/submit" method="post" id="heroForm">
            <div class="creation-section">
                <h2 class="section-title">Identité du Héros</h2>
                <div class="input-group-modern">
                    <label for="name"><i class="fas fa-signature"></i> Nom du héros</label>
                    <input type="text" id="name" name="name" placeholder="Entrez le nom de votre héros" required>
                </div>

                <div class="input-group-modern">
                    <label for="desc"><i class="fas fa-scroll"></i> Description</label>
                    <textarea id="desc" name="desc" rows="4" placeholder="Racontez l'histoire de votre héros..."></textarea>
                </div>
            </div>

            <div class="creation-section">
                <h2 class="section-title">Choisissez votre Classe</h2>
                <div class="class-selection">
                    <?php foreach($classes as $index => $class): ?>
                    <div class="class-card">
                        <input type="radio" name="type" id="class-<?php echo $class['id']; ?>" 
                               value="<?php echo htmlspecialchars($class['name']); ?>" 
                               <?php echo $index === 0 ? 'checked' : ''; ?>
                               data-pv="<?php echo $class['base_pv']; ?>"
                               data-mana="<?php echo $class['base_mana']; ?>"
                               data-strength="<?php echo $class['strength']; ?>"
                               data-initiative="<?php echo $class['initiative']; ?>">
                        <label for="class-<?php echo $class['id']; ?>">
                            <div class="class-icon">
                                <i class="fas fa-<?php 
                                    echo $class['name'] === 'guerrier' ? 'shield-alt' : 
                                         ($class['name'] === 'magicien' ? 'hat-wizard' : 'mask');
                                ?>"></i>
                            </div>
                            <h3><?php echo ucfirst(htmlspecialchars($class['name'])); ?></h3>
                            <p class="class-desc"><?php echo htmlspecialchars($class['description']); ?></p>
                            <div class="class-stats">
                                <div class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <span class="stat-value"><?php echo $class['base_pv']; ?></span>
                                    <span class="stat-label">PV</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-magic"></i>
                                    <span class="stat-value"><?php echo $class['base_mana']; ?></span>
                                    <span class="stat-label">Mana</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-fist-raised"></i>
                                    <span class="stat-value"><?php echo $class['strength']; ?></span>
                                    <span class="stat-label">Force</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-bolt"></i>
                                    <span class="stat-value"><?php echo $class['initiative']; ?></span>
                                    <span class="stat-label">Initiative</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="creation-section">
                <h2 class="section-title">Choisissez votre Avatar</h2>
                <div class="avatar-selection">
                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-1" value="Wizard.jpg" checked>
                        <label for="avatar-1">
                            <img src="/public/img/personnage/Wizard.jpg" alt="Wizard">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-2" value="Berserker.jpg">
                        <label for="avatar-2">
                            <img src="/public/img/personnage/Berserker.jpg" alt="Berserker">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-3" value="Dark Knight.jpg">
                        <label for="avatar-3">
                            <img src="/public/img/personnage/Dark Knight.jpg" alt="Dark Knight">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-4" value="Magician01.jpg">
                        <label for="avatar-4">
                            <img src="/public/img/personnage/Magician01.jpg" alt="Magician">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-5" value="Magician02.jpg">
                        <label for="avatar-5">
                            <img src="/public/img/personnage/Magician02.jpg" alt="Magician">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-6" value="Thief.jpg">
                        <label for="avatar-6">
                            <img src="/public/img/personnage/Thief.jpg" alt="Thief">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-7" value="OldMan01.jpg">
                        <label for="avatar-7">
                            <img src="/public/img/personnage/OldMan01.jpg" alt="Old Man">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-8" value="OldMan02.jpg">
                        <label for="avatar-8">
                            <img src="/public/img/personnage/OldMan02.jpg" alt="Old Man">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>

                    <div class="avatar-card">
                        <input type="radio" name="image" id="avatar-9" value="Person.jpg">
                        <label for="avatar-9">
                            <img src="/public/img/personnage/Person.jpg" alt="Person">
                            <div class="avatar-overlay">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="/" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Créer mon Héros
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.class-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.class-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.class-card').classList.add('selected');
            });
        });

        document.querySelector('.class-card input[type="radio"]:checked')?.closest('.class-card').classList.add('selected');

        document.querySelectorAll('.avatar-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.avatar-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.avatar-card').classList.add('selected');
            });
        });

        document.querySelector('.avatar-card input[type="radio"]:checked')?.closest('.avatar-card').classList.add('selected');
    </script>
</body>
</html>
<?php require_once 'views/layouts/footer.php'; ?>