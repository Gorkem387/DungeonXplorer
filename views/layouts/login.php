<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/login.css">
    <title>Connexion</title>
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
            ?>
        </div>
    <?php endif; ?>
    <div class="form-container">
        <form action="/traitementLogin.php" method="post">
            <label for="username">Nom d'utilisateur : </label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Mot de passe : </label>
            <input type="password" id="password" name="password" required><br><br>

            <input type="submit" value="Se connecter">
            <br>
            <a href="/register">Pas de compte ? Inscrivez-vous ici.</a>
            <a href="/">Retournez à l'accueil</a>
        </form>
    </div>
</body>
</html>

