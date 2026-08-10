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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Adams Pneumatique</title>
    <link rel="stylesheet" href="style.css">
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
                <th>Image</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach($services as $s): ?>
            <tr>
                <td><img src="../<?= $s['image'] ?>" alt="" width="50"></td>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['description']) ?></td>
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_service">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce service ?')">Supprimer</button>
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
                <th>Marque & Modèle</th>
                <th>Dimensions</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
            <?php foreach($tires as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['brand'] . ' - ' . $t['model']) ?></td>
                <td><?= htmlspecialchars($t['width'] . '/' . $t['ratio'] . ' ' . $t['rim']) ?></td>
                <td><?= htmlspecialchars($t['category']) ?> (<?= htmlspecialchars($t['condition_type']) ?>)</td>
                <td><?= htmlspecialchars($t['price']) ?> FCFA</td>
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_tire">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce pneu ?')">Supprimer</button>
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
                <label>Classe d'icône FontAwesome (ex: fa-car)</label>
                <input type="text" name="icon" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Image d'illustration (JPG/PNG)</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>Lien d'action (ex: #devis)</label>
                <input type="text" name="link" class="form-control" value="#devis" required>
            </div>
            <div class="form-group">
                <label>Texte du lien (ex: Demander un tarif)</label>
                <input type="text" name="linkText" class="form-control" required>
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
                    <option value="tourisme">Tourisme</option>
                    <option value="suv">SUV / 4x4</option>
                    <option value="utilitaire">Utilitaire</option>
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

</body>
</html>
