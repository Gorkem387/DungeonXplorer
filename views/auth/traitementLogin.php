<?php
    session_start();
    require_once '../../models/Database.php';

    $bdd = Database::getConnection();


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);

        $rep = $bdd -> query("Select mdp From utilisateur Where name = '" . $username . "';");

        if ($rep->rowCount() > 0) {
            $data = $rep->fetch();
            $hashed_password = $data['mdp'];

            if (password_verify($password, $hashed_password)) {
                $_SESSION['username'] = $username;
                header("Location: ./creationHero.php");
            } else {
                $_SESSION['error'] = "Mot de passe incorrect";
                header("Location: ./login.php"); 
                exit();
            }
        } else {
            $_SESSION['error'] = "Nom d'utilisateur non trouvé";
            header("Location: ./login.php"); 
            exit();
            
        }
    }
?>