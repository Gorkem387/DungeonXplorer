<?php

class MonsterLoot
{
    private $db;
    
    public function __construct()
    {
        require_once 'models/Database.php';
        $this->db = Database::getConnection();
    }
    
    public function getLoot($monsterId)
    {
        $query = $this->db->prepare("
            SELECT item_id, quantity, drop_rate
            FROM Monster_Loot
            WHERE monster_id = :monster_id
        ");
        $query->execute(['monster_id' => $monsterId]);
        $possibleLoot = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $droppedLoot = [];
        
        foreach ($possibleLoot as $loot) {
            if (rand(1, 100) <= $loot['drop_rate']) {
                $droppedLoot[] = [
                    'item_id' => $loot['item_id'],
                    'quantity' => $loot['quantity']
                ];
            }
        }
        
        return $droppedLoot;
    }
}