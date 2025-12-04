<?php
require_once 'models/Chapter.php';

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
        
        $nextChapterId = (int)$id + 1;

        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $stmt = $bdd->prepare("SELECT id FROM Chapter WHERE id = :next_id");
        $stmt->execute(['next_id' => $nextChapterId]);
        $nextChapterExists = $stmt->fetch(PDO::FETCH_ASSOC);

        $has_next_chapter = (bool)$nextChapterExists;
        $next_chapter_id = $has_next_chapter ? $nextChapterId : null;

        if (isset($_POST['hero_id'])) {
            $_SESSION['current_hero_id'] = (int) $_POST['hero_id'];
            error_log("Héros choisi pour l'aventure : " . $_SESSION['current_hero_id']);
        }
        
        error_log("Chapitre {$id} trouvé");

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
                require 'views/chapter.php';
            }
        }

        error_log("Affichage du chapitre {$id}");
        require $_SERVER['DOCUMENT_ROOT'] . '/views/chapter.php';
    }

    public function handleNext(){

        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        if (!isset($_SESSION['current_hero_id']) || !isset($_POST['id']) || !isset($_SESSION['currentChapterId'])) {
            header("Location: /");
            exit();
        }
        $currentChapterId = $_SESSION['currentChapterId'];
        $hero = $_SESSION['current_hero_id'];

        $id = htmlspecialchars($_POST['id']); 

        $update = $bdd->prepare("Update Hero_Progress set status = :status, completion_date = NOW() 
        where hero_id = :hero and chapter_id = :chapter");
                
        $update->execute(array(
            'status' => 'COMPLETED',
            'hero' => $hero,
            'chapter' => $currentChapterId
        ));

        $insert2 = $bdd->prepare("INSERT INTO Hero_Progress (hero_id, chapter_id, status) 
        VALUES (:hero, :chapter, :status)");
                
        $insert2->execute(array(
                    'hero' => $hero,
                    'chapter' => $id,
                    'status' => 'STARTED'
        ));

        /*$stmt = $bdd->prepare("SELECT level FROM Level WHERE required_xp <= :xp order by required_xp desc LIMIT 1");
        $stmt->execute(array('xp' => $currentXP));
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        $updateHero = $bdd->prepare("Update Hero set current_level = :level where id = :hero");
                
        $updateHero->execute(array(
            'level' => $level['level'],
            'hero' => $hero,
        ));*/

        header("Location: /chapter/".$id);

    }

    public function Start(){

        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        if (isset($_POST['hero_id'])) {
            $_SESSION['current_hero_id'] = (int) $_POST['hero_id'];
        }

        $hero = $_SESSION['current_hero_id'];

        $stmt = $bdd->prepare("SELECT xp FROM Hero WHERE id = :hero");
        $stmt->execute(array(
            'hero' => $hero
        ));
        $xp = $stmt->fetch(PDO::FETCH_ASSOC);
        /*if ($xp['xp'] == 0){
            $insert = $bdd->prepare("INSERT INTO Hero_Progress (hero_id, chapter_id, status) 
            VALUES (:hero, :chapter, :status)");
                
            $insert->execute(array(
                'hero' => $hero,
                'chapter' => 1,
                'status' => 'STARTED'
            ));
            header("Location: /chapter/1"); 
        }
        else{*/
            $stmt = $bdd->prepare("SELECT chapter_id FROM Hero_Progress WHERE hero_id = :hero and status = 'STARTED'");
            $stmt->execute(array(
                'hero' => $hero
            ));
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
    
            header("Location: /chapter/".$chapter['chapter_id']);
        //}    
    }
}