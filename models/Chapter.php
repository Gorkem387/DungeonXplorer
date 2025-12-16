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