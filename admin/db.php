<?php
$host = 'localhost';

// Détection de l'environnement local (localhost)
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1');

if ($isLocal) {
    // Environnement Local (XAMPP)
    $dbname = 'adamspne_bd';
    $username = 'root';
    $password = '';
    
    // Création automatique de la base locale si elle n'existe pas (XAMPP)
    try {
        $pdo_init = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    } catch (PDOException $e) {
        // Ignorer l'erreur, la connexion principale la gérera
    }
} else {
    // Environnement de Production (cPanel)
    $dbname = 'adamspne_bd';
    $username = 'adamspne_user';
    $password = 'd*w+NB$R]w[DTV2b';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
