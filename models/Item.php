<?php
require_once 'models/Database.php';

class Item
{
    private $id;
    private $name;
    private $description;
    private $item_type;

    public function __construct($id, $name, $description, $item_type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->item_type = $item_type;
    }

    public static function findById($id)
    {
        $db = Database::getConnection();

        if ($db === null) { return null; }
        
        $query = "SELECT id, name, description, item_type FROM Items WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Item(
                $data['id'],
                $data['name'],
                $data['description'],
                $data['item_type']
            );
        }
        
        return null;
    }

    public static function findAll()
    {
        $db = Database::getConnection();

        if ($db === null) { return []; }
        
        $query = "SELECT id, name, description, item_type FROM Items";
        $stmt = $db->query($query);
        
        $items = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new Item(
                $data['id'],
                $data['name'],
                $data['description'],
                $data['item_type']
            );
        }
        
        return $items;
    }

    public static function findByChapterId($chapterId)
    {
        $db = Database::getConnection();

        if ($db === null) { return []; }
        
        $query = "SELECT i.id, i.name, i.description, i.item_type 
                  FROM Items i
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
                $data['item_type']
            );
        }
        
        return $items;
    }

    public function save()
    {
        $db = Database::getConnection();

        if ($db === null) { return $this; }
        
        $query = "INSERT INTO Items (name, description, item_type) 
                  VALUES (:name, :description, :item_type)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':item_type', $this->item_type);
        $stmt->execute();
        
        $this->id = $db->lastInsertId();
        
        return $this;
    }

    public function update()
    {
        $db = Database::getConnection();

        if ($db === null) { return $this; }
        
        $query = "UPDATE Items 
                  SET name = :name, description = :description, item_type = :item_type 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type', $this->type);
        $stmt->execute();
        
        return $this;
    }

    public function delete()
    {
        $db = Database::getConnection();

        if ($db === null) { return false; }
        
        $query = "DELETE FROM Items WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

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
        return $this->item_type;
    }

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
        $this->item_type = $item_type;
    }
}