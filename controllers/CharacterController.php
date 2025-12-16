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
                $image = basename(htmlspecialchars($_POST['image']));
                $image = '../public/img/personnage/' . $image;
            }
            else{
                $image = '../public/img/personnage/Wizard.jpg';
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
                header("Location: /profil");
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

    public function delete()
    {
        session_start();
        require_once 'models/Database.php';
        require_once 'models/Hero.php';

        $bdd = Database::getConnection();
        $heroModel = new Hero();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profil');
            exit();
        }

        if (!isset($_SESSION['username']) || !isset($_POST['hero_id'])) {
            $_SESSION['error'] = 'Action non autorisée.';
            header('Location: /profil');
            exit();
        }

        $heroId = (int)$_POST['hero_id'];

        // Verify ownership
        $stmtUser = $bdd->prepare("SELECT id FROM utilisateur WHERE name = :name");
        $stmtUser->execute(['name' => $_SESSION['username']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $userId = $user ? $user['id'] : null;

        if (!$userId || !$heroModel->belongsToUser($heroId, $userId)) {
            $_SESSION['error'] = 'Vous ne pouvez pas supprimer ce personnage.';
            header('Location: /profil');
            exit();
        }

        try {
            $bdd->beginTransaction();

            // Remove related data if exists
            $stmt = $bdd->prepare("DELETE FROM Inventory WHERE hero_id = :hero");
            $stmt->execute(['hero' => $heroId]);

            $stmt = $bdd->prepare("DELETE FROM Hero_Progress WHERE hero_id = :hero");
            $stmt->execute(['hero' => $heroId]);

            $stmt = $bdd->prepare("DELETE FROM Level_Up_Log WHERE hero_id = :hero");
            $stmt->execute(['hero' => $heroId]);

            // Finally delete hero
            if (!$heroModel->delete($heroId)) {
                throw new Exception('Erreur suppression héros');
            }

            $bdd->commit();
            $_SESSION['success'] = 'Personnage supprimé avec succès.';
        } catch (Exception $e) {
            $bdd->rollBack();
            error_log('Erreur deletion hero: ' . $e->getMessage());
            $_SESSION['error'] = 'Une erreur est survenue lors de la suppression.';
        }

        header('Location: /profil');
        exit();
    }

}