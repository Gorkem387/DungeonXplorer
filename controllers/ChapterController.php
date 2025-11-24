<?php
require_once 'models/Chapter.php';
class ChapterController
{
    public function show($id)
    {
        $chapter = Chapter::findById($id);
        if ($chapter) {
            require $_SERVER['DOCUMENT_ROOT'] . '/views/chapter.php';
        } else {
            http_response_code(404);
            echo "Chapitre non trouvé!";
        }
    }
}