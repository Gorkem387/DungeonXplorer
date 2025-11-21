<?php

class Database
{
    private static $pdo;
    public static function getConnection()
    {
        if (!self::$pdo) {
            self::$pdo = new PDO(
                "mysql:host=mysql.info.unicaen.fr;dbname=dx13_bd;charset=utf8",
                "dx13",
                "iniejohJohghe9ch"
            );
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}
