<?php
class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'dx13_bd';
            $username = 'dx13';
            $password = 'iniejohJohghe9ch'; // Ligne 9 - C'EST ICI LE PROBLÈME
            
            try {
                self::$connection = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8",
                    $username,
                    $password
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}