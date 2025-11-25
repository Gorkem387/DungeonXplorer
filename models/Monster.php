<?php

class Monster
{
    private $db;
    
    public function __construct()
    {
        require_once 'models/Database.php';
        $this->db = Database::getConnection();
    }
    
    /**
     * Récupérer tous les monstres
     */
    public function findAll()
    {
        $query = $this->db->query("
            SELECT Monster.*, class.name as class_name 
            FROM Monster
            LEFT JOIN class ON Monster.class_id = class.id
            ORDER BY Monster.id DESC
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un monstre par son ID
     */
    public function findById($id)
    {
        $query = $this->db->prepare("
            SELECT Monster.*, class.name as class_name 
            FROM Monster
            LEFT JOIN class ON Monster.class_id = class.id
            WHERE Monster.id = :id
        ");
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un monstre aléatoire
     */
    public function findRandom()
    {
        $query = $this->db->query("
            SELECT * FROM Monster
            ORDER BY RAND()
            LIMIT 1
        ");
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
