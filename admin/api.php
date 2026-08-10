<?php
require 'db.php';

header('Content-Type: application/json');

$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    if ($type === 'services') {
        $stmt = $pdo->query("SELECT * FROM services WHERE is_hidden = 0 OR is_hidden IS NULL");
        $services = $stmt->fetchAll();
        foreach($services as &$s) {
            $s['desc'] = $s['description'];
            $s['link'] = '#devis';
            $s['linkText'] = 'Demander un devis';
        }
        echo json_encode($services);
    } elseif ($type === 'categories') {
        $stmt = $pdo->query("SELECT * FROM tire_categories");
        echo json_encode($stmt->fetchAll());
    } elseif ($type === 'tires') {
        $stmt = $pdo->query("SELECT * FROM tires WHERE is_hidden = 0 OR is_hidden IS NULL");
        $tires = $stmt->fetchAll();
        // Rename condition_type and description
        foreach($tires as &$t) {
            $t['condition'] = $t['condition_type'];
            $t['desc'] = $t['description'];
        }
        echo json_encode($tires);
    } else {
        echo json_encode(['error' => 'Type non spécifié']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur serveur']);
}
?>
