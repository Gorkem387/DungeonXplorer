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

    public function handleNext(){

        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $id = htmlspecialchars($_POST['id']); 

        $updateHero = $bdd->prepare("Update Hero set xp = xp + :xp where id = $hero");
                
        $updateHero->execute(array(
            'xp' => $id
        ));

        $update = $bdd->prepare("Update Hero_Progress set status = :status and completion_date = sysdate where hero_id = $hero and chapter_id = $chapterID");
                
        $update->execute(array(
                    'status' => 'COMPLETED'
        ));

        $insert2 = $bdd->prepare("INSERT INTO Hero_Progress (hero_id, chapter_id, status) VALUES (:hero, :chapter, :status)");
                
        $insert2->execute(array(
                    'hero' => $hero,
                    'chapter' => $id,
                    'status' => 'STARTED'
        ));

        $stmt = $bdd->prepare("SELECT level FROM Level WHERE ");
        $stmt->execute(['name' => $username]);
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        $updateHero = $bdd->prepare("Update Hero set current_level = :level where id = $hero");
                
        $updateHero->execute(array(
            'level' => $level['level']
        ));

        header("Location: /chapter/".$id);

    }

    public function Start(){

        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $hero = 25;
        echo "Chapitre non trouvé!";

        $stmt = $bdd->prepare("SELECT xp FROM Hero WHERE id = :hero");
        $stmt->execute(array(
            'hero' => $hero
        ));
        $xp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($xp['xp'] == 0){
            header("Location: /chapter/1"); 
        }
        else{
            $stmt = $bdd->prepare("SELECT chapter_id FROM Level WHERE hero_id = :hero and status = 'STARTED'");
            $stmt->execute(array(
                'hero' => $hero
            ));
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
    
            header("Location: /chapter/".$chapter['chapter_id']);
        }    
    }
}