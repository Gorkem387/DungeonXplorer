<?php
require_once 'models/Chapter.php';
class ChapterController
{
    public function show($id)
    {
        $chapter = Chapter::findById($id);
        if ($chapter) {
            require $_SERVER['DOCUMENT_ROOT'] . '/views/chapter.php';
        } else {
            http_response_code(404);
            echo "Chapitre non trouvé!";
        }
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