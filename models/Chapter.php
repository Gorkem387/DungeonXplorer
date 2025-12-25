<?php
require_once 'models/Database.php';

class Chapter
{
    private $id;
    private $title;
    private $content;
    private $image;
    private $links = [];

    public function __construct($id, $title, $content, $image)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->image = $image;
    }
    
    public static function findById($id)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return null; }

        $query = "SELECT id, title, content, image FROM Chapter WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $chapter = new Chapter($data['id'], $data['title'] ?? null, $data['content'], $data['image']);
            $chapter->loadLinks();
            return $chapter;
        }
        
        return null;
    }
    
    private function loadLinks()
    {
        $db = Database::getConnection();
        
        if ($db === null) { return; }

        $query = "SELECT chapter_id, next_chapter_id, description 
                  FROM Links 
                  WHERE chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function hasEncounter($chapterId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return false; }

        $query = "SELECT COUNT(*) FROM Encounter WHERE chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $chapterId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    public static function getEncounterWithMonster($chapterId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return null; }

        $query = "SELECT e.id, e.chapter_id, e.monster_id, 
                          m.name, m.pv, m.mana, m.initiative, m.strength, m.xp, m.img
                  FROM Encounter e
                  INNER JOIN Monster m ON e.monster_id = m.id
                  WHERE e.chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $chapterId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function getNextChapterAfterEncounter($chapterId)
    {
        $db = Database::getConnection();
        
        if ($db === null) { return null; }
        
        $query = "SELECT next_chapter_id 
                  FROM Links 
                  WHERE chapter_id = :chapter_id 
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $chapterId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['next_chapter_id'] : null;
    }

    public static function getTreasure($chapterId) {
        $db = Database::getConnection();
        $query = "SELECT ct.item_id, ct.quantity, i.name 
                  FROM Chapter_Treasure ct 
                  JOIN Items i ON ct.item_id = i.id 
                  WHERE ct.chapter_id = :cid";
        $stmt = $db->prepare($query);
        $stmt->execute([':cid' => $chapterId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function hasCollectedTreasure($heroId, $chapterId)
    {
        $db = Database::getConnection();
        $query = "SELECT status FROM Hero_Progress 
                WHERE hero_id = :hero_id AND chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':hero_id' => $heroId, ':chapter_id' => $chapterId]);
        $result = $stmt->fetchColumn();
        
        return ($result === 'collected'); 
    }

    public static function processTreasureCollection($heroId, $chapterId) {
        $db = Database::getConnection();
        $treasure = self::getTreasure($chapterId);
    
        if ($treasure) {
            try {
                $db->beginTransaction();

                $stmtInv = $db->prepare("INSERT INTO Inventory (hero_id, item_id, quantity) VALUES (?, ?, ?)");
                $stmtInv->execute([$heroId, $treasure['item_id'], $treasure['quantity']]);

                $stmtUpd = $db->prepare("UPDATE Hero_Progress SET status = 'collected' WHERE hero_id = ? AND chapter_id = ?");
                $stmtUpd->execute([$heroId, $chapterId]);
    
                return $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                return false;
            }
        }
        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getLinks()
    {
        return $this->links;
    }
}