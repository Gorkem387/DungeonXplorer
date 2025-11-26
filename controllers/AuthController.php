<?php
class AuthController {
    
    public function showLogin() {
        require 'views/auth/login.php';
    }
    
    public function showRegister() {
        session_start();
        require 'views/auth/register.php';
    }
    
    public function handleRegister() {
        session_start();
        require_once 'models/Database.php';
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
                if (strlen($password) < 8 ){
                    $_SESSION['error'] = "Le mot de passe doit faire au moins 8 caractères.";
                    header("Location: /register"); 
                    exit();
                }
                $boolean = 0;
                for ($i = 0; $i < strlen($password); $i = $i + 1){
                    if ($password[$i] <= 10 && $password[$i] >=  0){
                        $boolean = 1;
                    }
                }
                if ($boolean == false){
                    $_SESSION['error'] = "Le mot de passe doit contenir au moins un chiffre";
                    header("Location: /register"); 
                    exit();
                }
                if ($password != $password2) {
                    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                    header("Location: /register"); 
                    exit();
                }
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert = $bdd->prepare("INSERT INTO utilisateur (name, perm_user, mdp) VALUES (:name, :perm, :password)");
                
                if ($insert->execute(array(
                    'name' => $username,
                    'perm' => '0',
                    'password' => $hashed_password
                ))){
                    $_SESSION['username'] = $username;
                    header("Location: /hero");
                    exit();
                }                
                else {
                    $_SESSION['error'] = "Erreur lors de l'enregistrement de l'utilisateur.";
                    header("Location: /register"); 
                    exit();
                }
            }
            else {
                $_SESSION['error'] = "Nom d'utilisateur déjà utilisé.";
                header("Location: /register"); 
                exit();
            }
        }
    }
    public function handleLogin() {
        session_start();
        require_once 'models/Database.php';

        $bdd = Database::getConnection();


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);

            $rep = $bdd -> query("Select mdp, perm_user From utilisateur Where name = '" . $username . "';");

            if ($rep->rowCount() > 0) {
                $data = $rep->fetch();
                $hashed_password = $data['mdp'];

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['perm'] = $data['perm_user'];
                    if ($data['perm_user']==1){
                        header("Location: /admin");
                    }
                    else{
                        header("Location: /hero");
                    }
                    
                } else {
                    $_SESSION['error'] = "Mot de passe incorrect";
                    header("Location: /login"); 
                    exit();
                }
            } else {
                $_SESSION['error'] = "Nom d'utilisateur non trouvé";
                header("Location: /login"); 
                exit();
                
            }
        }
    }
}