<?php
require 'db.php';

try {
    // Table pour les services
    $sql_services = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        icon VARCHAR(100),
        image VARCHAR(255),
        link VARCHAR(255),
        linkText VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_services);
    
    // Table pour les pneus
    $sql_tires = "CREATE TABLE IF NOT EXISTS tires (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        width VARCHAR(10) NOT NULL,
        ratio VARCHAR(10) NOT NULL,
        rim VARCHAR(10) NOT NULL,
        category VARCHAR(50) NOT NULL,
        condition_type VARCHAR(20) NOT NULL,
        price INT NOT NULL,
        description TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_tires);

    // Initialisation des données de base si la table services est vide
    $stmt = $pdo->query("SELECT COUNT(*) FROM services");
    if ($stmt->fetchColumn() == 0) {
        $init_services = "INSERT INTO services (title, description, icon, image, link, linkText) VALUES 
        ('Vente de Pneus Neufs & Occasions', 'Large gamme de pneus certifiés des plus grandes marques.', 'fa-dharmachakra', 'assets/images/hero.png', '#catalogue', 'Voir le catalogue'),
        ('Parallélisme & Géométrie 3D', 'Réglage laser de précision pour éviter l\'usure anormale des pneus.', 'fa-crosshairs', 'assets/images/alignment.png', '#devis', 'Demander un tarif'),
        ('Batteries Automobiles', 'Vente, test de charge et remplacement rapide de batteries haute performance.', 'fa-car-battery', 'assets/images/battery.png', '#devis', 'Consulter les stocks'),
        ('Équilibrage & Jantes Alu/Acier', 'Équilibrage électronique dynamique pour supprimer les vibrations du volant.', 'fa-scale-balanced', 'assets/images/jantes.png', '#devis', 'Calculer un forfait')";
        $pdo->exec($init_services);
    }
    
    // Initialisation des données de base si la table tires est vide
    $stmt = $pdo->query("SELECT COUNT(*) FROM tires");
    if ($stmt->fetchColumn() == 0) {
        $init_tires = "INSERT INTO tires (brand, model, width, ratio, rim, category, condition_type, price, description) VALUES 
        ('Michelin', 'Primacy 4', '195', '65', 'R15', 'tourisme', 'new', 38000, 'Pneu été haute longévité.'),
        ('Bridgestone', 'Turanza ER300', '205', '55', 'R16', 'tourisme', 'new', 42000, 'Confort de conduite silencieux.'),
        ('Goodyear', 'EfficientGrip 2 SUV', '215', '60', 'R17', 'suv', 'new', 55000, 'Excellente tenue de route pour 4x4.'),
        ('Continental', 'ContiCrossContact LX2', '265', '65', 'R17', 'suv', 'new', 68000, 'Robuste tout-terrain et route.')";
        $pdo->exec($init_tires);
    }
    
    echo "<h1>Installation de la Base de Données Réussie !</h1>";
    echo "<p>Les tables 'services' et 'tires' ont été créées avec succès.</p>";
    echo "<a href='index.php'>Aller à l'administration</a>";
    
} catch (PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage());
}
?>
