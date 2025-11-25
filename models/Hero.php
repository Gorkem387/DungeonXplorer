<?php
require_once 'models/Database.php';
require_once 'models/Item.php';

/**
 * Hero avec des méthodes sur son inventaire. Demandez moi (Titouan) si vous avez besoin de méthodes personnalisées (gestion des items...).
 */
class Hero
{
    private $id;
    private $name;
    private $class_id;
    private $image;
    private $biography;
    private $pv;
    private $mana;
    private $strength;
    private $initiative;
    private $armor_item_id;
    private $primary_weapon_id;
    private $item_id;
    private $secondary_weapon_item_id;
    private $shield_item_id;
    private $spell_list;
    private $xp;
    private $current_level;
    private $id_utilisateur;

    public function __construct($id, $name, $class_id, $image, $biography, $pv, $mana, $strength, 
                                $initiative, $armor_item_id, $primary_weapon_id, $item_id, 
                                $secondary_weapon_item_id, $shield_item_id, $spell_list, $xp, 
                                $current_level, $id_utilisateur)
    {
        $this->id = $id;
        $this->name = $name;
        $this->class_id = $class_id;
        $this->image = $image;
        $this->biography = $biography;
        $this->pv = $pv;
        $this->mana = $mana;
        $this->strength = $strength;
        $this->initiative = $initiative;
        $this->armor_item_id = $armor_item_id;
        $this->primary_weapon_id = $primary_weapon_id;
        $this->item_id = $item_id;
        $this->secondary_weapon_item_id = $secondary_weapon_item_id;
        $this->shield_item_id = $shield_item_id;
        $this->spell_list = $spell_list;
        $this->xp = $xp;
        $this->current_level = $current_level;
        $this->id_utilisateur = $id_utilisateur;
    }

    /**
     * Récupère un héros par son ID
     */
    public static function getById($id)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, class_id, image, biography, pv, mana, strength, initiative, 
                         armor_item_id, primary_weapon_id, item_id, secondary_weapon_item_id, 
                         shield_item_id, spell_list, xp, current_level, id_utilisateur
                  FROM Hero WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Hero(
                $data['id'],
                $data['name'],
                $data['class_id'],
                $data['image'],
                $data['biography'],
                $data['pv'],
                $data['mana'],
                $data['strength'],
                $data['initiative'],
                $data['armor_item_id'],
                $data['primary_weapon_id'],
                $data['item_id'],
                $data['secondary_weapon_item_id'],
                $data['shield_item_id'],
                $data['spell_list'],
                $data['xp'],
                $data['current_level'],
                $data['id_utilisateur']
            );
        }
        
        return null;
    }

    /**
     * Récupère tous les héros d'un utilisateur
     */
    public static function getByUserId($userId)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, class_id, image, biography, pv, mana, strength, initiative, 
                         armor_item_id, primary_weapon_id, item_id, secondary_weapon_item_id, 
                         shield_item_id, spell_list, xp, current_level, id_utilisateur
                  FROM Hero WHERE id_utilisateur = :id_utilisateur";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_utilisateur', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $heroes = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $heroes[] = new Hero(
                $data['id'],
                $data['name'],
                $data['class_id'],
                $data['image'],
                $data['biography'],
                $data['pv'],
                $data['mana'],
                $data['strength'],
                $data['initiative'],
                $data['armor_item_id'],
                $data['primary_weapon_id'],
                $data['item_id'],
                $data['secondary_weapon_item_id'],
                $data['shield_item_id'],
                $data['spell_list'],
                $data['xp'],
                $data['current_level'],
                $data['id_utilisateur']
            );
        }
        
        return $heroes;
    }

    /**
     * Fait monter le héros d'un niveau et retire l'XP spécifié
     */
    public function levelUp($xpCost)
    {
        // Vérifier que le héros a assez d'XP
        if ($this->xp < $xpCost) {
            return false;
        }

        // Augmenter le niveau
        $this->current_level++;
        
        // Retirer l'XP (on ne met pas l'xp à 0 pour ne pas gâcher l'xp en supplément)
        $this->xp -= $xpCost;
        
        // Bonus de stats par niveau (A MODIFIER)
        $this->pv += 10;
        $this->strength += 2;
        $this->initiative += 1;
        
        // Sauvegarder en base de données
        $this->update();
        
        return true;
    }

    /**
     * Ajoute de l'XP au héros
     */
    public function gainXp($amount)
    {
        $this->xp += $amount;
        $this->update();
    }

    /**
     * Équipe un item dans un slot spécifique
     */
    public function switchSpecialItem($itemId, $itemType)
    {
        switch ($itemType) {
            case 'armor':
                $this->armor_item_id = $itemId;
                break;
            case 'primary_weapon':
                $this->primary_weapon_id = $itemId;
                break;
            case 'secondary_weapon':
                $this->secondary_weapon_item_id = $itemId;
                break;
            case 'shield':
                $this->shield_item_id = $itemId;
                break;
            default:
                return false;
        }
        
        $this->update();
        return true;
    }

    /**
     * Déséquipe un type special d'item (armure, arme principale, arme secondaire).
     */
    public function unequipSpecialItem($itemType)
    {
        return $this->switchSpecialItem(null, $itemType);
    }

    /**
     * Vérifie si le héros est vivant
     */
    public function isAlive()
    {
        return $this->pv > 0;
    }

    /**
     * Inflige des dégâts au héros
     */
    public function takeDamage($damage)
    {
        $this->pv -= $damage;
        if ($this->pv < 0) {
            $this->pv = 0;
        }
        $this->update();
    }

    /**
     * Soigne le héros
     */
    public function heal($amount)
    {
        $this->pv += $amount;
        // Optionnel : définir un max PV
        $this->update();
    }

    /**
     * Utilise du mana
     */
    public function useMana($amount)
    {
        if ($this->mana >= $amount) {
            $this->mana -= $amount;
            $this->update();
            return true;
        }
        return false;
    }

    /**
     * Restaure du mana
     */
    public function restoreMana($amount)
    {
        $this->mana += $amount;
        $this->update();
    }

    /**
     * Récupère l'inventaire du héros
     */
    public function getInventory()
    {
        require_once 'models/Inventory.php';
        return Inventory::getByHeroId($this->id);
    }

    /**
     * Récupère l'inventaire du héros avec les détails des items
     */
    public function getInventoryWithItems()
    {
        require_once 'models/Inventory.php';
        return Inventory::getByHeroIdWithItems($this->id);
    }

    /**
     * Sauvegarde un nouveau héros
     */
    public function save()
    {
        $db = Database::getConnection();
        
        $query = "INSERT INTO Hero (name, class_id, image, biography, pv, mana, strength, 
                                    initiative, armor_item_id, primary_weapon_id, item_id, 
                                    secondary_weapon_item_id, shield_item_id, spell_list, xp, 
                                    current_level, id_utilisateur) 
                  VALUES (:name, :class_id, :image, :biography, :pv, :mana, :strength, 
                          :initiative, :armor_item_id, :primary_weapon_id, :item_id, 
                          :secondary_weapon_item_id, :shield_item_id, :spell_list, :xp, 
                          :current_level, :id_utilisateur)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':class_id', $this->class_id, PDO::PARAM_INT);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':biography', $this->biography);
        $stmt->bindParam(':pv', $this->pv, PDO::PARAM_INT);
        $stmt->bindParam(':mana', $this->mana, PDO::PARAM_INT);
        $stmt->bindParam(':strength', $this->strength, PDO::PARAM_INT);
        $stmt->bindParam(':initiative', $this->initiative, PDO::PARAM_INT);
        $stmt->bindParam(':armor_item_id', $this->armor_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':primary_weapon_id', $this->primary_weapon_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $this->item_id, PDO::PARAM_INT);
        $stmt->bindParam(':secondary_weapon_item_id', $this->secondary_weapon_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':shield_item_id', $this->shield_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':spell_list', $this->spell_list);
        $stmt->bindParam(':xp', $this->xp, PDO::PARAM_INT);
        $stmt->bindParam(':current_level', $this->current_level, PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur', $this->id_utilisateur, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->id = $db->lastInsertId();
        
        return $this;
    }

    /**
     * Met à jour le héros
     */
    public function update()
    {
        $db = Database::getConnection();
        
        $query = "UPDATE Hero 
                  SET name = :name, class_id = :class_id, image = :image, biography = :biography,
                      pv = :pv, mana = :mana, strength = :strength, initiative = :initiative,
                      armor_item_id = :armor_item_id, primary_weapon_id = :primary_weapon_id,
                      item_id = :item_id, secondary_weapon_item_id = :secondary_weapon_item_id,
                      shield_item_id = :shield_item_id, spell_list = :spell_list, xp = :xp,
                      current_level = :current_level, id_utilisateur = :id_utilisateur
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':class_id', $this->class_id, PDO::PARAM_INT);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':biography', $this->biography);
        $stmt->bindParam(':pv', $this->pv, PDO::PARAM_INT);
        $stmt->bindParam(':mana', $this->mana, PDO::PARAM_INT);
        $stmt->bindParam(':strength', $this->strength, PDO::PARAM_INT);
        $stmt->bindParam(':initiative', $this->initiative, PDO::PARAM_INT);
        $stmt->bindParam(':armor_item_id', $this->armor_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':primary_weapon_id', $this->primary_weapon_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $this->item_id, PDO::PARAM_INT);
        $stmt->bindParam(':secondary_weapon_item_id', $this->secondary_weapon_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':shield_item_id', $this->shield_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':spell_list', $this->spell_list);
        $stmt->bindParam(':xp', $this->xp, PDO::PARAM_INT);
        $stmt->bindParam(':current_level', $this->current_level, PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur', $this->id_utilisateur, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
    }

    /**
     * Supprime le héros
     */
    public function delete()
    {
        $db = Database::getConnection();
        
        $query = "DELETE FROM Hero WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getClassId() { return $this->class_id; }
    public function getImage() { return $this->image; }
    public function getBiography() { return $this->biography; }
    public function getPv() { return $this->pv; }
    public function getMana() { return $this->mana; }
    public function getStrength() { return $this->strength; }
    public function getInitiative() { return $this->initiative; }
    public function getArmorItemId() { return $this->armor_item_id; }
    public function getPrimaryWeaponId() { return $this->primary_weapon_id; }
    public function getItemId() { return $this->item_id; }
    public function getSecondaryWeaponItemId() { return $this->secondary_weapon_item_id; }
    public function getShieldItemId() { return $this->shield_item_id; }
    public function getSpellList() { return $this->spell_list; }
    public function getXp() { return $this->xp; }
    public function getCurrentLevel() { return $this->current_level; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }

    // Setters
    public function setName($name) { $this->name = $name; }
    public function setClassId($class_id) { $this->class_id = $class_id; }
    public function setImage($image) { $this->image = $image; }
    public function setBiography($biography) { $this->biography = $biography; }
    public function setPv($pv) { $this->pv = $pv; }
    public function setMana($mana) { $this->mana = $mana; }
    public function setStrength($strength) { $this->strength = $strength; }
    public function setInitiative($initiative) { $this->initiative = $initiative; }
    public function setArmorItemId($armor_item_id) { $this->armor_item_id = $armor_item_id; }
    public function setPrimaryWeaponId($primary_weapon_id) { $this->primary_weapon_id = $primary_weapon_id; }
    public function setItemId($item_id) { $this->item_id = $item_id; }
    public function setSecondaryWeaponItemId($secondary_weapon_item_id) { $this->secondary_weapon_item_id = $secondary_weapon_item_id; }
    public function setShieldItemId($shield_item_id) { $this->shield_item_id = $shield_item_id; }
    public function setSpellList($spell_list) { $this->spell_list = $spell_list; }
    public function setXp($xp) { $this->xp = $xp; }
    public function setCurrentLevel($current_level) { $this->current_level = $current_level; }
    public function setIdUtilisateur($id_utilisateur) { $this->id_utilisateur = $id_utilisateur; }
}