<?php

class CombatController 
{
    public function start($id = null)
    {
        session_start();
        
        if ($id !== null) {
            $_SESSION['current_hero_id'] = $id;
        }
        
        if (!isset($_SESSION['current_hero_id'])) {
            header('Location: /character/list');
            exit;
        }
        
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $hero = $heroModel->findById($_SESSION['current_hero_id']);
        
        if (!$hero) {
            die("Héros introuvable");
        }
        
        require_once 'models/Monster.php';
        $monsterModel = new Monster();
        //$_SESSION['encounter_monster_id'] = $encounter['monster_id'];
        
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
            'log' => [
                "Le combat commence !",
                "{$hero['name']} (PV: {$hero['pv']}) VS {$monster['name']} (PV: {$monster['pv']})"
            ],
            'turn' => 1,
            'hero_initiative' => rand(1, 6) + $hero['initiative'],
            'monster_initiative' => rand(1, 6) + $monster['initiative']
        ];

        header('Location: /combat/fight');
        exit;
    }
    
    public function fight()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /character/list');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $log = $combat['log'];

        if ($hero['pv'] <= 0 || $monster['pv'] <= 0) {
            header('Location: /combat/end');
            exit;
        }
        
        require 'views/game/combat.php';
    }
    
    public function attack()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /character/list');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        
        $attaque = rand(1, 6) + $hero['strength'];
        $defense = rand(1, 6) + (int)($monster['strength'] / 2);
        $degats = $hero['strength'];
        
        $monster['pv'] -= $degats;
        $monster['pv'] = max(0, $monster['pv']);
        
        if ($degats > 0) {
            $combat['log'][] = "{$hero['name']} attaque et inflige {$degats} dégâts à {$monster['name']} !";
        } else {
            $combat['log'][] = "{$monster['name']} bloque l'attaque de {$hero['name']} !";
        }
        
        $combat['monster'] = $monster;

        if ($monster['pv'] <= 0) {
            $_SESSION['combat'] = $combat;
            header('Location: /combat/end');
            exit;
        }
        
        $this->monsterTurn($combat);
        
        $_SESSION['combat'] = $combat;
        header('Location: /combat/fight');
        exit;
    }
    
    public function magic()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /character/list');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        $coutSort = 20;
        
        if ($hero['mana'] < $coutSort) {
            $combat['log'][] = "Pas assez de mana ! (Il vous faut {$coutSort} mana)";
            $_SESSION['combat'] = $combat;
            header('Location: /combat/fight');
            exit;
        }

        $hero['mana'] -= $coutSort;
        $attaqueMagique = (rand(1, 6) + rand(1, 6)) + $coutSort;
        $defense = rand(1, 6) + (int)($monster['strength'] / 2);
        $degats = $hero['strength'];
        
        $monster['pv'] -= $degats;
        $monster['pv'] = max(0, $monster['pv']);
        
        $combat['log'][] = "{$hero['name']} lance un sort et inflige {$degats} dégâts magiques !";
        $combat['log'][] = "Mana restant : {$hero['mana']}";
        
        $combat['hero'] = $hero;
        $combat['monster'] = $monster;

        if ($monster['pv'] <= 0) {
            $_SESSION['combat'] = $combat;
            header('Location: /combat/end');
            exit;
        }

        $this->monsterTurn($combat);
        
        $_SESSION['combat'] = $combat;
        header('Location: /combat/fight');
        exit;
    }
    
    private function monsterTurn(&$combat)
    {
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        
        $attaque = rand(1, 6) + $monster['strength'];
        $defense = rand(1, 6) + (int)($hero['strength'] / 2);
        
        if (isset($combat['defending']) && $combat['defending']) {
            $defense += 5;
        }
        
        $degats = $monster['strength'];
        
        $hero['pv'] -= $degats;
        $hero['pv'] = max(0, $hero['pv']);
        
        if ($degats > 0) {
            $combat['log'][] = "{$monster['name']} attaque et inflige {$degats} dégâts à {$hero['name']} !";
        } else {
            $combat['log'][] = "{$hero['name']} bloque l'attaque de {$monster['name']} !";
        }
        
        $combat['hero'] = $hero;
        $combat['turn']++;
    }
    
    public function end()
    {
        session_start();
        
        if (!isset($_SESSION['combat'])) {
            header('Location: /character/list');
            exit;
        }
        
        $combat = $_SESSION['combat'];
        $hero = $combat['hero'];
        $monster = $combat['monster'];
        
        if ($hero['pv'] > 0) {
            $resultat = [
                'winner' => 'hero',
                'xp' => $monster['xp'],
                'log' => array_merge($combat['log'], [
                    "Victoire ! {$hero['name']} a vaincu {$monster['name']} !",
                    "Vous gagnez {$monster['xp']} XP !"
                ])
            ];
            
            require_once 'models/Hero.php';
            $heroModel = new Hero();
            $heroModel->update($hero['id'], [
                'pv' => $hero['pv'],
                'mana' => $hero['mana'],
                'xp' => $hero['xp'] + $monster['xp']
            ]);
            
        } else {
            $resultat = [
                'winner' => 'monster',
                'xp' => 0,
                'log' => array_merge($combat['log'], [
                    "Défaite... {$hero['name']} a été vaincu par {$monster['name']}."
                ])
            ];
        }
        
        unset($_SESSION['combat']);
        require 'views/game/combat-end.php';
    }
}