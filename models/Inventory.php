<?php
require_once 'models/Database.php';
require_once 'models/Item.php';

class Inventory
{
    private $id;
    private $hero_id;
    private $item_id;
    private $quantity;
    private $item;

    public function __construct($id, $hero_id, $item_id, $quantity)
    {
        $this->id = $id;
        $this->hero_id = $hero_id;
        $this->item_id = $item_id;
        $this->quantity = $quantity;
    }

    public static function findById($id)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return null; }
        
        $query = "SELECT id, hero_id, item_id, quantity FROM Inventory WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Inventory(
                $data['id'],
                $data['hero_id'],
                $data['item_id'],
                $data['quantity']
            );
        }
        
        return null;
    }

    public static function getByHeroId($heroId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return []; }
        
        $query = "SELECT id, hero_id, item_id, quantity FROM Inventory WHERE hero_id = :hero_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        $inventoryItems = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inventoryItems[] = new Inventory(
                $data['id'],
                $data['hero_id'],
                $data['item_id'],
                $data['quantity']
            );
        }
        
        return $inventoryItems;
    }

    public static function getHeroItems($heroId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { 
             return []; 
        }
        
        $query = "SELECT 
                i.id as item_id, 
                i.name, 
                i.description, 
                i.image,
                inv.quantity
              FROM Inventory inv
              INNER JOIN Items i ON inv.item_id = i.id
              WHERE inv.hero_id = :hero_id AND inv.quantity > 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        $inventoryItems = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inventoryItems[] = $data;
        }
        
        return $inventoryItems;
    }

    public static function hasItem($heroId, $itemId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return null; }
        
        $query = "SELECT id, hero_id, item_id, quantity 
                  FROM Inventory 
                  WHERE hero_id = :hero_id AND item_id = :item_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Inventory(
                $data['id'],
                $data['hero_id'],
                $data['item_id'],
                $data['quantity']
            );
        }
        
        return null;
    }

    public static function addItem($heroId, $itemId, $quantity = 1)
    {
        $existing = self::hasItem($heroId, $itemId);
        
        if ($existing) {
            $existing->setQuantity($existing->getQuantity() + $quantity);
            $existing->update();
            return $existing;
        } else {
            $inventory = new Inventory(null, $heroId, $itemId, $quantity);
            return $inventory->save();
        }
    }

    public static function removeItem($heroId, $itemId, $quantity = 1)
    {
        $existing = self::hasItem($heroId, $itemId);
        
        if ($existing) {
            $newQuantity = $existing->getQuantity() - $quantity;
            
            if ($newQuantity <= 0) {
                $existing->delete();
                return true;
            } else {
                $existing->setQuantity($newQuantity);
                $existing->update();
                return $existing;
            }
        }
        
        return false;
    }

    public function save()
    {
        $db = Database::getConnection();
        
        if ($db === null) { return $this; }
        
        $query = "INSERT INTO Inventory (hero_id, item_id, quantity) 
                  VALUES (:hero_id, :item_id, :quantity)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $this->hero_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $this->item_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $this->quantity, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->id = $db->lastInsertId();
        
        return $this;
    }

    public function update()
    {
        $db = Database::getConnection();
        
        if ($db === null) { return $this; }
        
        $query = "UPDATE Inventory 
                  SET hero_id = :hero_id, item_id = :item_id, quantity = :quantity 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':hero_id', $this->hero_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $this->item_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $this->quantity, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
    }

    public function delete()
    {
        $db = Database::getConnection();
        
        if ($db === null) { return false; }
        
        $query = "DELETE FROM Inventory WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    public static function clearHeroInventory($heroId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return false; }
        
        $query = "DELETE FROM Inventory WHERE hero_id = :hero_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    public static function countItemStacks($heroId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return 0; }
        
        $query = "SELECT COUNT(id) FROM Inventory WHERE hero_id = :hero_id AND quantity > 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHeroId()
    {
        return $this->hero_id;
    }

    public function getItemId()
    {
        return $this->item_id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getItem()
    {
        if ($this->item === null) {
            $this->item = Item::findById($this->item_id); 
        }
        return $this->item;
    }

    public function setHeroId($hero_id)
    {
        $this->hero_id = $hero_id;
    }

    public function setItemId($item_id)
    {
        $this->item_id = $item_id;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }
}