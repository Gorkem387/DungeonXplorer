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
        
        error_log("Chapitre {$id} trouvé");

        if (Chapter::hasEncounter($id)) {
            error_log("Encounter détecté pour le chapitre {$id}");

            $encounter = Chapter::getEncounterWithMonster($id);
            
            if ($encounter) {
                error_log("Monstre de l'encounter: " . $encounter['monster_id'] . " - " . $encounter['name']);
                
                $_SESSION['chapter_after_combat'] = $id;
                $_SESSION['encounter_monster_id'] = $encounter['monster_id'];

                if (!isset($_SESSION['current_hero_id'])) {
                    error_log("Pas de héros actif, redirection vers /character/list");
                    $_SESSION['error'] = "Vous devez choisir un héros avant d'entrer dans ce chapitre !";
                    header("Location: /profil");
                    exit();
                }
                
                error_log("Redirection vers /combat/start/" . $encounter['id']);
                header("Location: /combat/start/" . $_SESSION['current_hero_id']);
                exit();
            }
        } else {
            error_log("Pas d'encounter pour le chapitre {$id}");
        }

        error_log("Affichage normal du chapitre {$id}");
        require $_SERVER['DOCUMENT_ROOT'] . '/views/chapter.php';
    }
}