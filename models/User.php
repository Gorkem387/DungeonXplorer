<?php
require_once 'models/Database.php';

class User
{
    private $id;
    private $name;
    private $perm_user;
    private $mdp;

    public function __construct($id, $name, $perm_user, $mdp)
    {
        $this->id = $id;
        $this->name = $name;
        $this->perm_user = $perm_user;
        $this->mdp = $mdp;
    }

    /**
     * Récupère un utilisateur par son ID
     */
    public static function findById($id)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, perm_user, mdp FROM utilisateur WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new User(
                $data['id'],
                $data['name'],
                $data['perm_user'],
                $data['mdp']
            );
        }
        
        return null;
    }

    /**
     * Récupère un utilisateur par son nom
     */
    public static function findByName($name)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, perm_user, mdp FROM utilisateur WHERE name = :name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new User(
                $data['id'],
                $data['name'],
                $data['perm_user'],
                $data['mdp']
            );
        }
        
        return null;
    }

    /**
     * Récupère tous les utilisateurs
     */
    public static function findAll()
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, name, perm_user, mdp FROM utilisateur";
        $stmt = $db->query($query);
        
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                $data['id'],
                $data['name'],
                $data['perm_user'],
                $data['mdp']
            );
        }
        
        return $users;
    }

    /**
     * Authentifie un utilisateur
     */
    public static function authenticate($name, $password)
    {
        $user = self::findByName($name);
        
        if ($user && password_verify($password, $user->getMdp())) {
            return $user;
        }
        
        return null;
    }

    /**
     * Vérifie si l'utilisateur a les permissions d'administrateur
     */
    public function isAdmin()
    {
        return $this->perm_user === 'admin' || $this->perm_user === 1;
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permission)
    {
        return $this->perm_user === $permission;
    }

    /**
     * Récupère tous les héros de l'utilisateur
     */
    public function getHeroes()
    {
        require_once 'models/Hero.php';
        return Hero::getByUserId($this->id);
    }

    /**
     * Sauvegarde un nouvel utilisateur
     */
    public function save()
    {
        $db = Database::getConnection();
        
        // Hasher le mot de passe avant de le sauvegarder
        $hashedPassword = password_hash($this->mdp, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO utilisateur (name, perm_user, mdp) 
                  VALUES (:name, :perm_user, :mdp)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':perm_user', $this->perm_user);
        $stmt->bindParam(':mdp', $hashedPassword);
        $stmt->execute();
        
        $this->id = $db->lastInsertId();
        $this->mdp = $hashedPassword;
        
        return $this;
    }

    /**
     * Met à jour l'utilisateur
     */
    public function update()
    {
        $db = Database::getConnection();
        
        $query = "UPDATE utilisateur 
                  SET name = :name, perm_user = :perm_user, mdp = :mdp 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':perm_user', $this->perm_user);
        $stmt->bindParam(':mdp', $this->mdp);
        $stmt->execute();
        
        return $this;
    }

    /**
     * Change le mot de passe de l'utilisateur
     */
    public function changePassword($newPassword)
    {
        $this->mdp = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->update();
    }

    /**
     * Supprime l'utilisateur
     */
    public function delete()
    {
        $db = Database::getConnection();
        
        $query = "DELETE FROM utilisateur WHERE id = :id";
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

    public function getPermUser()
    {
        return $this->perm_user;
    }

    public function getMdp()
    {
        return $this->mdp;
    }

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPermUser($perm_user)
    {
        $this->perm_user = $perm_user;
    }

    public function setMdp($mdp)
    {
        $this->mdp = $mdp;
    }
}