<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout des monstres</title>
</head>
<body>
<h1>Liste des monstres</h1>
    <?php
    $rep = $bdd -> query("Select id, name, pv, mana, initiative, strength, attack, xp From Monster;");
    while ($row = $rep->fetch()){
        echo $row['id'] . " :";
        echo 'Nom : ' . $row['name'] ;
        echo 'Points de vie : ' . $row['pv'] ;
        echo 'Nombre de mana : ' . $row['mana'] ;
        echo 'Nombre d\'initiative : ' . $row['strength'] ;
        echo 'Puissance : ' . $row['name'] ;
        echo 'Point d\'attaque : ' . $row['attack'] ;
        echo 'Xp donnée : ' . $row['xp'] ;
        echo '<br><br>';
    ?>
    <form method="post" action="/admin/monsters/modify">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" value="Modifier">
    </form>
    <form method="post" action="/admin/monsters/delete">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" value="Supprimer">
    </form>
    <?php
        echo '<br><br>';  
    }
    $rep->closeCursor();    
    ?>
    <form method="post" action="/admin/monsters/add">
        <input type="submit" value="Ajouter un monstre">
    </form> 

</body>
</html>