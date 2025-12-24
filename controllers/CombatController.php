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

        $_SESSION['combat'] = [
            'hero' => $hero,
            'monster' => $monster,
            'turn' => 1,
            'hero_initiative' => rand(1, 6) + $hero['initiative'],
            'monster_initiative' => rand(1, 6) + $monster['initiative'],
            'defending' => false,
            'equipped_weapon' => null // Aucune arme équipée au début
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
        
        // Récupérer l'item
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
        
        // ÉQUIPER UNE ARME
        if ($itemAction === 'equip' && $item['damage_multiplier'] > 1.0) {
            $combat['equipped_weapon'] = $item;
            $combat['defending'] = false;
            $log[] = [
                'attacker' => $combat['hero']['name'],
                'attack_name' => 'Équipe ' . $item['name'],
                'damage' => 0,
                'target_pv_left' => $combat['monster']['pv'],
                'target' => 'Équipement'
            ];
            return $log;
        }
        
        // UTILISER UN CONSOMMABLE
        if ($itemAction === 'use') {
            
            // POTION DE SOIN
            if ($item['is_heal']) {
                $maxPv = $this->getMaxPvByClass($combat['hero']['class_id']);
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
            
            // FLÈCHE (nécessite arc équipé)
            if (stripos($item['name'], 'flèche') !== false) {
                $equippedWeapon = $combat['equipped_weapon'] ?? null;
                
                if (!$equippedWeapon || $equippedWeapon['requires_ammo'] == 0) {
                    return ['error' => 'Vous devez équiper un arc pour tirer une flèche'];
                }
                
                $damage = $this->calculateDamage($combat['hero'], $equippedWeapon);
                $dodgeChance = 20 + $equippedWeapon['dodge_modifier'];
                
                if (rand(1, 100) <= $dodgeChance) {
                    $log[] = [
                        'attacker' => $combat['hero']['name'],
                        'attack_name' => 'Tir de flèche',
                        'damage' => 0,
                        'target_pv_left' => $combat['monster']['pv'],
                        'target' => $combat['monster']['name'],
                        'message' => 'Le monstre esquive!'
                    ];
                } else {
                    $combat['monster']['pv'] -= $damage;
                    $combat['monster']['pv'] = max(0, $combat['monster']['pv']);
                    
                    $log[] = [
                        'attacker' => $combat['hero']['name'],
                        'attack_name' => 'Tir de flèche',
                        'damage' => $damage,
                        'target_pv_left' => $combat['monster']['pv'],
                        'target' => $combat['monster']['name']
                    ];
                }
                
                $this->consumeItem($heroId, $itemId, 1);
                $combat['defending'] = false;
                return $log;
            }
            
            // FIOLE DE POISON (nécessite arc + flèche)
            if (stripos($item['name'], 'fiole') !== false || stripos($item['name'], 'poison') !== false) {
                $equippedWeapon = $combat['equipped_weapon'] ?? null;
                
                if (!$equippedWeapon || $equippedWeapon['requires_ammo'] == 0) {
                    return ['error' => 'Vous devez équiper un arc pour utiliser une fiole de poison'];
                }
                
                // Vérifier si le héros a une flèche
                $arrowQuery = $bdd->prepare("
                    SELECT i.id, hi.quantity 
                    FROM Items i 
                    JOIN Inventory hi ON i.id = hi.item_id 
                    WHERE hi.hero_id = ? AND i.name LIKE '%flèche%' OR i.name LIKE '%Flèche%'
                ");
                $arrowQuery->execute([$heroId]);
                $arrow = $arrowQuery->fetch(PDO::FETCH_ASSOC);
                
                if (!$arrow || $arrow['quantity'] <= 0) {
                    return ['error' => 'Vous n\'avez pas de flèche pour utiliser la fiole de poison'];
                }
                
                // Attaque imparable avec dégâts fixes
                $damage = $item['damage']; // 100 dégâts
                $combat['monster']['pv'] -= $damage;
                $combat['monster']['pv'] = max(0, $combat['monster']['pv']);
                
                $this->consumeItem($heroId, $itemId, 1); // Consommer la fiole
                $this->consumeItem($heroId, $arrow['id'], 1); // Consommer une flèche
                $combat['defending'] = false;
                
                $log[] = [
                    'attacker' => $combat['hero']['name'],
                    'attack_name' => 'Flèche empoisonnée',
                    'damage' => $damage,
                    'target_pv_left' => $combat['monster']['pv'],
                    'target' => $combat['monster']['name'],
                    'message' => 'Coup critique! Le poison fait effet!'
                ];
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
    
    private function calculateDamage($hero, $weapon)
    {
        $baseDamage = $weapon['damage'] + $hero['strength'];
        $finalDamage = $baseDamage * $weapon['damage_multiplier'];
        return (int)$finalDamage;
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
                $itemObject = Item::findById($itemId);
                
                $itemName = (is_object($itemObject) && method_exists($itemObject, 'getName')) 
                            ? $itemObject->getName() 
                            : "Objet Inconnu ({$itemId})";
                
                if (!$existingItem && $currentStacks >= $maxItems) {
                    $lootResults[] = [
                        'name' => $itemName,
                        'quantity' => $quantity,
                        'status' => 'lost_full',
                        'message' => " (Inventaire plein)"
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
        }
    }
}