<?php
require_once 'models/Chapter.php';
require_once 'models/Encounter.php';

class ChapterController
{
    public function show($id)
    {
        session_start();
        
        error_log("ChapterController::show");
        error_log("ID demandé: " . $id);
        
        if (!isset($_SESSION['username'])) {
            error_log("Utilisateur non connecté, redirection vers /login");
            header("Location: /login");
            exit();
        }

        $chapter = Chapter::findById($id);
        
        if (!$chapter) {
            error_log("Chapitre {$id} non trouvé en base");
            http_response_code(404);
            echo "Chapitre non trouvé!";
            return;
        }

        if (isset($_GET['hero_id'])) {
            $_SESSION['current_hero_id'] = (int) $_GET['hero_id'];
            error_log("Héros choisi pour l'aventure : " . $_SESSION['current_hero_id']);
        }
        
        error_log("Chapitre {$id} trouvé");

        // Vérifier si c'est un encounter
        $hasEncounter = Chapter::hasEncounter($id);
        $encounter = null;
        
        if ($hasEncounter) {
            error_log("Encounter détecté pour le chapitre {$id}");
            $encounter = Chapter::getEncounterWithMonster($id);
            
            if (!isset($_SESSION['current_hero_id'])) {
                require_once 'models/Hero.php';
                $heroModel = new Hero();
                $heroes = $heroModel->findByUserId($_SESSION['user_id']);
                if (!empty($heroes)) {
                    $_SESSION['current_hero_id'] = $heroes[0]['id'];
                    error_log("Héros actif automatiquement sélectionné : " . $heroes[0]['id']);
                } else {
                    $_SESSION['error'] = "Vous devez créer un héros avant d'entrer dans ce chapitre !";
                    header("Location: /profil");
                    exit();
                }
            }
        }

        error_log("Affichage du chapitre {$id}");
        require $_SERVER['DOCUMENT_ROOT'] . '/views/chapter.php';
    }
}