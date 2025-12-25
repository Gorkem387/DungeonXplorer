<?php
require_once 'models/Chapter.php';

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
            $_SESSION['encounter_monster_id'] = $encounter['monster_id'];
            
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

        $chapterXpReward = 20;
        $updateXp = $bdd->prepare("UPDATE Hero SET xp = xp + :xp WHERE id = :hero");
        $updateXp->execute(array(
            'xp' => $chapterXpReward,
            'hero' => $hero
        ));

        $getOldLevel = $bdd->prepare("SELECT current_level FROM Hero WHERE id = :hero");
        $getOldLevel->execute(array('hero' => $hero));
        $oldLevelRow = $getOldLevel->fetch(PDO::FETCH_ASSOC);
        $oldLevel = $oldLevelRow ? (int)$oldLevelRow['current_level'] : 0;

        $getOldStats = $bdd->prepare("SELECT pv, mana, strength, initiative FROM Hero WHERE id = :hero");
        $getOldStats->execute(['hero' => $hero]);
        $oldStats = $getOldStats->fetch(PDO::FETCH_ASSOC);
        $oldPv = (int)($oldStats['pv'] ?? 0);
        $oldMana = (int)($oldStats['mana'] ?? 0);
        $oldStrength = (int)($oldStats['strength'] ?? 0);
        $oldInitiative = (int)($oldStats['initiative'] ?? 0);

        $getXpAndClass = $bdd->prepare("SELECT xp, class_id FROM Hero WHERE id = :hero");
        $getXpAndClass->execute(['hero' => $hero]);
        $heroRow = $getXpAndClass->fetch(PDO::FETCH_ASSOC);
        if ($heroRow) {
            $xp = (int)$heroRow['xp'];
            $classId = $heroRow['class_id'];

            $getLevel = $bdd->prepare("SELECT level FROM `Level` WHERE class_id = :class_id AND required_xp <= :xp ORDER BY level DESC LIMIT 1");
            $getLevel->execute(['class_id' => $classId, 'xp' => $xp]);
            $levelRow = $getLevel->fetch(PDO::FETCH_ASSOC);
            $newLevel = $levelRow ? (int)$levelRow['level'] : 1;

            $updateLevel = $bdd->prepare("UPDATE Hero SET current_level = :level WHERE id = :hero");
            $updateLevel->execute(['level' => $newLevel, 'hero' => $hero]);
        } else {
            $newLevel = 1;
            $classId = null;
        }

        $heroData = ['current_level' => $newLevel, 'class_id' => $classId];

        if ($heroData) {
            $newLevel = (int)$heroData['current_level'];
            $classId = $heroData['class_id'];

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

                    $getNewStats = $bdd->prepare("SELECT pv, mana, strength, initiative FROM Hero WHERE id = :hero");
                    $getNewStats->execute(['hero' => $hero]);
                    $newStats = $getNewStats->fetch(PDO::FETCH_ASSOC);

                    $pvGained = max(0, (int)($newStats['pv'] ?? 0) - $oldPv);
                    $manaGained = max(0, (int)($newStats['mana'] ?? 0) - $oldMana);
                    $strengthGained = max(0, (int)($newStats['strength'] ?? 0) - $oldStrength);
                    $initiativeGained = max(0, (int)($newStats['initiative'] ?? 0) - $oldInitiative);

                    $_SESSION['level_up_notification'] = array(
                        'hero_id' => $hero,
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'pv_gained' => $pvGained,
                        'mana_gained' => $manaGained,
                        'strength_gained' => $strengthGained,
                        'initiative_gained' => $initiativeGained
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

    public function getHeroStats($heroId)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $stmt = $bdd->prepare("SELECT COUNT(*) FROM Hero_Progress WHERE hero_id = :hero AND status = 'COMPLETED'");
        $stmt->execute(['hero' => $heroId]);
        $chaptersCompleted = (int)$stmt->fetchColumn();

        $stmt = $bdd->prepare("SELECT COUNT(*) FROM Level_Up_Log WHERE hero_id = :hero");
        $stmt->execute(['hero' => $heroId]);
        $levelsGained = (int)$stmt->fetchColumn();

        $stmt = $bdd->prepare("SELECT id, name, current_level, xp, pv, mana, strength, initiative FROM Hero WHERE id = :hero");
        $stmt->execute(['hero' => $heroId]);
        $heroRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $heroName = $heroRow ? $heroRow['name'] : null;
        $heroCurrentLevel = $heroRow ? (int)$heroRow['current_level'] : null;
        $xpCurrent = $heroRow ? (int)$heroRow['xp'] : 0;
        $pvCurrent = $heroRow ? (int)$heroRow['pv'] : 0;
        $manaCurrent = $heroRow ? (int)$heroRow['mana'] : 0;
        $strengthCurrent = $heroRow ? (int)$heroRow['strength'] : 0;
        $initiativeCurrent = $heroRow ? (int)$heroRow['initiative'] : 0;

        return [
            'hero_id' => $heroId,
            'hero_name' => $heroName,
            'hero_level' => $heroCurrentLevel,
            'chapters_completed' => $chaptersCompleted,
            'levels_gained' => $levelsGained,
            'xp_current' => $xpCurrent,
            'pv' => $pvCurrent,
            'mana' => $manaCurrent,
            'strength' => $strengthCurrent,
            'initiative' => $initiativeCurrent
        ];
    }

    public function getLeaderboard($limit = 10)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $limit = (int)$limit;
            $sql = "SELECT
                    H.id,
                    H.name,
                    H.current_level,
                    H.xp,
                    COALESCE(C.name, 'Inconnu') as class_name,
                    COALESCE(U.name, 'N/A') as username,
                    COUNT(HP.id) as chapters_completed
                FROM Hero H
                LEFT JOIN Class C ON H.class_id = C.id
                LEFT JOIN utilisateur U ON H.id_utilisateur = U.id
                LEFT JOIN Hero_Progress HP ON H.id = HP.hero_id AND HP.status = 'COMPLETED'
                WHERE H.name IS NOT NULL AND TRIM(H.name) <> ''
                GROUP BY H.id
                ORDER BY H.current_level DESC, H.xp DESC
                LIMIT $limit";

            $stmt = $bdd->prepare($sql);
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
        $stats = $this->getHeroStats($heroId);
        require $_SERVER['DOCUMENT_ROOT'] . '/views/progression_timeline.php';
    }

    public function collect() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
    
        if (isset($_SESSION['current_hero_id']) && isset($_POST['chapter_id'])) {
            $chapterId = (int)$_POST['chapter_id'];
            $heroId = $_SESSION['current_hero_id'];
    
            Chapter::processTreasureCollection($heroId, $chapterId);

            header("Location: /chapter/" . $chapterId);
            exit;
        }
        header("Location: /");
        exit;
    }
}