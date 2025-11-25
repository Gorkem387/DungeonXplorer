<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/login.css">
    <title>Modification d'un chapitre</title>
</head>
<body>
    <div class="form-container">
        <form action="/admin/chapter/modify/modify" method="post">
            <p><?php echo $_SESSION['id_chapter']?></p>
            <label for="desc">Description : </label>
            <input type="text" id="desc" name="desc" size="255" placeholder="<?php echo $_SESSION['description']?>"><br><br>

            <label for="image">Changer Image : </label>
            <input type="file" id="image" name="image" accept="image/*"><br><br>

            <input type="submit" value="Modifier le chapitre">
        </form>
    </div>
</body>
</html>