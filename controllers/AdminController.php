<?php

class AdminController
{
    public function index()
    {
        require 'views/admin/dashboard.php';
    }

    public function listeJoueur()
    {
        require 'views/admin/joueur/joueur.php';
    }

    public function deleteJoueur()
    {
        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();


        $id = htmlspecialchars($_POST['id']); 
        $delete = 'DELETE FROM utilisateur WHERE id = :id ;';
        $req = $bdd->prepare($delete);
        $req->execute(
            array(
            'id' => $id
    )
        );
        header("Location: /admin/joueur");
    }

    public function chapter()
    {
        require 'views/admin/chapters/listeChapitre.php';
    }

    public function chapterAddPage()
    {
        require 'views/admin/chapters/ajout.php';
    }

    public function chapterModifyPage()
    {
        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();

        $id = htmlspecialchars($_POST['id']); 
        $result_class = $bdd -> query("Select content From Chapter Where id = ".$id.";");
        $rep = $result_class->fetch(PDO::FETCH_ASSOC);
        $_SESSION['id'] = $id;
        $_SESSION['desc'] = $rep['content'];
        require 'views/admin/chapters/modify.php';
    }

    public function chapterDelete()
    {
        session_start();
        require_once 'models/Database.php';
        $bdd = Database::getConnection();


        $id = htmlspecialchars($_POST['id']); 

        $links = 'DELETE FROM Links WHERE chapter_id = :id or next_chapter_id = :id;';
        $req = $bdd->prepare($links);
        $req->execute(
            array(
            'id' => $id
        )
        );


        $delete = 'DELETE FROM Chapter WHERE id = :id ;';
        $req = $bdd->prepare($delete);
        $req->execute(
            array(
            'id' => $id
        )
        );
        header("Location: /admin/chapter");    
    }

    public function chapterModify()
{
    session_start();
    require_once 'models/Database.php';
    $bdd = Database::getConnection();
    
    $chapterId = $_SESSION['id'];
    $desc = htmlspecialchars($_POST['desc'], ENT_NOQUOTES, 'UTF-8');
    
    $update = 'UPDATE Chapter SET content = :desc WHERE id = :id';
    $req = $bdd->prepare($update);
    $req->execute(array(
        'desc' => $desc,
        'id' => $chapterId
    ));
      
    $deleteLinks = 'DELETE FROM Links WHERE chapter_id = :id OR next_chapter_id = :id';
    $reqDelete = $bdd->prepare($deleteLinks);
    $reqDelete->execute(array('id' => $chapterId));
    
    if (isset($_POST['precedent'])) {
        foreach ($_POST['precedent'] as $targetChapterId => $linkData) {
            if (isset($linkData['selected']) && !empty($linkData['name'])) {
                $insertLink = 'INSERT INTO Links (chapter_id, next_chapter_id, description) VALUES (:chapter_id, :next_chapter_id, :description)';
                $reqLink = $bdd->prepare($insertLink);
                $reqLink->execute(array(
                    'chapter_id' => $targetChapterId,
                    'next_chapter_id' => $chapterId,
                    'description' => htmlspecialchars($linkData['name'], ENT_NOQUOTES, 'UTF-8')
                ));
            }
        }
    }
    
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $currentItemsQuery = $bdd->prepare("SELECT item_id, quantity FROM Chapter_Item WHERE chapter_id = ?");
        $currentItemsQuery->execute([$chapterId]);
        $currentItems = [];
        while ($row = $currentItemsQuery->fetch(PDO::FETCH_ASSOC)) {
            $currentItems[$row['item_id']] = $row['quantity'];
        }
        
        foreach ($_POST['items'] as $itemId => $newQuantity) {
            $newQuantity = (int)$newQuantity;
            $itemId = (int)$itemId;
            
            $wasInChapter = isset($currentItems[$itemId]);
            $oldQuantity = $wasInChapter ? $currentItems[$itemId] : 0;
            
            if ($oldQuantity > 0 && $newQuantity == 0) {
                $deleteStmt = $bdd->prepare("DELETE FROM Chapter_Item WHERE chapter_id = ? AND item_id = ?");
                $deleteStmt->execute([$chapterId, $itemId]);
                
            } elseif ($oldQuantity == 0 && $newQuantity > 0) {
                $insertStmt = $bdd->prepare("INSERT INTO Chapter_Item (chapter_id, item_id, quantity) VALUES (?, ?, ?)");
                $insertStmt->execute([$chapterId, $itemId, $newQuantity]);
                
            } elseif ($wasInChapter && $newQuantity > 0 && $oldQuantity != $newQuantity) {
                $updateStmt = $bdd->prepare("UPDATE Chapter_Item SET quantity = ? WHERE chapter_id = ? AND item_id = ?");
                $updateStmt->execute([$newQuantity, $chapterId, $itemId]);
            }
        }
    }

    if (isset($_POST['prochain'])) {
        foreach ($_POST['prochain'] as $targetChapterId => $linkData) {
            if (isset($linkData['selected']) && !empty($linkData['name'])) {
                $insertLink = 'INSERT INTO Links (chapter_id, next_chapter_id, description) VALUES (:chapter_id, :next_chapter_id, :description)';
                $reqLink = $bdd->prepare($insertLink);
                $reqLink->execute(array(
                    'chapter_id' => $chapterId,
                    'next_chapter_id' => $targetChapterId,
                    'description' => htmlspecialchars($linkData['name'])
                ));
            }
        }
    }
    
    header("Location: /admin/chapter");
}
    public function chapterAdd()
    {
        session_start();
        require_once 'models/Database.php';

        $bdd = Database::getConnection();


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $desc = htmlspecialchars($_POST['desc']);
            $imagePath = '../public/img/Castle01.jpg';
        

            $insert = $bdd -> prepare("Insert Into Chapter (content, image) 
            Values (:desc, :image);");
            if ($insert->execute([
                'desc' => $desc,
                'image' => $imagePath,
            ]));

            $result = $bdd -> query("Select id From Chapter Where id = (select max(id) from Chapter);");
            $rep = $result->fetch(PDO::FETCH_ASSOC);
       
            if (iSSet($_POST['precedent'])) {
                foreach ($_POST['precedent'] as $val) {
                    if (isset($val['selected']) && !empty($val['selected']) && !empty($val['name'])) {
                        $insert = $bdd -> prepare("Insert Into Links (chapter_id, next_chapter_id, description) 
                        Values (:curr, :next, :name);");
                        if ($insert->execute([
                            'curr' => $val['selected'],
                            'next' => $rep['id'],
                            'name' => htmlspecialchars($val['name'])
                        ]));
                    }
                }
            }

           if (isset($_POST['prochain'])) {
                foreach ($_POST['prochain'] as $val) {
                    if (isset($val['selected']) && !empty($val['selected']) && !empty($val['name'])) {
                        $insert = $bdd -> prepare("Insert Into Links (chapter_id, next_chapter_id, description) 
                        Values (:curr, :next, :name);");
                        if ($insert->execute([
                            'curr' => $rep['id'],
                            'next' => $val['selected'],
                            'name' => htmlspecialchars($val['name'])
                        ]));
                    }
                }
            }
            
            header("Location: /admin/chapter");    
        }                
        else {
        $_SESSION['error'] = "Erreur lors de l'enregistrement du hero.";
                header("Location: /admin/add/add"); 
                exit();
        }
    }
}