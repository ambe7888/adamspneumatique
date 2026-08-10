<?php
session_start();
require 'db.php';

// Authentification simple
$password = 'admin123'; // Mot de passe par défaut

if (isset($_POST['login'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Mot de passe incorrect.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['admin_logged_in'])) {
?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Connexion Administration</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="login-box">
            <h2>Administration Adams Pneumatique</h2>
            <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
            <form method="post">
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                </div>
                <button type="submit" name="login" class="btn">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
<?php
    exit;
}

// Fetch data
$services = $pdo->query("SELECT * FROM services")->fetchAll();
$tires = $pdo->query("SELECT * FROM tires")->fetchAll();
$tire_categories = $pdo->query("SELECT * FROM tire_categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Adams Pneumatique</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .action-group form { display: inline; margin-right: 5px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-info { background: #17a2b8; }
        .btn-info:hover { background: #138496; }
        .row-hidden { opacity: 0.5; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Tableau de Bord</h1>
            <a href="?logout=1" class="btn btn-danger">Déconnexion</a>
        </div>
        <p><a href="../index.html" target="_blank">Voir le site public</a></p>
        <hr>

        <h2>Gestion des Services</h2>
        <button class="btn" onclick="document.getElementById('modalService').style.display='block'">Ajouter un Service</button>
        <table>
            <tr>
                <th>Statut</th>
                <th>Image</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach($services as $s): 
                $isHidden = isset($s['is_hidden']) && $s['is_hidden'] == 1;
            ?>
            <tr class="<?= $isHidden ? 'row-hidden' : '' ?>">
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_hide_service">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="state" value="<?= $isHidden ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm <?= $isHidden ? 'btn-info' : 'btn-secondary' ?>">
                            <?= $isHidden ? 'Afficher' : 'Masquer' ?>
                        </button>
                    </form>
                </td>
                <td><img src="../<?= $s['image'] ?>" alt="" width="50"></td>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['description']) ?></td>
                <td class="action-group">
                    <button class="btn btn-sm btn-info" onclick="openEditService(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['title'])) ?>', '<?= htmlspecialchars(addslashes($s['description'])) ?>', '<?= $s['icon'] ?>', '<?= $s['image'] ?>')">Modifier</button>
                    
                    <form action="action.php" method="post">
                        <input type="hidden" name="action" value="duplicate_service">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-secondary">Dupliquer</button>
                    </form>
                    
                    <form action="action.php" method="post">
                        <input type="hidden" name="action" value="delete_service">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce service ?')">Suppr.</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin: 40px 0;">

        <h2>Catégories de Pneus</h2>
        <button class="btn" onclick="document.getElementById('modalCategory').style.display='block'">Ajouter une Catégorie</button>
        <table>
            <tr>
                <th>Identifiant (slug)</th>
                <th>Nom Affiché</th>
                <th>Icône</th>
                <th>Prix de base</th>
                <th>Actions</th>
            </tr>
            <?php foreach($tire_categories as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['slug']) ?></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><i class="fa-solid <?= htmlspecialchars($c['icon']) ?>"></i> <?= htmlspecialchars($c['icon']) ?></td>
                <td><?= htmlspecialchars($c['base_price']) ?> FCFA</td>
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Attention: Supprimer une catégorie peut affecter les pneus liés. Continuer ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin: 40px 0;">

        <h2>Gestion des Pneus</h2>
        <button class="btn" onclick="document.getElementById('modalTire').style.display='block'">Ajouter un Pneu</button>
        <table>
            <tr>
                <th>Statut</th>
                <th>Marque & Modèle</th>
                <th>Dimensions</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
            <?php foreach($tires as $t): 
                $isHiddenTire = isset($t['is_hidden']) && $t['is_hidden'] == 1;
            ?>
            <tr class="<?= $isHiddenTire ? 'row-hidden' : '' ?>">
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_hide_tire">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="state" value="<?= $isHiddenTire ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm <?= $isHiddenTire ? 'btn-info' : 'btn-secondary' ?>">
                            <?= $isHiddenTire ? 'Afficher' : 'Masquer' ?>
                        </button>
                    </form>
                </td>
                <td><?= htmlspecialchars($t['brand'] . ' - ' . $t['model']) ?></td>
                <td><?= htmlspecialchars($t['width'] . '/' . $t['ratio'] . ' ' . $t['rim']) ?></td>
                <td><?= htmlspecialchars($t['category']) ?> (<?= htmlspecialchars($t['condition_type']) ?>)</td>
                <td><?= htmlspecialchars($t['price']) ?> FCFA</td>
                <td class="action-group">
                    <button class="btn btn-sm btn-info" onclick="openEditTire(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['brand'])) ?>', '<?= htmlspecialchars(addslashes($t['model'])) ?>', '<?= $t['width'] ?>', '<?= $t['ratio'] ?>', '<?= $t['rim'] ?>', '<?= $t['category'] ?>', '<?= $t['condition_type'] ?>', <?= $t['price'] ?>, '<?= htmlspecialchars(addslashes($t['description'])) ?>')">Modifier</button>

                    <form action="action.php" method="post">
                        <input type="hidden" name="action" value="duplicate_tire">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-secondary">Dupliquer</button>
                    </form>

                    <form action="action.php" method="post">
                        <input type="hidden" name="action" value="delete_tire">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce pneu ?')">Suppr.</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Modale Ajout Service -->
    <div id="modalService" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalService').style.display='none'">&times;</span>
        <h2>Ajouter un Service</h2>
        <form action="action.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_service">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Icône</label>
                <select name="icon" class="form-control">
                    <option value="fa-car">Voiture (fa-car)</option>
                    <option value="fa-truck">Camion (fa-truck)</option>
                    <option value="fa-truck-monster">SUV/4x4 (fa-truck-monster)</option>
                    <option value="fa-van-shuttle">Utilitaire (fa-van-shuttle)</option>
                    <option value="fa-dharmachakra">Pneu / Roue (fa-dharmachakra)</option>
                    <option value="fa-wrench">Clé à molette (fa-wrench)</option>
                    <option value="fa-crosshairs">Parallélisme (fa-crosshairs)</option>
                    <option value="fa-car-battery">Batterie (fa-car-battery)</option>
                    <option value="fa-scale-balanced">Equilibrage (fa-scale-balanced)</option>
                    <option value="fa-oil-can">Huile (fa-oil-can)</option>
                    <option value="fa-tools">Outils (fa-tools)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Image d'illustration (JPG/PNG)</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
        </form>
      </div>
    </div>

    <!-- Modale Modification Service -->
    <div id="modalEditService" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalEditService').style.display='none'">&times;</span>
        <h2>Modifier le Service</h2>
        <form action="action.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_service">
            <input type="hidden" name="id" id="editServiceId">
            <input type="hidden" name="existing_image" id="editServiceExistingImage">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" id="editServiceTitle" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="editServiceDesc" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Icône</label>
                <select name="icon" id="editServiceIcon" class="form-control">
                    <option value="fa-car">Voiture (fa-car)</option>
                    <option value="fa-truck">Camion (fa-truck)</option>
                    <option value="fa-truck-monster">SUV/4x4 (fa-truck-monster)</option>
                    <option value="fa-van-shuttle">Utilitaire (fa-van-shuttle)</option>
                    <option value="fa-dharmachakra">Pneu / Roue (fa-dharmachakra)</option>
                    <option value="fa-wrench">Clé à molette (fa-wrench)</option>
                    <option value="fa-crosshairs">Parallélisme (fa-crosshairs)</option>
                    <option value="fa-car-battery">Batterie (fa-car-battery)</option>
                    <option value="fa-scale-balanced">Equilibrage (fa-scale-balanced)</option>
                    <option value="fa-oil-can">Huile (fa-oil-can)</option>
                    <option value="fa-tools">Outils (fa-tools)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nouvelle image (laisser vide pour conserver l'actuelle)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
      </div>
    </div>

    <!-- Modale Ajout Catégorie -->
    <div id="modalCategory" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalCategory').style.display='none'">&times;</span>
        <h2>Ajouter une Catégorie de Pneus</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="add_category">
            <div class="form-group">
                <label>Nom Affiché (ex: Poids Lourd)</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Identifiant court unique sans espace (ex: poids-lourd)</label>
                <input type="text" name="slug" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Icône de l'onglet</label>
                <select name="icon" class="form-control">
                    <option value="fa-car">Voiture (fa-car)</option>
                    <option value="fa-truck">Camion (fa-truck)</option>
                    <option value="fa-truck-monster">SUV/4x4 (fa-truck-monster)</option>
                    <option value="fa-van-shuttle">Utilitaire (fa-van-shuttle)</option>
                    <option value="fa-arrows-rotate">Occasion (fa-arrows-rotate)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prix de base pour l'estimation (FCFA)</label>
                <input type="number" name="base_price" class="form-control" required>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
        </form>
      </div>
    </div>

    <!-- Modale Ajout Pneu -->
    <div id="modalTire" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalTire').style.display='none'">&times;</span>
        <h2>Ajouter un Pneu</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="add_tire">
            <div class="form-group">
                <label>Marque</label>
                <input type="text" name="brand" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Modèle</label>
                <input type="text" name="model" class="form-control" required>
            </div>
            <div style="display:flex; gap: 10px;">
                <div class="form-group"><label>Largeur</label><input type="text" name="width" class="form-control" required></div>
                <div class="form-group"><label>Hauteur</label><input type="text" name="ratio" class="form-control" required></div>
                <div class="form-group"><label>Diamètre (ex: R15)</label><input type="text" name="rim" class="form-control" required></div>
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="category" class="form-control">
                    <?php foreach($tire_categories as $c): ?>
                        <option value="<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>État</label>
                <select name="condition_type" class="form-control">
                    <option value="new">Neuf</option>
                    <option value="occ">Occasion</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prix (FCFA)</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description courte</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
        </form>
      </div>
    </div>

    <!-- Modale Modification Pneu -->
    <div id="modalEditTire" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalEditTire').style.display='none'">&times;</span>
        <h2>Modifier le Pneu</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="edit_tire">
            <input type="hidden" name="id" id="editTireId">
            <div class="form-group">
                <label>Marque</label>
                <input type="text" name="brand" id="editTireBrand" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Modèle</label>
                <input type="text" name="model" id="editTireModel" class="form-control" required>
            </div>
            <div style="display:flex; gap: 10px;">
                <div class="form-group"><label>Largeur</label><input type="text" name="width" id="editTireWidth" class="form-control" required></div>
                <div class="form-group"><label>Hauteur</label><input type="text" name="ratio" id="editTireRatio" class="form-control" required></div>
                <div class="form-group"><label>Diamètre (ex: R15)</label><input type="text" name="rim" id="editTireRim" class="form-control" required></div>
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="category" id="editTireCategory" class="form-control">
                    <?php foreach($tire_categories as $c): ?>
                        <option value="<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>État</label>
                <select name="condition_type" id="editTireCondition" class="form-control">
                    <option value="new">Neuf</option>
                    <option value="occ">Occasion</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prix (FCFA)</label>
                <input type="number" name="price" id="editTirePrice" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description courte</label>
                <textarea name="description" id="editTireDesc" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
      </div>
    </div>

    <script>
        function openEditService(id, title, desc, icon, image) {
            document.getElementById('editServiceId').value = id;
            document.getElementById('editServiceTitle').value = title;
            document.getElementById('editServiceDesc').value = desc;
            document.getElementById('editServiceIcon').value = icon;
            document.getElementById('editServiceExistingImage').value = image;
            document.getElementById('modalEditService').style.display = 'block';
        }

        function openEditTire(id, brand, model, width, ratio, rim, category, condition, price, desc) {
            document.getElementById('editTireId').value = id;
            document.getElementById('editTireBrand').value = brand;
            document.getElementById('editTireModel').value = model;
            document.getElementById('editTireWidth').value = width;
            document.getElementById('editTireRatio').value = ratio;
            document.getElementById('editTireRim').value = rim;
            document.getElementById('editTireCategory').value = category;
            document.getElementById('editTireCondition').value = condition;
            document.getElementById('editTirePrice').value = price;
            document.getElementById('editTireDesc').value = desc;
            document.getElementById('modalEditTire').style.display = 'block';
        }
    </script>
</body>
</html>
