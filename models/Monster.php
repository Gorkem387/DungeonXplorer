<?php

class Monster
{
    private $db;
    
    public function __construct()
    {
        require_once 'models/Database.php';
        $this->db = Database::getConnection();
    }
    
    public function findById($id)
    {
        $query = $this->db->prepare("
            SELECT *
            FROM Monster
            WHERE id = :id
        ");
        $query->execute(['id' => $id]);
        $monster = $query->fetch(PDO::FETCH_ASSOC);

        if ($monster) {
            $monster['max_pv'] = $monster['pv']; 
        }
        
        return $monster;
    }
    
    public function findRandom()
    {
        $query = $this->db->query("
            SELECT *
            FROM Monster
            ORDER BY RAND()
            LIMIT 1
        ");
        $monster = $query->fetch(PDO::FETCH_ASSOC);

        if ($monster) {
            $monster['max_pv'] = $monster['pv']; 
        }
        
        return $monster;
    }
}