<?php 
    session_start();
    require_once 'models/Database.php';
    $bdd = Database::getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout de chapitres</title>
</head>
<body>
    <h1>Liste des chapitres</h1>
    <?php
    $rep = $bdd -> query("Select * From Chapter;");
    while ($row = $rep->fetch()){
        echo $row['id'] . " : " . $row['content'] . '<br><br>';
    ?>
    <form method="post" action="/admin/chapter/modify">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" value="Modifier">
    </form>
    <form method="post" action="/admin/chapter/delete">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" value="Supprimer">
    </form>
    <?php
        echo '<br><br>';  
    }
    $rep->closeCursor();    
    ?>
    <form method="post" action="/admin/chapter/add">
        <input type="submit" value="Ajouter un chapitre">
    </form> 
</body>
</html>