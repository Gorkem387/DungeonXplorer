<?php
    session_start();
    require_once '../../models/Database.php';

    $bdd = Database::getConnection();


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $password2 = htmlspecialchars($_POST['password2']);

        $query = "SELECT COUNT(*) FROM utilisateur WHERE name = :name";
        $stmt = $bdd->prepare($query);
        $stmt->bindParam(':name', $username, PDO::PARAM_STR);
        $stmt->execute();
        $existingUser = $stmt->fetchColumn();

        if ($existingUser == 0) {
            if ($password != $password2) {
                $_SESSION['error'] =  "Les mots de passe ne correspondent pas.";
                header("Location: ./register.php"); 
                exit();
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $bdd -> prepare("Insert Into utilisateur (name, perm_user, mdp) Values (:name, :perm, :password);");
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
    }
?>