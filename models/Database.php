<?php

class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'dx13_bd';
            $username = 'dx13';
            $password = 'iniejohJohghe9ch';
            try {
                self::$connection = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8",
                    $username,
                    $password
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                return null; 
            }
        }
        return self::$connection;
    }
}