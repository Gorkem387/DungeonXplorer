<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>Creation de votre hero</title>
</head>
<body>
    <div class="form-container">
        <form action="./traitementHero.php" method="post">
            <label for="name">Nom du hero : </label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="type">Type du hero : </label>
            <select name="type" id="type">
            <option value="">--Choisissez un type--</option>
            <option value="guerrier">Guerrier</option>
            <option value="magicien">Magicien</option>
            <option value="voleuse">Voleuse</option>
            </select>

            <label for="desc">Description : </label>
            <input type="text" id="desc" name="desc" size="255" required><br><br>

            <label for="image">Image : </label>
            <input type="image" id="image" name="image" required><br><br>

            <input type="submit" value="Se connecter">
            <br>
            <a href="./home.php">Retournez à l'accueil</a>
        </form>
    </div>
</body>
</html>