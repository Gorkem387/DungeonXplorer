<?php
    session_start();
    require_once '../models/Database.php';

    $bdd = Database::getConnection();


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = htmlspecialchars($_POST['name']);
        $type = htmlspecialchars($_POST['type']);
        $description = htmlspecialchars($_POST['desc']);
        $image = htmlspecialchars($_POST['image']);

        $rep = $bdd -> query("Select id From class Where name = '" . $name . "';");

        $insert = $bdd -> prepare("Insert Into hero (name, , mdp) Values (:name, :perm, :password);");
        if ($insert -> execute(array(
            'name' => $username,
            'perm' => '0',
            'password' => $hashed_password
        ))){
            $_SESSION['username'] = $username;
            header("Location: ./hero.php");
            exit();
        }                
        else {
        $_SESSION['error'] = "Erreur lors de l'enregistrement de l'utilisateur.";
                header("Location: ./register.php"); 
                exit();
            }
        }
        else {
            $_SESSION['error'] = "Nom d'utilisateur déjà utilisé.";
            header("Location: ./register.php"); 
            exit();
        }
?>