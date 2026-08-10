<?php
require 'db.php';

header('Content-Type: application/json');

$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    if ($type === 'services') {
        $stmt = $pdo->query("SELECT * FROM services");
        $services = $stmt->fetchAll();
        // Rename description to desc to match frontend JS expectations
        foreach($services as &$s) {
            $s['desc'] = $s['description'];
        }
        echo json_encode($services);
    } elseif ($type === 'tires') {
        $stmt = $pdo->query("SELECT * FROM tires");
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
