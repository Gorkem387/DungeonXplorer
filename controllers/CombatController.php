<?php

class CombatController 
{
    public function start($id = null)
    {
        session_start();
        
        if (isset($_POST['chapter_id'])) {
            $_SESSION['chapter_after_combat'] = $_POST['chapter_id'];
        }
        
        if ($id !== null) {
            $_SESSION['current_hero_id'] = $id;
        }
        
        if (!isset($_SESSION['current_hero_id'])) {
            header('Location: /profil');
            exit;
        }
        
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $hero = $heroModel->findById($_SESSION['current_hero_id']);
        
        if (!$hero || !isset($hero['max_pv'])) {
            die("Erreur critique: Données de PV Max du héros introuvables. Vérifiez les tables Class et Level.");
        }
        
        require_once 'models/Monster.php';
        $monsterModel = new Monster();
        
        if (isset($_SESSION['encounter_monster_id'])) {
            $monster = $monsterModel->findById($_SESSION['encounter_monster_id']);
            unset($_SESSION['encounter_monster_id']);
        } else {
            $monster = $monsterModel->findRandom();
        }

        if (!$monster) {
            die("Aucun monstre trouvé");
        }

        $_SESSION['combat'] = [
            'hero' => $hero,
            'monster' => $monster,
            'turn' => 1,
            'hero_initiative' => rand(1, 6) + $hero['initiative'],
            'monster_initiative' => rand(1, 6) + $monster['initiative'],
            'defending' => false
        ];

        header('Location: /combat/fight');
        exit;
    }
    
    public function fight()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /profil');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];

        if ($hero['pv'] <= 0 || $monster['pv'] <= 0) {
            header('Location: /combat/end');
            exit;
        }
        
        require 'views/game/combat.php';
    }

    public function handleAction()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            echo json_encode(['error' => 'Combat non initialisé']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
            echo json_encode(['error' => 'Action invalide']);
            exit;
        }

        ob_clean();
        header('Content-Type: application/json');
        
        $action = $_POST['action'];
        $combat = $_SESSION['combat'];
        $heroActionLog = [];
        
        switch ($action) {
            case 'attack':
                $heroActionLog = $this->heroAttack($combat, 'Attaque Basique');
                break;
            case 'spell':
                $heroActionLog = $this->heroMagic($combat);
                if (end($heroActionLog)['attack_name'] === 'Échec Sort Magique') {
                     echo json_encode(['error' => 'Pas assez de mana !']);
                     exit;
                }
                break;
            case 'defend':
                $heroActionLog = $this->heroDefend($combat);
                break;
            default:
                echo json_encode(['error' => 'Type d\'action inconnu']);
                exit;
        }

        if ($combat['monster']['pv'] <= 0) {
            $_SESSION['combat'] = $combat;
            
            require_once 'models/Hero.php';
            $heroModel = new Hero();
            $heroModel->update($combat['hero']['id'], [
                'pv' => $combat['hero']['pv'],
                'mana' => $combat['hero']['mana']
            ]);

            echo json_encode([
                'success' => true,
                'actions' => $heroActionLog,
                'combat_ended' => true
            ]);
            exit;
        }
        
        $monsterActionLog = $this->monsterTurn($combat);
        $fullActionLog = array_merge($heroActionLog, $monsterActionLog);

        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $heroModel->update($combat['hero']['id'], [
            'pv' => $combat['hero']['pv'],
            'mana' => $combat['hero']['mana']
        ]);

        if ($combat['hero']['pv'] <= 0) {
            $_SESSION['combat'] = $combat;
            echo json_encode([
                'success' => true,
                'actions' => $fullActionLog,
                'combat_ended' => true
            ]);
            exit;
        }

        $_SESSION['combat'] = $combat;

        echo json_encode([
            'success' => true,
            'actions' => $fullActionLog,
            'combat_ended' => false
        ]);
    }
    
    public function getInventory()
    {
        session_start();
        
        if (!isset($_SESSION['current_hero_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Aucun héros en combat.']);
            exit;
        }

        ob_clean();
        header('Content-Type: application/json');

        $heroId = $_SESSION['current_hero_id'];

        if (!class_exists('Item')) {
            require_once 'models/Item.php';
        }
        require_once 'models/Inventory.php';
        
        try {
            $items = Inventory::getHeroItems($heroId);
            echo json_encode(['success' => true, 'inventory' => $items]);
        } catch (\PDOException $e) {
            error_log("PDO Exception lors du chargement de l'inventaire: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur SQL lors du chargement de l\'inventaire.']);
        } catch (\Error $e) {
            error_log("Erreur PHP lors du chargement de l'inventaire: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur PHP critique (Item.php manquant?).']);
        }
        exit;
    }

    private function heroAttack(&$combat, $attackName)
    {
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $log = [];

        $attaque = rand(1, 6) + $hero['strength']; 
        $defense = rand(1, 6) + (int)($monster['strength'] / 2); 
        $degats = max(0, $attaque - $defense); 
        
        $monster['pv'] -= $degats;
        $monster['pv'] = max(0, $monster['pv']);
        
        $combat['defending'] = false;
        $combat['monster'] = $monster;
        $combat['hero'] = $hero;

        $log[] = [
            'attacker' => $hero['name'], 
            'attack_name' => $attackName, 
            'damage' => $degats, 
            'target_pv_left' => $monster['pv'],
            'target' => $monster['name']
        ];
        return $log;
    }
    
    private function heroMagic(&$combat)
    {
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $log = [];
        $coutSort = 20;

        if ($hero['mana'] < $coutSort) {
            $combat['defending'] = false;
            $log[] = ['attacker' => $hero['name'], 'attack_name' => 'Échec Sort Magique', 'damage' => 0];
            return $log;
        }

        $hero['mana'] -= $coutSort;
        $attaqueMagique = (rand(1, 6) + rand(1, 6)) + $coutSort;
        $defense = rand(1, 6) + (int)($monster['strength'] / 2);
        $degats = max(0, $attaqueMagique - $defense);
        
        $monster['pv'] -= $degats;
        $monster['pv'] = max(0, $monster['pv']);
        
        $combat['defending'] = false;
        $combat['hero'] = $hero;
        $combat['monster'] = $monster;

        $log[] = [
            'attacker' => $hero['name'], 
            'attack_name' => 'Sort Magique', 
            'damage' => $degats, 
            'target_pv_left' => $monster['pv'],
            'hero_mana_left' => $hero['mana'],
            'target' => $monster['name']
        ];
        return $log;
    }

    private function heroDefend(&$combat)
    {
        $combat['defending'] = true;
        
        $log[] = ['attacker' => $combat['hero']['name'], 'attack_name' => 'Défense', 'damage' => 0, 'target_pv_left' => $combat['monster']['pv']];
        return $log;
    }
    
    private function monsterTurn(&$combat)
    {
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $log = [];

        $attaque = rand(1, 6) + $monster['strength'];
        $defense = rand(1, 6) + (int)($hero['strength'] / 2);
        
        $defenseBonus = 0;
        if (isset($combat['defending']) && $combat['defending']) {
            $defenseBonus = 5;
            $defense += $defenseBonus;
        }
        
        $degats = max(0, $attaque - $defense);
        
        $hero['pv'] -= $degats;
        $hero['pv'] = max(0, $hero['pv']);
        
        $combat['hero'] = $hero;
        $combat['defending'] = false;
        $combat['turn']++;

        $log[] = [
            'attacker' => $monster['name'], 
            'attack_name' => 'Attaque Monstre', 
            'damage' => $degats, 
            'target_pv_left' => $hero['pv'],
            'target' => $hero['name']
        ];
        return $log;
    }
    
    public function end()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /profil');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        
        $completedChapterId = $_SESSION['chapter_after_combat'] ?? null;
        
        if ($hero['pv'] > 0) {
            
        require_once 'models/Chapter.php';
            $nextChapterId = Chapter::getNextChapterAfterEncounter($completedChapterId);
            
            $resultat = [
                'winner' => 'hero',
                'xp' => $monster['xp'],
                'hero_name' => $hero['name'],
                'monster_name' => $monster['name'],
                'next_chapter_id' => $nextChapterId, 
                'has_next_chapter' => $nextChapterId !== null,
                'loot_gained' => []
            ];
            
            require_once 'models/Database.php';
            $bdd = Database::getConnection();
            
            require_once 'models/MonsterLoot.php';
            require_once 'models/Inventory.php';
            require_once 'models/Item.php';
            
            $monsterLootModel = new MonsterLoot();
            $droppedLoot = $monsterLootModel->getLoot($monster['id']);

            $maxItems = $hero['max_items'] ?? 10; 
            $currentStacks = Inventory::countItemStacks($hero['id']);
            
            $lootResults = [];

            foreach ($droppedLoot as $loot) {
                $itemId = $loot['item_id'];
                $quantity = $loot['quantity'];
                $existingItem = Inventory::hasItem($hero['id'], $itemId);
                $itemObject = Item::findById($itemId);
                
                $itemName = (is_object($itemObject) && method_exists($itemObject, 'getName')) 
                            ? $itemObject->getName() 
                            : "Objet Inconnu ({$itemId})";
                
                if (!$existingItem && $currentStacks >= $maxItems) {
                    $lootResults[] = [
                        'name' => $itemName, 
                        'quantity' => $quantity,
                        'status' => 'lost_full',
                        'message' => " (Inventaire plein : {$currentStacks}/{$maxItems}, objet perdu)"
                    ];
                    continue; 
                }
                
                $itemAdded = Inventory::addItem($hero['id'], $itemId, $quantity);
                
                if ($itemAdded) {
                    if (!$existingItem) {
                        $currentStacks++;
                    }
                    
                    $lootResults[] = [
                        'name' => $itemName, 
                        'quantity' => $quantity,
                        'status' => 'added',
                        'message' => " (Ajouté)"
                    ];
                }
            }
            
            $resultat['loot_gained'] = $lootResults; 
            $resultat['inventory_limit'] = ['current' => $currentStacks, 'max' => $maxItems];

            // Get old level before XP update
            $getOldLevel = $bdd->prepare("SELECT current_level FROM Hero WHERE id = :id");
            $getOldLevel->execute(['id' => $hero['id']]);
            $oldLevelData = $getOldLevel->fetch(PDO::FETCH_ASSOC);
            $oldLevel = $oldLevelData['current_level'];

            // Get old stats to compute actual gained values later
            $getOldStats = $bdd->prepare("SELECT pv, mana, strength, initiative FROM Hero WHERE id = :id");
            $getOldStats->execute(['id' => $hero['id']]);
            $oldStats = $getOldStats->fetch(PDO::FETCH_ASSOC);
            $oldPv = (int)($oldStats['pv'] ?? 0);
            $oldMana = (int)($oldStats['mana'] ?? 0);
            $oldStrength = (int)($oldStats['strength'] ?? 0);
            $oldInitiative = (int)($oldStats['initiative'] ?? 0);
            
            // Update XP
            $updateXp = $bdd->prepare("UPDATE Hero SET xp = xp + :xp WHERE id = :id");
            $updateXp->execute(['xp' => $monster['xp'], 'id' => $hero['id']]);
            
            // Level computed via Level table (required_xp)
            // Level computation moved to Level table lookup (required_xp)
            
            // Get new level
            // Determine new level based on the Level table (class-specific required_xp)
            $getXpAndClass = $bdd->prepare("SELECT xp, class_id FROM Hero WHERE id = :id");
            $getXpAndClass->execute(['id' => $hero['id']]);
            $heroRow = $getXpAndClass->fetch(PDO::FETCH_ASSOC);
            if ($heroRow) {
                $xp = (int)$heroRow['xp'];
                $classId = $heroRow['class_id'];

                $getLevel = $bdd->prepare("SELECT level FROM `Level` WHERE class_id = :class_id AND required_xp <= :xp ORDER BY level DESC LIMIT 1");
                $getLevel->execute(['class_id' => $classId, 'xp' => $xp]);
                $levelRow = $getLevel->fetch(PDO::FETCH_ASSOC);
                $newLevel = $levelRow ? (int)$levelRow['level'] : 1;

                $updateLevel = $bdd->prepare("UPDATE Hero SET current_level = :level WHERE id = :id");
                $updateLevel->execute(['level' => $newLevel, 'id' => $hero['id']]);
            } else {
                $newLevel = 1;
                $classId = null;
            }
            
            // If leveled up, log it and apply bonuses
            if ($newLevel > $oldLevel) {
                $insertLog = $bdd->prepare("
                    INSERT INTO Level_Up_Log (hero_id, old_level, new_level, level_up_date)
                    VALUES (:hero_id, :old_level, :new_level, NOW())
                ");
                $insertLog->execute([
                    'hero_id' => $hero['id'],
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel
                ]);
                
                // Get bonuses and apply them
                $getBonus = $bdd->prepare("
                    SELECT pv_bonus, mana_bonus, strength_bonus, initiative_bonus 
                    FROM Level 
                    WHERE class_id = :class_id AND level = :level
                ");
                $getBonus->execute(['class_id' => $classId, 'level' => $newLevel]);
                $bonus = $getBonus->fetch(PDO::FETCH_ASSOC);
                
                if ($bonus) {
                    $applyBonus = $bdd->prepare("
                        UPDATE Hero SET 
                            pv = pv + :pv_bonus,
                            mana = mana + :mana_bonus,
                            strength = strength + :strength_bonus,
                            initiative = initiative + :initiative_bonus
                        WHERE id = :id
                    ");
                    $applyBonus->execute([
                        'pv_bonus' => $bonus['pv_bonus'],
                        'mana_bonus' => $bonus['mana_bonus'],
                        'strength_bonus' => $bonus['strength_bonus'],
                        'initiative_bonus' => $bonus['initiative_bonus'],
                        'id' => $hero['id']
                    ]);
                }
                
                // Fetch new stats and compute actual gains (fallback to bonus values if provided)
                $getNewStats = $bdd->prepare("SELECT pv, mana, strength, initiative FROM Hero WHERE id = :id");
                $getNewStats->execute(['id' => $hero['id']]);
                $newStats = $getNewStats->fetch(PDO::FETCH_ASSOC);

                $pvGained = max(0, (int)($newStats['pv'] ?? 0) - $oldPv);
                $manaGained = max(0, (int)($newStats['mana'] ?? 0) - $oldMana);
                $strengthGained = max(0, (int)($newStats['strength'] ?? 0) - $oldStrength);
                $initiativeGained = max(0, (int)($newStats['initiative'] ?? 0) - $oldInitiative);
                
                // Store notification in session
                $_SESSION['level_up_notification'] = [
                    'hero_id' => $hero['id'],
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'pv_gained' => $pvGained,
                    'mana_gained' => $manaGained,
                    'strength_gained' => $strengthGained,
                    'initiative_gained' => $initiativeGained
                ];
            }
            
            // Update PV and mana
            $heroModel->update($hero['id'], [
                'pv' => $hero['pv'],
                'mana' => $hero['mana']
            ]);
            
        } else {
            $hero['pv'] = 20; 
            
            $resultat = [
                'winner' => 'monster',
                'xp' => 0,
                'hero_name' => $hero['name'],
                'monster_name' => $monster['name'],
                'has_next_chapter' => false,
            ];

            $heroModel->update($hero['id'], [
                'pv' => $hero['pv']
            ]);
        }
        
        unset($_SESSION['combat']);
        unset($_SESSION['chapter_after_combat']);

        require 'views/game/combat-end.php';
    }
    
    public function attack() { header('Location: /combat/fight'); exit; }
    public function magic() { header('Location: /combat/fight'); exit; }
    public function defend() { header('Location: /combat/fight'); exit; }
}