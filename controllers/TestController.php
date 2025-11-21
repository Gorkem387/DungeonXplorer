<?php

class TestController
{
    public function db()
    {
        try {
            require_once 'models/Database.php';
            $db = Database::getConnection();

            echo "<h2>Connexion réussie !</h2>";
        } catch (Exception $e) {
            echo "<h2>Erreur de connexion :</h2>";
            echo "<pre>" . $e->getMessage() . "</pre>";
        }
    }
}