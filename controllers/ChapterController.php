<?php
require_once 'models/Chapter.php';
require_once 'models/Encounter.php';

class ChapterController
{
    public function show($id)
    {
        session_start();
        
        if (!isset($_SESSION['username'])) {
            header("Location: /login");
            exit();
        }

        $chapter = Chapter::findById($id);
        
        if (!$chapter) {
            http_response_code(404);
            echo "Chapitre non trouvé!";
            return;
        }

        if (isset($_POST['hero_id'])) {
            $_SESSION['current_hero_id'] = (int) $_POST['hero_id'];
        }
        
        $hasEncounter = Chapter::hasEncounter($id);
        $encounter = null;
        
        if ($hasEncounter) {
            $encounter = Chapter::getEncounterWithMonster($id);
            
            if (!isset($_SESSION['current_hero_id'])) {
                $_SESSION['error'] = "Veuillez sélectionner un héros avant de commencer un combat.";
                header("Location: /profil");
                exit();
            }
        }

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

        header("Location: /chapter/".$id);

    }

    public function Start(){

        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        if (isset($_POST['hero_id'])) {
            $_SESSION['current_hero_id'] = (int) $_POST['hero_id'];
        }

        if (!isset($_SESSION['current_hero_id'])) {
            header("Location: /profil");
            exit();
        }

        $hero = $_SESSION['current_hero_id'];

        $stmt = $bdd->prepare("SELECT xp FROM Hero WHERE id = :hero");
        $stmt->execute(array(
            'hero' => $hero
        ));
        $xp = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $bdd->prepare("SELECT chapter_id FROM Hero_Progress WHERE hero_id = :hero and status = 'STARTED'");
        $stmt->execute(array(
            'hero' => $hero
        ));
        $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

        $targetChapterId = null;

        if ($chapter) {
            $targetChapterId = $chapter['chapter_id'];
        } else {
            $startChapterId = 1;
            
            $insert = $bdd->prepare("INSERT INTO Hero_Progress (hero_id, chapter_id, status) 
            VALUES (:hero, :chapter, :status)");
            
            $insert->execute(array(
                'hero' => $hero,
                'chapter' => $startChapterId,
                'status' => 'STARTED'
            ));
            $targetChapterId = $startChapterId;
        }

        if ($targetChapterId) {
            header("Location: /chapter/".$targetChapterId);
        } else {
            header("Location: /profil");
        }
        exit();
    }
}