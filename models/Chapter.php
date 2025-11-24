<?php
require_once 'models/Database.php';

class Chapter
{
    private $id;
    private $content;
    private $image;
    private $links = [];

    public function __construct($id, $content, $image)
    {
        $this->id = $id;
        $this->content = $content;
        $this->image = $image;
    }
    public static function findById($id)
    {
        $db = Database::getConnection();
        
        $query = "SELECT id, content, image FROM Chapter WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $chapter = new Chapter($data['id'], $data['content'], $data['image']);
            $chapter->loadLinks();
            return $chapter;
        }
        
        return null;
    }
    private function loadLinks()
    {
        $db = Database::getConnection();
        
        $query = "SELECT chapter_id, next_chapter_id, description 
                  FROM Links 
                  WHERE chapter_id = :chapter_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':chapter_id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getId()
    {
        return $this->id;
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