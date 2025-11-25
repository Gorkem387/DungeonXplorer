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
    <title>Liste des joueurs</title>
</head>
<body>
    <h1>Liste des joueurs</h1>
    <?php 
    $rep = $bdd -> query("Select * From utilisateur;");
    while ($row = $rep->fetch()){
        if ($row['perm_user']==1){
            echo "Joueur Admin n°" .$row['id'] . " : " . $row['name'] . '<br><br>';

        }
        else{
            echo "Joueur n°" .$row['id'] . " : " . $row['name'];
            ?>
            <form method="post" action="/admin/delete">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="submit" value="Supprimer">
            </form>
            <?php
            echo '<br><br>';
        }
    }
    $rep->closeCursor();    
    ?>
</body>
</html>