<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Accès refusé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // -- CATEGORIES --
    if ($action === 'add_category') {
        $name = $_POST['name'];
        // Génération automatique du slug à partir du nom
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($name)));
        $icon = $_POST['icon'];
        $base_price = (int)$_POST['base_price'];

        $stmt = $pdo->prepare("INSERT INTO tire_categories (slug, name, icon, base_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$slug, $name, $icon, $base_price]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_category') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM tire_categories WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php");
        exit;
    }

    // -- SERVICES --
    if ($action === 'add_service') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];
        $link = '#devis';
        $linkText = 'Demander un devis';
        
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
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

    if ($action === 'edit_service') {
        $id = (int)$_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];

        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uniqueName = time() . '_' . $name;
                $dest = '../assets/images/' . $uniqueName;
                if (move_uploaded_file($tmp_name, $dest)) {
                    // Supprimer l'ancienne image si nouvelle fournie
                    if ($imagePath && file_exists('../' . $imagePath)) unlink('../' . $imagePath);
                    $imagePath = 'assets/images/' . $uniqueName;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, icon = ?, image = ? WHERE id = ?");
        $stmt->execute([$title, $description, $icon, $imagePath, $id]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_service') {
        $id = $_POST['id'];
        
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

    if ($action === 'duplicate_service') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if ($service) {
            $newTitle = $service['title'] . ' (Copie)';
            $stmtInsert = $pdo->prepare("INSERT INTO services (title, description, icon, image, link, linkText, is_hidden) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([$newTitle, $service['description'], $service['icon'], $service['image'], $service['link'], $service['linkText'], $service['is_hidden'] ?? 0]);
        }
        header("Location: index.php");
        exit;
    }

    if ($action === 'toggle_hide_service') {
        $id = (int)$_POST['id'];
        $state = (int)$_POST['state']; // 1 for hide, 0 for show
        $stmt = $pdo->prepare("UPDATE services SET is_hidden = ? WHERE id = ?");
        $stmt->execute([$state, $id]);
        header("Location: index.php");
        exit;
    }

    // -- TIRES --
    if ($action === 'add_tire') {
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $width = $_POST['width'];
        $ratio = $_POST['ratio'];
        $rim = $_POST['rim'];
        $category = $_POST['category'];
        $condition_type = $_POST['condition_type'];
        $price = (int)$_POST['price'];
        $description = $_POST['description'];

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uniqueName = time() . '_tire_' . $name;
                $dest = '../assets/images/' . $uniqueName;
                if (move_uploaded_file($tmp_name, $dest)) {
                    $imagePath = 'assets/images/' . $uniqueName;
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO tires (brand, model, width, ratio, rim, category, condition_type, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brand, $model, $width, $ratio, $rim, $category, $condition_type, $price, $description, $imagePath]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'edit_tire') {
        $id = (int)$_POST['id'];
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $width = $_POST['width'];
        $ratio = $_POST['ratio'];
        $rim = $_POST['rim'];
        $category = $_POST['category'];
        $condition_type = $_POST['condition_type'];
        $price = (int)$_POST['price'];
        $description = $_POST['description'];

        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $uniqueName = time() . '_tire_' . $name;
                $dest = '../assets/images/' . $uniqueName;
                if (move_uploaded_file($tmp_name, $dest)) {
                    if ($imagePath && file_exists('../' . $imagePath)) unlink('../' . $imagePath);
                    $imagePath = 'assets/images/' . $uniqueName;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE tires SET brand=?, model=?, width=?, ratio=?, rim=?, category=?, condition_type=?, price=?, description=?, image=? WHERE id=?");
        $stmt->execute([$brand, $model, $width, $ratio, $rim, $category, $condition_type, $price, $description, $imagePath, $id]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_tire') {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("SELECT image FROM tires WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        if ($res && $res['image']) {
            $imgPath = '../' . $res['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }

        $stmt = $pdo->prepare("DELETE FROM tires WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'duplicate_tire') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT * FROM tires WHERE id = ?");
        $stmt->execute([$id]);
        $tire = $stmt->fetch();

        if ($tire) {
            $newModel = $tire['model'] . ' (Copie)';
            $stmtInsert = $pdo->prepare("INSERT INTO tires (brand, model, width, ratio, rim, category, condition_type, price, description, image, is_hidden) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([$tire['brand'], $newModel, $tire['width'], $tire['ratio'], $tire['rim'], $tire['category'], $tire['condition_type'], $tire['price'], $tire['description'], $tire['image'], $tire['is_hidden'] ?? 0]);
        }
        header("Location: index.php");
        exit;
    }

    if ($action === 'toggle_hide_tire') {
        $id = (int)$_POST['id'];
        $state = (int)$_POST['state'];
        $stmt = $pdo->prepare("UPDATE tires SET is_hidden = ? WHERE id = ?");
        $stmt->execute([$state, $id]);
        header("Location: index.php");
        exit;
    }

    // -- EXTRA SERVICES (OPTIONS DE DEVIS) --
    if ($action === 'add_extra_service') {
        $title = $_POST['title'];
        $price = (int)$_POST['price'];
        $price_type = $_POST['price_type'];
        $is_checked = isset($_POST['is_checked']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO extra_services (title, price, price_type, is_checked) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $price, $price_type, $is_checked]);
        header("Location: index.php");
        exit;
    }
    
    if ($action === 'delete_extra_service') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM extra_services WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php");
        exit;
    }

    if ($action === 'toggle_hide_extra_service') {
        $id = (int)$_POST['id'];
        $state = (int)$_POST['state'];
        $stmt = $pdo->prepare("UPDATE extra_services SET is_hidden = ? WHERE id = ?");
        $stmt->execute([$state, $id]);
        header("Location: index.php");
        exit;
    }

    // -- LOCATIONS (AGENCES) --
    if ($action === 'add_location') {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $hours = $_POST['hours'];
        $map_url = $_POST['map_url'];
        
        $stmt = $pdo->prepare("INSERT INTO locations (name, address, phone, hours, map_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $address, $phone, $hours, $map_url]);
        
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_location') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php");
        exit;
    }

    // -- TESTIMONIALS --
    if ($action === 'add_testimonial') {
        $author = $_POST['author'];
        $role = $_POST['role'];
        $text = $_POST['text'];
        $stars = (int)$_POST['stars'];
        
        $stmt = $pdo->prepare("INSERT INTO testimonials (author, role, text, stars) VALUES (?, ?, ?, ?)");
        $stmt->execute([$author, $role, $text, $stars]);
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete_testimonial') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php");
        exit;
    }

    if ($action === 'toggle_hide_testimonial') {
        $id = (int)$_POST['id'];
        $state = (int)$_POST['state'];
        $stmt = $pdo->prepare("UPDATE testimonials SET is_hidden = ? WHERE id = ?");
        $stmt->execute([$state, $id]);
        header("Location: index.php");
        exit;
    }

    // -- SETTINGS --
    if ($action === 'update_settings') {
        // Liste des clés autorisées
        $allowed_keys = ['site_name', 'phone', 'whatsapp', 'address', 'map_url', 'facebook_url', 'working_hours'];
        
        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
        }
        header("Location: index.php");
        exit;
    }
}
header("Location: index.php");
?>
