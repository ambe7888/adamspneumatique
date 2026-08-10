<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Accès refusé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_service') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];
        $link = $_POST['link'];
        $linkText = $_POST['linkText'];
        
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
            // Sécurité de base
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uniqueName = time() . '_' . $name;
                $dest = '../assets/images/' . $uniqueName;
                if (move_uploaded_file($tmp_name, $dest)) {
                    $imagePath = 'assets/images/' . $uniqueName;
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO services (title, description, icon, image, link, linkText) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $imagePath, $link, $linkText]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_service') {
        $id = $_POST['id'];
        
        // Supprimer l'image associée si elle existe
        $stmt = $pdo->prepare("SELECT image FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        if ($res && $res['image']) {
            $imgPath = '../' . $res['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }

        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'add_tire') {
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $width = $_POST['width'];
        $ratio = $_POST['ratio'];
        $rim = $_POST['rim'];
        $category = $_POST['category'];
        $condition_type = $_POST['condition_type'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("INSERT INTO tires (brand, model, width, ratio, rim, category, condition_type, price, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brand, $model, $width, $ratio, $rim, $category, $condition_type, $price, $description]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_tire') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM tires WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php");
        exit;
    }
}
header("Location: index.php");
?>
