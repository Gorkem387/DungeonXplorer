<?php
require_once 'models/Database.php';

class Item
{
    private $id;
    private $name;
    private $description;
    private $type;

    public function __construct($id, $name, $description, $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
    }

    /**
     * Récupère un item par son ID
     */
    public static function findById($id)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, description, type FROM Item WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Item(
                $data['id'],
                $data['name'],
                $data['description'],
                $data['type']
            );
        }
        
        return null;
    }

    /**
     * Récupère tous les items
     */
    public static function findAll()
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, description, type FROM Item";
        $stmt = $db->query($query);
        
        $items = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new Item(
                $data['id'],
                $data['name'],
                $data['description'],
                $data['type']
            );
        }
        
        return $items;
    }

    /**
     * Récupère les items d'un chapitre spécifique
     */
    public static function findByChapterId($chapterId)
    {
        $db = Database::getConnection();
        
        $query = "SELECT i.id, i.name, i.description, i.type 
                  FROM Item i
                  INNER JOIN Chapter_Item ci ON i.id = ci.item_id
                  WHERE ci.chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $chapterId, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new Item(
                $data['id'],
                $data['name'],
                $data['description'],
                $data['type']
            );
        }
        
        return $items;
    }

    /**
     * Sauvegarde un nouvel item
     */
    public function save()
    {
        $db = Database::getConnection();
        
        $query = "INSERT INTO Item (name, description, type) 
                  VALUES (:name, :description, :type)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type', $this->type);
        $stmt->execute();
        
        $this->id = $db->lastInsertId();
        
        return $this;
    }

    /**
     * Met à jour un item existant
     */
    public function update()
    {
        $db = Database::getConnection();
        
        $query = "UPDATE Item 
                  SET name = :name, description = :description, type = :type 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type', $this->type);
        $stmt->execute();
        
        return $this;
    }

    /**
     * Supprime un item
     */
    public function delete()
    {
        $db = Database::getConnection();
        
        $query = "DELETE FROM Item WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getType()
    {
        return $this->type;
    }

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}