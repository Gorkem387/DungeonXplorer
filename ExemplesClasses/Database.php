<?php

// core/Database.php

// Chemin vers le fichier .env
$envFile = __DIR__ . '/../.env';

// Vérification de l'existence du fichier .env
if (!file_exists($envFile)) {
    die("Le fichier .env n'existe pas.");
}

// Lecture du fichier .env et récupération des variables d'environnement
$env = parse_ini_file($envFile);

// Récupération des variables d'environnement
$dbHost = $env['DB_HOST'];
$dbName = $env['DB_NAME'];
$dbUser = $env['DB_USER'];
$dbPassword = $env['DB_PASSWORD'];

try {
    // Connexion à la base de données
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPassword);
    // Définition des attributs de PDO pour afficher les erreurs
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}
