<?php

class Combat
{
    private $hero;
    private $monster;
    private $log = [];
    
    public function __construct($hero, $monster)
    {
        $this->hero = $hero;
        $this->monster = $monster;
    }

    public function start()
    {
        $this->log[] = "Le combat commence !";
        $this->log[] = "{$this->hero['name']} (PV: {$this->hero['pv']}) VS {$this->monster['name']} (PV: {$this->monster['pv']})";

        while ($this->hero['pv'] > 0 && $this->monster['pv'] > 0) {
            $this->tourCombat();
        }
        
        if ($this->hero['pv'] > 0) {
            $this->log[] = "Victoire ! {$this->hero['name']} a vaincu {$this->monster['name']} !";
            $xpGagne = $this->monster['xp'];
            $this->log[] = "Vous gagnez {$xpGagne} XP !";
            return ['winner' => 'hero', 'xp' => $xpGagne, 'log' => $this->log];
        } else {
            $this->log[] = "Défaite... {$this->hero['name']} a été vaincu par {$this->monster['name']}.";
            return ['winner' => 'monster', 'xp' => 0, 'log' => $this->log];
        }
    }
    
    private function tourCombat()
    {

        $initiativeHero = rand(1, 6) + $this->hero['initiative'];
        $initiativeMonster = rand(1, 6) + $this->monster['initiative'];
        
        $this->log[] = "--- Nouveau tour ---";

        if ($initiativeHero >= $initiativeMonster) {
            $this->log[] = "{$this->hero['name']} attaque en premier (initiative: {$initiativeHero})";
            $this->attaquePhysique($this->hero, $this->monster, true);

            if ($this->monster['pv'] > 0) {
                $this->log[] = "{$this->monster['name']} riposte";
                $this->attaquePhysique($this->monster, $this->hero, false);
            }
        } else {
            $this->log[] = "{$this->monster['name']} attaque en premier (initiative: {$initiativeMonster})";
            $this->attaquePhysique($this->monster, $this->hero, false);

            if ($this->hero['pv'] > 0) {
                $this->log[] = "{$this->hero['name']} riposte";
                $this->attaquePhysique($this->hero, $this->monster, true);
            }
        }
    }

    private function attaquePhysique(&$attaquant, &$defenseur, $isHero)
    {
        $attaque = rand(1, 6) + $attaquant['strength'];
        $defense = rand(1, 6) + (int)($defenseur['strength'] / 2);
        $degats = max(0, $attaque - $defense);
        
        $defenseur['pv'] -= $degats;
        $defenseur['pv'] = max(0, $defenseur['pv']);
        $nomAttaquant = $attaquant['name'];
        $nomDefenseur = $defenseur['name'];
        
        if ($degats > 0) {
            $this->log[] = "{$nomAttaquant} inflige {$degats} dégâts à {$nomDefenseur} (PV restants: {$defenseur['pv']})";
        }

        if ($isHero) {
            $this->hero = $attaquant;
            $this->monster = $defenseur;
        } else {
            $this->monster = $attaquant;
            $this->hero = $defenseur;
        }
    }

    public function getHero()
    {
        return $this->hero;
    }
}