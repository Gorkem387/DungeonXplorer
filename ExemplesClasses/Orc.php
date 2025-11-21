<?php

// models/Orc.php

class Orc extends Monster
{
    public function __construct()
    {
        $treasure = ['gold' => 20, 'armor' => 'armure en cuir'];
        parent::__construct('Orc', 60, 0, 100, $treasure);
    }

    public function attack()
    {
        return "{$this->name} vous charge avec sa massue.";
    }
}
