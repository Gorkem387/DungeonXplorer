<?php

class CharacterController
{
    public function index()
    {
        require 'views/character/create.php';
    }

    public function handleHero() {
        session_start();
        require_once 'models/Database.php';

        $bdd = Database::getConnection();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = htmlspecialchars($_POST['name']);
            $type = htmlspecialchars($_POST['type']);
            if (isset($_POST['desc'])){
                $description = htmlspecialchars($_POST['desc']);
            }
            else{
                $description = 'Personnage';
            }
            if (isset($_FILES['image'])){
                $image = htmlspecialchars($_POST['image']);
            }
            else{
                $image = '../public/img/guerrier.jpg';
            }

            $result_class = $bdd -> query("Select * From Class Where name = '" . $type . "';");
            $rep = $result_class->fetch(PDO::FETCH_ASSOC);

            $result_user = $bdd -> query("Select id From utilisateur Where name = '" . $_SESSION['username'] . "';");
            $util = $result_user->fetch(PDO::FETCH_ASSOC);

            $insert = $bdd -> prepare("Insert Into Hero (name, class_id, image, biography, pv, mana, strength, initiative, xp, current_level, id_utilisateur) 
            Values (:name, :class, :image, :bio, :pv, :mana, :strength, :initiative, :xp, :current_level, :id_utilisateur);");
            if ($insert->execute([
                'name' => $name,
                'class' => $rep['id'],
                'image' => $image,
                'bio' => $description,
                'pv' => $rep['base_pv'],
                'mana' => $rep['base_mana'],
                'strength' => $rep['strength'],
                'initiative' => $rep['initiative'],
                'xp' => 0,
                'current_level' => 1,
                'id_utilisateur' => $util['id']
            ])) {
                header("Location: /profile");
                exit();
            }                
            else {
            $_SESSION['error'] = "Erreur lors de l'enregistrement du hero.";
                    header("Location: /hero"); 
                    exit();
                }
        }
    }

    public function list()
    {
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $heroes = $heroModel->findAll();

        require 'views/character/list.php';
    }

}