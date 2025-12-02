<?php

class ProfileController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $heros = $this->getHeroesByUserId($_SESSION['user_id']);

        require 'views/profile/index.php';
    }

    private function getHeroesByUserId($userId)
    {
        $bdd = Database::getConnection();
        $stmt = $bdd->prepare("
            SELECT h.*, c.name as class_name, c.image as class_image
            FROM Hero h
            LEFT JOIN Class c ON h.class_id = c.id
            WHERE h.id_utilisateur = :user_id
            ORDER BY h.id DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }

    public function getCharacterDetails()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Non authentifié']);
            exit();
        }
        
        if (isset($_GET['id'])) {
            require_once 'models/Database.php';
            $bdd = Database::getConnection();

            $stmt = $bdd->prepare("
                SELECT h.*, c.name as class_name, c.image as class_image
                FROM Hero h
                LEFT JOIN Class c ON h.class_id = c.id
                WHERE h.id = :id AND h.id_utilisateur = :user_id
            ");
            $stmt->execute([
                'id' => $_GET['id'],
                'user_id' => $_SESSION['user_id']
            ]);
            $character = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($character) {
                echo json_encode([
                    'name' => $character['name'],
                    'class_name' => $character['class_name'],
                    'chapter' => 'Chapitre ' . $character['current_level'],
                    'pv' => $character['pv'],
                    'initiative' => $character['initiative'],
                    'strength' => $character['strength'],
                    'mana' => $character['mana'],
                ]);
            } else {
                echo json_encode(['error' => 'Personnage non trouvé']);
            }
        } else {
            echo json_encode(['error' => 'ID manquant']);
        }
    }
}