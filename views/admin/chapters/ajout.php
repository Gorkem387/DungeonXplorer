<?php 
    session_start();
    require_once 'models/Database.php';
    $bdd = Database::getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/login.css">
    <title>Creation de votre hero</title>
</head>
<body>
    <div class="form-container">
        <form action="/admin/chapter/add/add" method="post">
            <label for="desc">Description : </label>
            <input type="text" id="desc" name="desc" size="255"><br><br>
            
            <label for="image">Image : </label>
            <input type="file" id="image" name="image" accept="image/*"><br><br>
            <p>Les chapitres precedents : </p><br>
            <?php
            $rep = $bdd -> query("Select * From Chapter;");
            while ($row = $rep->fetch()){?>
                <input type="checkbox" name="precedent[]" value="<?php echo $row['id'];?>" /> <?php echo $row['id'];?>
            <?php
            }
            $rep->closeCursor();    
            ?>
            <p>Les chapitres suivants : </p><br>
            <?php
            $rep = $bdd -> query("Select * From Chapter;");
            while ($row = $rep->fetch()){?>
                <input type="checkbox" name="prochain[]" value="<?php echo $row['id'];?>" /> <?php echo $row['id'];?>
            <?php
            }
            $rep->closeCursor();    
            ?>
            <br>

            <input type="submit" value="Ajouter le chapitre">
        </form>
    </div>
</body>
</html>