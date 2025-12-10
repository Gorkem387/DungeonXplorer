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

        $chapterXpReward = 100;
        $updateXp = $bdd->prepare("UPDATE Hero SET xp = xp + :xp WHERE id = :hero");
        $updateXp->execute(array(
            'xp' => $chapterXpReward,
            'hero' => $hero
        ));

        $updateLevel = $bdd->prepare("
            UPDATE Hero SET current_level = CASE 
                WHEN xp >= 2700 THEN 10
                WHEN xp >= 2200 THEN 9
                WHEN xp >= 1750 THEN 8
                WHEN xp >= 1350 THEN 7
                WHEN xp >= 1000 THEN 6
                WHEN xp >= 700 THEN 5
                WHEN xp >= 450 THEN 4
                WHEN xp >= 250 THEN 3
                WHEN xp >= 100 THEN 2
                ELSE 1
            END 
            WHERE id = :hero
        ");
        $updateLevel->execute(array('hero' => $hero));

        $getHero = $bdd->prepare("SELECT current_level, class_id FROM Hero WHERE id = :hero");
        $getHero->execute(array('hero' => $hero));
        $heroData = $getHero->fetch(PDO::FETCH_ASSOC);

        if ($heroData) {
            $oldLevel = $heroData['current_level'];
            $newLevel = $heroData['current_level'];
            $classId = $heroData['class_id'];

            $getBonus = $bdd->prepare("
                SELECT pv_bonus, mana_bonus, strength_bonus, initiative_bonus 
                FROM Level 
                WHERE class_id = :class_id AND level = :level
            ");
            $getBonus->execute(array('class_id' => $classId, 'level' => $newLevel));
            $bonus = $getBonus->fetch(PDO::FETCH_ASSOC);

            if ($bonus) {
                $applyBonus = $bdd->prepare("
                    UPDATE Hero SET 
                        pv = pv + :pv_bonus,
                        mana = mana + :mana_bonus,
                        strength = strength + :strength_bonus,
                        initiative = initiative + :initiative_bonus
                    WHERE id = :hero
                ");
                $applyBonus->execute(array(
                    'pv_bonus' => $bonus['pv_bonus'],
                    'mana_bonus' => $bonus['mana_bonus'],
                    'strength_bonus' => $bonus['strength_bonus'],
                    'initiative_bonus' => $bonus['initiative_bonus'],
                    'hero' => $hero
                ));

                $getNewLevel = $bdd->prepare("SELECT current_level FROM Hero WHERE id = :hero");
                $getNewLevel->execute(array('hero' => $hero));
                $newLevelData = $getNewLevel->fetch(PDO::FETCH_ASSOC);
                $newLevel = $newLevelData['current_level'];

                if ($newLevel > $oldLevel) {
                    $insertNotif = $bdd->prepare("
                        INSERT INTO Level_Up_Log (hero_id, old_level, new_level, level_up_date)
                        VALUES (:hero_id, :old_level, :new_level, NOW())
                    ");
                    $insertNotif->execute(array(
                        'hero_id' => $hero,
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel
                    ));

                    $_SESSION['level_up_notification'] = array(
                        'hero_id' => $hero,
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'pv_gained' => $bonus['pv_bonus'],
                        'mana_gained' => $bonus['mana_bonus'],
                        'strength_gained' => $bonus['strength_bonus'],
                        'initiative_gained' => $bonus['initiative_bonus']
                    );
                }
            }
        }

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

    public function getProgressionTimeline($heroId)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $stmt = $bdd->prepare("
            SELECT id, old_level, new_level, level_up_date
            FROM Level_Up_Log
            WHERE hero_id = :hero_id
            ORDER BY level_up_date DESC
        ");
        $stmt->execute(array('hero_id' => $heroId));
        $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $timeline;
    }

    public function getLeaderboard($limit = 10)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $limit = (int)$limit;
        $stmt = $bdd->prepare("
            SELECT 
                H.id,
                H.name,
                H.current_level,
                H.xp,
                C.name as class_name,
                U.username,
                COUNT(HP.id) as chapters_completed
            FROM Hero H
            LEFT JOIN Class C ON H.class_id = C.id
            LEFT JOIN User U ON H.id_utilisateur = U.id
            LEFT JOIN Hero_Progress HP ON H.id = HP.hero_id AND HP.status = 'COMPLETED'
            GROUP BY H.id
            ORDER BY H.current_level DESC, H.xp DESC
            LIMIT $limit
        ");
        $stmt->execute();
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $leaderboard;
    }

    public function displayLeaderboard()
    {
        session_start();
        
        $leaderboard = $this->getLeaderboard(10);
        require $_SERVER['DOCUMENT_ROOT'] . '/views/leaderboard.php';
    }

    public function displayProgressionTimeline($heroId)
    {
        session_start();
        
        $timeline = $this->getProgressionTimeline($heroId);
        require $_SERVER['DOCUMENT_ROOT'] . '/views/progression_timeline.php';
    }
}