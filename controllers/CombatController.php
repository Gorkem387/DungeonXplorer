<?php

class CombatController 
{
    public function start($id = null)
    {
        session_start();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Si un id est passé via la route, le stocker en session
        if ($id !== null) {
            $_SESSION['current_hero_id'] = $id;
        }

        // Vérifier qu'un héros est choisi
        if (!isset($_SESSION['current_hero_id'])) {
            header('Location: /character/list');
            exit;
        }

        // Récupération du héros
        require_once 'models/Hero.php';
        $heroModel = new Hero();
        $hero = $heroModel->findById($_SESSION['current_hero_id']);
        if (!$hero) {
            die("Héros introuvable avec l'id {$_SESSION['current_hero_id']}");
        }

        // Récupération du monstre aléatoire
        require_once 'models/Monster.php';
        $monsterModel = new Monster();
        $monster = $monsterModel->findRandom();
        if (!$monster) {
            die("Aucun monstre trouvé");
        }

        // Lancer le combat
        require_once 'models/Combat.php';
        $combat = new Combat($hero, $monster);
        $resultat = $combat->start();

        // Vérifier que le résultat est valide
        if (!$resultat || !isset($resultat['log'])) {
            die("Erreur lors du combat");
        }

        // Mettre à jour le héros après combat
        $heroApres = $combat->getHero();
        $heroModel->update($hero['id'], [
            'pv' => $heroApres['pv'],
            'xp' => $hero['xp'] + $resultat['xp']
        ]);

        // Afficher la vue combat
        require 'views/game/combat.php';
    }
}
