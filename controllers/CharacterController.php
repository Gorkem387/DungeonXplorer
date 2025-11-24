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
            if (isset($_POST['image'])){
                $image = htmlspecialchars($_POST['image']);
            }
            else{
                $image = '../public/img/Wizard.jpg';
            }

            $rep = $bdd -> query("Select id From class Where name = '" . $name . "';");

            $util = $bdd -> query("Select id From utilisateur Where name = '" . $_SESSION['username'] . "';");


            $insert = $bdd -> prepare("Insert Into hero (name, class_id, image, biography, pv, mana, strength, initiative, xp, current_level, id_utilisateur) 
            Values (:name, :class, :image, :bio, :pv, :mana, :strength, :initiative, :xp, :current_level, id_utilisateur);");
            if ($insert -> execute(array(
                'name' => $name,
                'class' => $_rep[''],
                'image' => $image,
                'bio' => $description,
                'pv' => $rep[''],
                'mana' => $rep[''],
                'strength' => $rep[''],
                'initiative' => $rep[''],
                'xp' => '0',
                'current_level' => '1',
                'id_utilisateur' => $util['']
            ))){
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
}