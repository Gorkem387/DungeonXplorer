<?php

class CombatController 
{
    public function start($id = null)
    {
        session_start();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
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
            die("Héros introuvable avec l'id {$_SESSION['current_hero_id']}");
        }

        require_once 'models/Monster.php';
        $monsterModel = new Monster();
        $monster = $monsterModel->findRandom();
        if (!$monster) {
            die("Aucun monstre trouvé");
        }

        require_once 'models/Combat.php';
        $combat = new Combat($hero, $monster);
        $resultat = $combat->start();

        if (!$resultat || !isset($resultat['log'])) {
            die("Erreur lors du combat");
        }

        $heroApres = $combat->getHero();
        $heroModel->update($hero['id'], [
            'pv' => $heroApres['pv'],
            'xp' => $hero['xp'] + $resultat['xp']
        ]);

        require 'views/game/combat.php';
    }
}
