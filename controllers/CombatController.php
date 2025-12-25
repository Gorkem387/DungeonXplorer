<?php

class CombatController 
{
    public function start($id = null)
    {
        session_start();
        
        if ($id !== null) {
            $_SESSION['current_hero_id'] = $id;
        }
        
        if (isset($_POST['chapter_id'])) {
            $_SESSION['chapter_after_combat'] = $_POST['chapter_id'];
        }
        
        if (!isset($_SESSION['current_hero_id'])) {
            header('Location: /profil');
            exit;
        }
        
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $hero = $heroModel->findById($_SESSION['current_hero_id']);
        
        if (!$hero || !isset($hero['max_pv'])) {
            die("Erreur critique: Données de PV Max du héros introuvables.");
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

        if (!isset($_SESSION['permanent_equipment'])) {
            $_SESSION['permanent_equipment'] = [1 => null, 2 => null, 3 => null, 4 => null];
        }

        $_SESSION['combat'] = [
            'hero' => $hero,
            'monster' => $monster,
            'turn' => 1,
            'hero_initiative' => rand(1, 6) + $hero['initiative'],
            'monster_initiative' => rand(1, 6) + $monster['initiative'],
            'defending' => false,
            'equipment' => $_SESSION['permanent_equipment'],
            'equipped_weapon' => $_SESSION['permanent_equipment'][4] ?? null 
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
                $heroActionLog = $this->heroAttack($combat);
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
            case 'use_item':
                $heroActionLog = $this->useItem($combat, $_POST['item_id'], $_POST['item_action']);
                if (isset($heroActionLog['error'])) {
                    echo json_encode($heroActionLog);
                    exit;
                }
                break;
            default:
                echo json_encode(['error' => 'Type d\'action inconnu']);
                exit;
        }

        if ($combat['monster']['pv'] <= 0) {
            $_SESSION['combat'] = $combat;
            $this->saveHeroState($combat['hero']);
            echo json_encode([
                'success' => true,
                'actions' => $heroActionLog,
                'combat_ended' => true
            ]);
            exit;
        }
        
        $monsterActionLog = $this->monsterTurn($combat);
        $fullActionLog = array_merge($heroActionLog, $monsterActionLog);
        $this->saveHeroState($combat['hero']);

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
    
    private function useItem(&$combat, $itemId, $itemAction)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();
        
        $heroId = $_SESSION['current_hero_id'];
        
        $itemQuery = $bdd->prepare("
            SELECT i.*, hi.quantity 
            FROM Items i 
            JOIN Inventory hi ON i.id = hi.item_id 
            WHERE i.id = ? AND hi.hero_id = ?
        ");
        $itemQuery->execute([$itemId, $heroId]);
        $item = $itemQuery->fetch(PDO::FETCH_ASSOC);
        
        if (!$item || $item['quantity'] <= 0) {
            return ['error' => 'Item introuvable ou quantité insuffisante'];
        }
        
        $log = [];
        $type = (int)$item['item_type'];

        // --- LOGIQUE ÉQUIPEMENT (Slots 1 à 4) ---
        if ($itemAction === 'equip' || ($type >= 1 && $type <= 4)) {
            if (!isset($_SESSION['permanent_equipment'])) {
                $_SESSION['permanent_equipment'] = [1 => null, 2 => null, 3 => null, 4 => null];
            }
            // SAUVEGARDE PERSISTANTE
            $_SESSION['permanent_equipment'][$type] = $item;
            
            // MISE À JOUR COMBAT ACTUEL
            $combat['equipment'][$type] = $item;
            if ($type === 4) { $combat['equipped_weapon'] = $item; }
            
            $combat['defending'] = false;

            $log[] = [
                'attacker' => $combat['hero']['name'],
                'attack_name' => 'Équipe ' . $item['name'],
                'target' => 'Équipement',
                'slot' => $type,
                'item_name' => $item['name'],
                'damage' => 0
            ];
            return $log;
        }
        
        // --- LOGIQUE CONSOMMABLE ---
        if ($itemAction === 'use' || $type == 10) {
            
            if ($item['is_heal']) {
                // Utiliser le max_pv du héros chargé au début
                $maxPv = $combat['hero']['max_pv'];
                $healAmount = min($item['damage'], $maxPv - $combat['hero']['pv']);
                $combat['hero']['pv'] += $healAmount;
                
                $this->consumeItem($heroId, $itemId, 1);
                $combat['defending'] = false;
                
                $log[] = [
                    'attacker' => $combat['hero']['name'],
                    'attack_name' => 'Utilise ' . $item['name'],
                    'damage' => -$healAmount,
                    'target_pv_left' => $combat['hero']['pv'],
                    'target' => $combat['hero']['name']
                ];
                return $log;
            }
            
            // Logique Flèches et Poison
            if (stripos($item['name'], 'flèche') !== false) {
                $equippedWeapon = $combat['equipped_weapon'] ?? null;
                if (!$equippedWeapon || $equippedWeapon['requires_ammo'] == 0) {
                    return ['error' => 'Vous devez équiper un arc pour tirer une flèche'];
                }
                $damage = $this->calculateDamage($combat['hero'], $equippedWeapon);
                $dodgeChance = 20 + $equippedWeapon['dodge_modifier'];
                if (rand(1, 100) <= $dodgeChance) {
                    $log[] = ['attacker' => $combat['hero']['name'], 'attack_name' => 'Tir de flèche', 'damage' => 0, 'target_pv_left' => $combat['monster']['pv'], 'target' => $combat['monster']['name'], 'message' => 'Le monstre esquive!'];
                } else {
                    $combat['monster']['pv'] = max(0, $combat['monster']['pv'] - $damage);
                    $log[] = ['attacker' => $combat['hero']['name'], 'attack_name' => 'Tir de flèche', 'damage' => $damage, 'target_pv_left' => $combat['monster']['pv'], 'target' => $combat['monster']['name']];
                }
                $this->consumeItem($heroId, $itemId, 1);
                $combat['defending'] = false;
                return $log;
            }
        }
        
        return ['error' => 'Action impossible avec cet item'];
    }
    
    private function consumeItem($heroId, $itemId, $quantity)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();
        
        // Décrémenter la quantité
        $updateQuery = $bdd->prepare("
            UPDATE Inventory 
            SET quantity = quantity - ? 
            WHERE hero_id = ? AND item_id = ?
        ");
        $updateQuery->execute([$quantity, $heroId, $itemId]);
        
        // Supprimer si quantité = 0
        $deleteQuery = $bdd->prepare("
            DELETE FROM Inventory 
            WHERE hero_id = ? AND item_id = ? AND quantity <= 0
        ");
        $deleteQuery->execute([$heroId, $itemId]);
    }
    
    private function calculateDamage($hero, $equippedWeapon) {
        $baseDamage = rand(1, 6) + $hero['strength'];
        if ($equippedWeapon && isset($equippedWeapon['damage_multiplier'])) {
            return (int)($baseDamage * $equippedWeapon['damage_multiplier']);
        }
        return $baseDamage;
    }
    
    private function getArmorReduction($heroId)
    {
        require_once 'models/Database.php';
        $bdd = Database::getConnection();
        
        $armorQuery = $bdd->prepare("
            SELECT SUM(i.damage) as total_armor 
            FROM Items i 
            JOIN Inventory hi ON i.id = hi.item_id 
            WHERE hi.hero_id = ? AND i.is_armor = 1
        ");
        $armorQuery->execute([$heroId]);
        $result = $armorQuery->fetch(PDO::FETCH_ASSOC);
        
        return min(100, (int)($result['total_armor'] ?? 0)); // Max 100%
    }
    
    private function getMaxPvByClass($classId)
    {
        switch ($classId) {
            case 1: return 150; // Guerrier
            case 2: return 100; // Magicien
            case 3: return 120; // Voleur
            default: return 100;
        }
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
            error_log("PDO Exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur SQL']);
        } catch (\Error $e) {
            error_log("Erreur PHP: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur PHP']);
        }
        exit;
    }

    private function heroAttack(&$combat)
    {
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $log = [];
        
        $equippedWeapon = $combat['equipped_weapon'] ?? null;
        
        if ($equippedWeapon && $equippedWeapon['damage_multiplier'] > 1.0) {
            // Attaque avec arme équipée
            $damage = $this->calculateDamage($hero, $equippedWeapon);
            $dodgeChance = 20 + $equippedWeapon['dodge_modifier'];
            
            if (rand(1, 100) <= $dodgeChance) {
                $log[] = [
                    'attacker' => $hero['name'],
                    'attack_name' => 'Attaque avec ' . $equippedWeapon['name'],
                    'damage' => 0,
                    'target_pv_left' => $monster['pv'],
                    'target' => $monster['name'],
                    'message' => 'Le monstre esquive!'
                ];
            } else {
                $monster['pv'] -= $damage;
                $monster['pv'] = max(0, $monster['pv']);
                
                $log[] = [
                    'attacker' => $hero['name'],
                    'attack_name' => 'Attaque avec ' . $equippedWeapon['name'],
                    'damage' => $damage,
                    'target_pv_left' => $monster['pv'],
                    'target' => $monster['name']
                ];
            }
        } else {
            // Attaque à mains nues
            $attaque = rand(1, 6) + $hero['strength'];
            $defense = rand(1, 6) + (int)($monster['strength'] / 2);
            $degats = max(0, $attaque - $defense);
            
            $monster['pv'] -= $degats;
            $monster['pv'] = max(0, $monster['pv']);
            
            $log[] = [
                'attacker' => $hero['name'],
                'attack_name' => 'Attaque à mains nues',
                'damage' => $degats,
                'target_pv_left' => $monster['pv'],
                'target' => $monster['name']
            ];
        }
        
        $combat['defending'] = false;
        $combat['monster'] = $monster;
        $combat['hero'] = $hero;

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
        
        $log[] = [
            'attacker' => $combat['hero']['name'],
            'attack_name' => 'Défense',
            'damage' => 0,
            'target_pv_left' => $combat['monster']['pv']
        ];
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
        
        // Appliquer réduction armure
        $armorReduction = $this->getArmorReduction($_SESSION['current_hero_id']);
        $degatsFinaux = (int)($degats * (1 - $armorReduction / 100));
        
        $hero['pv'] -= $degatsFinaux;
        $hero['pv'] = max(0, $hero['pv']);
        
        $combat['hero'] = $hero;
        $combat['defending'] = false;
        $combat['turn']++;

        $log[] = [
            'attacker' => $monster['name'],
            'attack_name' => 'Attaque Monstre',
            'damage' => $degatsFinaux,
            'target_pv_left' => $hero['pv'],
            'target' => $hero['name'],
            'armor_reduced' => $armorReduction > 0 ? $armorReduction : null
        ];
        return $log;
    }
    
    private function saveHeroState($hero)
    {
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $heroModel->update($hero['id'], [
            'pv' => $hero['pv'],
            'mana' => $hero['mana']
        ]);
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
                
                $itemQuery = $bdd->prepare("SELECT name FROM Items WHERE id = ?");
                $itemQuery->execute([$itemId]);
                $itemData = $itemQuery->fetch(PDO::FETCH_ASSOC);
                $itemName = $itemData['name'] ?? "Objet Inconnu ({$itemId})";
                
                if (!$existingItem && $currentStacks >= $maxItems) {
                    $lootResults[] = [
                        'name' => $itemName,
                        'quantity' => $quantity,
                        'message' => " (Inventaire plein)"
                    ];
                    continue;
                }
                
                if (Inventory::addItem($hero['id'], $itemId, $quantity)) {
                    if (!$existingItem) $currentStacks++;
                    $lootResults[] = [
                        'name' => $itemName,
                        'quantity' => $quantity,
                        'message' => " (Ajouté)"
                    ];
                }
            }
            
            $resultat['loot_gained'] = $lootResults;

            $newXp = $hero['xp'] + $monster['xp'];
            $heroModel->update($hero['id'], ['xp' => $newXp]);

        } 
        else {
            $resultat = [
                'winner' => 'monster',
                'hero_name' => $hero['name'],
                'monster_name' => $monster['name']
            ];
            $heroModel->update($hero['id'], ['pv' => 0]);
        }

        unset($_SESSION['combat']);
        unset($_SESSION['chapter_after_combat']);
        
        require 'views/game/combat-end.php';
    }
}