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


        $desc = htmlspecialchars($_POST['desc']);
        if (isset($_FILES['image'])){
            $image = htmlspecialchars($_POST['image']);
            $update = 'update Chapter set image = :image and content = :desc WHERE id = ' . $_SESSION['id'] . ';';
            $req = $bdd->prepare($update);
            $req->execute(
            array(
            'image' => $image,
            'desc' => $desc
        )
        );
        }
        else{
            $update = 'update Chapter set content = :desc WHERE id = ' . $_SESSION['id'] . ';';
            $req = $bdd->prepare($update);
            $req->execute(
            array(
            'desc' => $desc
        )
        );
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
            if (isset($_FILES['image'])){
                $image = htmlspecialchars($_POST['image']);
            }
            else{
                $image = '../public/img/Castle01.jpg';
            }

            $insert = $bdd -> prepare("Insert Into Chapter (content, image) 
            Values (:desc, :image);");
            if ($insert->execute([
                'desc' => $desc,
                'image' => $image,
            ]));

            $result = $bdd -> query("Select id From Chapter Where id = (select max(id) from Chapter);");
            $rep = $result->fetch(PDO::FETCH_ASSOC);
       
            if (iSSet($_POST['precedent'])) {
                foreach ($_POST['precedent'] as $val) {
                    $insert = $bdd -> prepare("Insert Into Links (chapter_id, next_chapter_id) 
                    Values (:curr, :next);");
                    if ($insert->execute([
                        'curr' => $val,
                        'next' => $rep['id'],
                    ]));
                }
            }

           if (isset($_POST['prochain'])) {
                foreach ($_POST['prochain'] as $val) {
                    $insert = $bdd -> prepare("Insert Into Links (chapter_id, next_chapter_id) 
                    Values (:curr, :next);");
                    if ($insert->execute([
                        'curr' => $rep['id'],
                        'next' => $val,
                    ]));
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