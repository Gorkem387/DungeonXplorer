<?php
require_once 'models/Database.php';
require_once 'models/Item.php';

/**
 * Bien comprendre que Inventory ne stoque pas tout l'inventaire d'un héro.
 * Il stoque seulement un item et sa quantité.
 * Des méthodes pour récupérer tout l'inventaire d'un héro sont présentes pour faciliter son utilisation.
 */
class Inventory
{
    private $id;
    private $hero_id;
    private $item_id;
    private $quantity;
    private $item; // Objet Item associé (optionnel)

    public function __construct($id, $hero_id, $item_id, $quantity)
    {
        $this->id = $id;
        $this->hero_id = $hero_id;
        $this->item_id = $item_id;
        $this->quantity = $quantity;
    }

    /**
     * Récupère une entrée d'inventaire par son ID
     */
    public static function findById($id)
    {
        $db = Database::getConnection();
        
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

    /**
     * Récupère tout l'inventaire d'un héros (un inventaire contient le nombre d'item du même type)
     */
    public static function getByHeroId($heroId)
    {
        $db = Database::getConnection();
        
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

    /**
     * Récupère l'inventaire d'un héros avec les détails des items
     */
    public static function getHeroItems($heroId)
    {
        $db = Database::getConnection();
        
        $query = "SELECT i.name, inv.quantity, i.description, i.item_type
                  FROM Inventory inv
                  INNER JOIN Items i ON inv.item_id = i.id
                  WHERE inv.hero_id = :hero_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        $inventoryItems = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inventory = [
                $data['name'],
                $data['quantity'],
                $data['description'],
                $data['item_type']
            ];
            $inventoryItems[] = $inventory;
        }
        
        return $inventoryItems;
    }

    /**
     * Vérifie si un héros possède un item spécifique
     */
    public static function hasItem($heroId, $itemId)
    {
        $db = Database::getConnection();
        
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

    /**
     * Ajoute un item à l'inventaire ou augmente la quantité
     */
    public static function addItem($heroId, $itemId, $quantity = 1)
    {
        $existing = self::hasItem($heroId, $itemId);
        
        if ($existing) {
            // L'item existe déjà, on augmente la quantité
            $existing->setQuantity($existing->getQuantity() + $quantity);
            $existing->update();
            return $existing;
        } else {
            // Nouvel item dans l'inventaire
            $inventory = new Inventory(null, $heroId, $itemId, $quantity);
            return $inventory->save();
        }
    }

    /**
     * Retire un item de l'inventaire ou diminue la quantité
     */
    public static function removeItem($heroId, $itemId, $quantity = 1)
    {
        $existing = self::hasItem($heroId, $itemId);
        
        if ($existing) {
            $newQuantity = $existing->getQuantity() - $quantity;
            
            if ($newQuantity <= 0) {
                // Si la quantité atteint 0 ou moins, on supprime l'entrée
                $existing->delete();
                return true;
            } else {
                // Sinon on diminue la quantité
                $existing->setQuantity($newQuantity);
                $existing->update();
                return $existing;
            }
        }
        
        return false;
    }

    /**
     * Sauvegarde une nouvelle entrée d'inventaire
     */
    public function save()
    {
        $db = Database::getConnection();
        
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

    /**
     * Met à jour une entrée d'inventaire
     */
    public function update()
    {
        $db = Database::getConnection();
        
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

    /**
     * Supprime une entrée d'inventaire
     */
    public function delete()
    {
        $db = Database::getConnection();
        
        $query = "DELETE FROM Inventory WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    /**
     * Vide tout l'inventaire d'un héros
     */
    public static function clearHeroInventory($heroId)
    {
        $db = Database::getConnection();
        
        $query = "DELETE FROM Inventory WHERE hero_id = :hero_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    // Getters
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

    // Setters
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