<?php

class Hero
{
    private $db;

    public function __construct()
    {
        require_once 'models/Database.php';
        $this->db = Database::getConnection();
    }

    /**
     * Récupérer tous les héros
     */
    public function findAll()
    {
        $sql = "
            SELECT Hero.*, Class.name as class_name
            FROM Hero
            LEFT JOIN Class ON Hero.class_id = Class.id
            ORDER BY Hero.id DESC
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un héros par son ID
     */
    public function findById($id)
    {
        $sql = "
            SELECT Hero.*, Class.name as class_name
            FROM Hero
            LEFT JOIN Class ON Hero.class_id = Class.id
            WHERE Hero.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les héros d'un utilisateur
     */
    public function findByUserId($userId)
    {
        $sql = "
            SELECT Hero.*, Class.name as class_name
            FROM Hero
            LEFT JOIN Class ON Hero.class_id = Class.id
            WHERE Hero.user_id = :user_id
            ORDER BY Hero.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour un héros
     */
    public function update($id, $data)
    {
        $sql = "
            UPDATE Hero
            SET pv = :pv, xp = :xp
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'pv' => $data['pv'],
            'xp' => $data['xp'],
            'id' => $id
        ]);
    }
}
