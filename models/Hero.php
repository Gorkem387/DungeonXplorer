<?php

class Hero
{
    private $db;
    
    public function __construct()
    {
        require_once 'models/Database.php';
        $this->db = Database::getConnection();
    }
    
    public function findAll()
    {
        $query = $this->db->query("
            SELECT Hero.*, c.name as class_name 
            FROM Hero 
            LEFT JOIN Class c ON Hero.class_id = c.id
            ORDER BY Hero.id DESC
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id)
    {
        $query = $this->db->prepare("
            SELECT 
                H.*, 
                C.name as class_name, 
                C.base_pv, 
                C.base_mana,
                L.pv_bonus,
                L.mana_bonus
            FROM Hero H 
            LEFT JOIN Class C ON H.class_id = C.id
            LEFT JOIN Level L ON L.class_id = H.class_id AND L.level = H.current_level
            WHERE H.id = :id
        ");
        $query->execute(['id' => $id]);
        $hero = $query->fetch(PDO::FETCH_ASSOC);

        if ($hero) {
            $basePv = $hero['base_pv'] ?? 0;
            $bonusPv = $hero['pv_bonus'] ?? 0;

            $baseMana = $hero['base_mana'] ?? 0;
            $bonusMana = $hero['mana_bonus'] ?? 0;

            $hero['max_pv'] = $basePv + $bonusPv;
            $hero['max_mana'] = $baseMana + $bonusMana;

            $hero['pv'] = min($hero['pv'], $hero['max_pv']);
            $hero['mana'] = min($hero['mana'], $hero['max_mana']);
        }
        
        return $hero;
    }
    
    public function findByUserId($userId)
    {
        $query = $this->db->prepare("
            SELECT Hero.*, c.name as class_name 
            FROM Hero 
            LEFT JOIN Class c ON Hero.class_id = c.id
            WHERE Hero.id_utilisateur = :user_id
            ORDER BY Hero.id DESC
        ");
        $query->execute(['user_id' => $userId]);
        $heroes = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return $heroes;
    }
    
    public function create($data)
    {
        try {
            $query = $this->db->prepare("
                INSERT INTO Hero (
                    name, class_id, image, biography, pv, mana, 
                    strength, initiative, current_level, xp, id_utilisateur
                )
                VALUES (
                    :name, :class_id, :image, :biography, :pv, :mana, 
                    :strength, :initiative, :current_level, :xp, :id_utilisateur
                )
            ");
            
            $result = $query->execute([
                'name' => $data['name'],
                'class_id' => $data['class_id'],
                'image' => $data['image'] ?? null,
                'biography' => $data['biography'] ?? null,
                'pv' => $data['pv'],
                'mana' => $data['mana'],
                'strength' => $data['strength'],
                'initiative' => $data['initiative'],
                'current_level' => $data['current_level'] ?? 1,
                'xp' => $data['xp'] ?? 0,
                'id_utilisateur' => $data['id_utilisateur']
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur création héros : " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data)
    {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            
            $setClause = implode(', ', $fields);
            
            $query = $this->db->prepare("
                UPDATE Hero 
                SET {$setClause}
                WHERE id = :id
            ");
            
            return $query->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour héros : " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id)
    {
        try {
            $query = $this->db->prepare("DELETE FROM Hero WHERE id = :id");
            return $query->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur suppression héros : " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePv($id, $newPv)
    {
        return $this->update($id, ['pv' => $newPv]);
    }
    
    public function updateMana($id, $newMana)
    {
        return $this->update($id, ['mana' => $newMana]);
    }
    
    public function addXp($id, $xpToAdd)
    {
        $hero = $this->findById($id);
        if ($hero) {
            $newXp = $hero['xp'] + $xpToAdd;
            return $this->update($id, ['xp' => $newXp]);
        }
        return false;
    }
    
    public function belongsToUser($heroId, $userId)
    {
        $query = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM Hero 
            WHERE id = :hero_id AND id_utilisateur = :user_id
        ");
        $query->execute([
            'hero_id' => $heroId,
            'user_id' => $userId
        ]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}