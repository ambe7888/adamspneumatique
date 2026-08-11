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
        <link rel="stylesheet" href="style.css?v=<?= time() ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="login-box">
            <i class="fa-solid fa-lock" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px;"></i>
            <h2>Espace Sécurisé</h2>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Administration Adams Pneumatique</p>
            <?php if(isset($error)) echo "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> $error</div>"; ?>
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
$extra_services = $pdo->query("SELECT * FROM extra_services")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials")->fetchAll();

$settings_raw = $pdo->query("SELECT * FROM settings")->fetchAll();
$settings = [];
foreach($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$locations = [];
try {
    $locations = $pdo->query("SELECT * FROM locations")->fetchAll();
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Adams Pneumatique</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0;"><i class="fa-solid fa-gauge-high" style="color: var(--primary)"></i> Tableau de Bord</h1>
            <div>
                <a href="../index.php" target="_blank" class="btn btn-secondary"><i class="fa-solid fa-globe"></i> Voir le site</a>
                <a href="?logout=1" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 25px 0;">

        <div class="section-header">
            <h2><i class="fa-solid fa-hand-holding-hand" style="color: var(--secondary)"></i> Gestion des Services</h2>
            <button class="btn" onclick="document.getElementById('modalService').style.display='block'"><i class="fa-solid fa-plus"></i> Nouveau Service</button>
        </div>
        <div class="table-responsive">
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
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">

        <div class="section-header">
            <h2><i class="fa-solid fa-tags" style="color: var(--secondary)"></i> Catégories de Pneus</h2>
            <button class="btn" onclick="document.getElementById('modalCategory').style.display='block'"><i class="fa-solid fa-plus"></i> Nouvelle Catégorie</button>
        </div>
        <div class="table-responsive">
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
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">

        <div class="section-header">
            <h2><i class="fa-solid fa-tire" style="color: var(--secondary)"></i> Gestion des Pneus</h2>
            <button class="btn" onclick="document.getElementById('modalTire').style.display='block'"><i class="fa-solid fa-plus"></i> Nouveau Pneu</button>
        </div>
        <div class="table-responsive">
        <table>
            <tr>
                <th>Statut</th>
                <th>Image</th>
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
                <td><?php if(!empty($t['image'])): ?><img src="../<?= $t['image'] ?>" alt="" width="50"><?php endif; ?></td>
                <td><?= htmlspecialchars($t['brand'] . ' - ' . $t['model']) ?></td>
                <td><?= htmlspecialchars($t['width'] . '/' . $t['ratio'] . ' ' . $t['rim']) ?></td>
                <td><?= htmlspecialchars($t['category']) ?> (<?= htmlspecialchars($t['condition_type']) ?>)</td>
                <td><?= htmlspecialchars($t['price']) ?> FCFA</td>
                <td class="action-group">
                    <button class="btn btn-sm btn-info" onclick="openEditTire(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['brand'])) ?>', '<?= htmlspecialchars(addslashes($t['model'])) ?>', '<?= $t['width'] ?>', '<?= $t['ratio'] ?>', '<?= $t['rim'] ?>', '<?= $t['category'] ?>', '<?= $t['condition_type'] ?>', <?= $t['price'] ?>, '<?= htmlspecialchars(addslashes($t['description'])) ?>', '<?= $t['image'] ?>')">Modifier</button>

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
        <!-- Options de Devis (Services Complémentaires) -->
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">
        <div class="section-header">
            <h2><i class="fa-solid fa-list-check" style="color: var(--secondary)"></i> Options de Devis</h2>
            <button class="btn" onclick="document.getElementById('modalExtraService').style.display='block'"><i class="fa-solid fa-plus"></i> Nouvelle Option</button>
        </div>
        <div class="table-responsive">
        <table>
            <tr>
                <th>Statut</th>
                <th>Titre</th>
                <th>Prix</th>
                <th>Type de prix</th>
                <th>Coché par défaut</th>
                <th>Actions</th>
            </tr>
            <?php foreach($extra_services as $es): 
                $isHidden = isset($es['is_hidden']) && $es['is_hidden'] == 1;
            ?>
            <tr class="<?= $isHidden ? 'row-hidden' : '' ?>">
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_hide_extra_service">
                        <input type="hidden" name="id" value="<?= $es['id'] ?>">
                        <input type="hidden" name="state" value="<?= $isHidden ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm <?= $isHidden ? 'btn-info' : 'btn-secondary' ?>">
                            <?= $isHidden ? 'Afficher' : 'Masquer' ?>
                        </button>
                    </form>
                </td>
                <td><?= htmlspecialchars($es['title']) ?></td>
                <td><?= htmlspecialchars($es['price']) ?> FCFA</td>
                <td><?= $es['price_type'] == 'per_tire' ? 'Par pneu' : 'Forfait fixe' ?></td>
                <td><?= $es['is_checked'] ? 'Oui' : 'Non' ?></td>
                <td class="action-group">
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_extra_service">
                        <input type="hidden" name="id" value="<?= $es['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette option ?')">Suppr.</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <!-- Témoignages -->
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">
        <div class="section-header">
            <h2><i class="fa-solid fa-comments" style="color: var(--secondary)"></i> Avis Clients</h2>
            <button class="btn" onclick="document.getElementById('modalTestimonial').style.display='block'"><i class="fa-solid fa-plus"></i> Nouvel Avis</button>
        </div>
        <div class="table-responsive">
        <table>
            <tr>
                <th>Statut</th>
                <th>Auteur</th>
                <th>Rôle/Lieu</th>
                <th>Note</th>
                <th>Texte</th>
                <th>Actions</th>
            </tr>
            <?php foreach($testimonials as $testi): 
                $isHidden = isset($testi['is_hidden']) && $testi['is_hidden'] == 1;
            ?>
            <tr class="<?= $isHidden ? 'row-hidden' : '' ?>">
                <td>
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_hide_testimonial">
                        <input type="hidden" name="id" value="<?= $testi['id'] ?>">
                        <input type="hidden" name="state" value="<?= $isHidden ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm <?= $isHidden ? 'btn-info' : 'btn-secondary' ?>">
                            <?= $isHidden ? 'Afficher' : 'Masquer' ?>
                        </button>
                    </form>
                </td>
                <td><?= htmlspecialchars($testi['author']) ?></td>
                <td><?= htmlspecialchars($testi['role']) ?></td>
                <td><?= htmlspecialchars($testi['stars']) ?>/5</td>
                <td><small><?= htmlspecialchars($testi['text']) ?></small></td>
                <td class="action-group">
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_testimonial">
                        <input type="hidden" name="id" value="<?= $testi['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet avis ?')">Suppr.</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <!-- Agences (Locations) -->
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">
        <div class="section-header">
            <h2><i class="fa-solid fa-map-location-dot" style="color: var(--secondary)"></i> Nos Agences</h2>
            <button class="btn" onclick="document.getElementById('modalLocation').style.display='block'"><i class="fa-solid fa-plus"></i> Nouvelle Agence</button>
        </div>
        <div class="table-responsive">
        <table>
            <tr>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Horaires</th>
                <th>Actions</th>
            </tr>
            <?php foreach($locations as $loc): ?>
            <tr>
                <td><?= htmlspecialchars($loc['name']) ?></td>
                <td><small><?= htmlspecialchars($loc['address']) ?></small></td>
                <td><?= htmlspecialchars($loc['phone']) ?></td>
                <td><small><?= htmlspecialchars($loc['hours']) ?></small></td>
                <td class="action-group">
                    <form action="action.php" method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_location">
                        <input type="hidden" name="id" value="<?= $loc['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette agence ?')">Suppr.</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($locations) == 0): ?>
            <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Aucune agence ajoutée.</td></tr>
            <?php endif; ?>
        </table>
        </div>

        <!-- Paramètres Globaux -->
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">
        <div class="section-header">
            <h2><i class="fa-solid fa-cogs" style="color: var(--secondary)"></i> Informations du Site & Contacts</h2>
        </div>
        <div style="background: var(--bg-card); padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <form action="action.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_settings">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Nom du site</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone affiché</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Numéro WhatsApp (sans + ni espaces)</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($settings['whatsapp'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Horaires d'ouverture</label>
                        <input type="text" name="working_hours" class="form-control" value="<?= htmlspecialchars($settings['working_hours'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Adresse Physique</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($settings['address'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Lien Facebook</label>
                        <input type="text" name="facebook_url" class="form-control" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Lien vers Google Maps (URL normale, pour les clics)</label>
                        <input type="text" name="map_url" class="form-control" value="<?= htmlspecialchars($settings['map_url'] ?? '') ?>" required>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>🗺️ URL Google Maps Embed (pour afficher la carte — format <code>https://www.google.com/maps/embed?pb=...</code>)</label>
                        <input type="text" name="map_embed" class="form-control" value="<?= htmlspecialchars($settings['map_embed'] ?? '') ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                        <small style="color: #94a3b8;">Sur Google Maps → Partager → Intégrer une carte → copier le lien <code>src="..."</code> de l'iframe</small>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; background: #0f172a; padding: 15px; border-radius: 8px; border: 1px dashed var(--primary);">
                        <label style="color: var(--primary); font-weight: 700; font-size: 1rem;"><i class="fa-solid fa-video"></i> Vidéo de Présentation (Téléversement direct ou Lien YouTube)</label>
                        <p style="margin: 5px 0 10px 0; font-size: 0.85rem; color: #94a3b8;">
                            Téléversez directement un fichier vidéo MP4/WebM depuis votre appareil ou saisissez un lien YouTube.
                        </p>
                        
                        <?php 
                        $currentVideo = $settings['video_url'] ?? '';
                        $isLocalVideo = strpos($currentVideo, 'assets/videos/') === 0;
                        ?>
                        
                        <?php if ($currentVideo): ?>
                            <div style="margin-bottom: 12px; padding: 10px; background: #1e293b; border-radius: 6px; font-size: 0.9rem;">
                                🎬 <strong>Vidéo actuelle :</strong> <?= htmlspecialchars($currentVideo) ?><br>
                                <?php if ($isLocalVideo): ?>
                                    <label style="margin-top: 6px; display: inline-block; color: #ef233c; cursor: pointer;">
                                        <input type="checkbox" name="delete_video" value="1"> <i class="fa-solid fa-trash"></i> Supprimer cette vidéo téléversée
                                    </label>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <label style="font-size: 0.85rem; color: #cbd5e1;">📁 Option A : Téléverser un fichier vidéo (MP4, WebM)</label>
                                <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                            </div>
                            <div style="text-align: center; color: #64748b; font-size: 0.8rem; font-weight: bold;">— OU —</div>
                            <div>
                                <label style="font-size: 0.85rem; color: #cbd5e1;">🔗 Option B : Lien Vidéo YouTube / Externe</label>
                                <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($isLocalVideo ? '' : $currentVideo) ?>" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="margin-top: 15px;"><i class="fa-solid fa-save"></i> Enregistrer les informations</button>
            </form>
        </div>
    </div>

    <!-- Modale Ajout Option Devis -->
    <div id="modalExtraService" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalExtraService').style.display='none'">&times;</span>
        <h2>Ajouter une option de devis</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="add_extra_service">
            <div class="form-group">
                <label>Titre de l'option (ex: Montage & Equilibrage)</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Prix (FCFA)</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Type de calcul</label>
                <select name="price_type" class="form-control">
                    <option value="per_tire">Par pneu (Prix × Quantité)</option>
                    <option value="flat">Forfait fixe (Prix ajouté une seule fois)</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_checked" value="1" checked>
                    Coché par défaut dans le devis
                </label>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
        </form>
      </div>
    </div>

    <!-- Modale Ajout Témoignage -->
    <div id="modalTestimonial" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalTestimonial').style.display='none'">&times;</span>
        <h2>Ajouter un avis client</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="add_testimonial">
            <div class="form-group">
                <label>Nom du client</label>
                <input type="text" name="author" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Rôle ou Lieu (ex: Client de Marcory)</label>
                <input type="text" name="role" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Texte de l'avis</label>
                <textarea name="text" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Note (Etoiles)</label>
                <select name="stars" class="form-control">
                    <option value="5">5 Étoiles</option>
                    <option value="4">4 Étoiles</option>
                    <option value="3">3 Étoiles</option>
                    <option value="2">2 Étoiles</option>
                    <option value="1">1 Étoile</option>
                </select>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
        </form>
      </div>
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
        <form action="action.php" method="post" enctype="multipart/form-data">
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
            <div class="form-group">
                <label>Image du pneu (optionnelle)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
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
        <form action="action.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_tire">
            <input type="hidden" name="id" id="editTireId">
            <input type="hidden" name="existing_image" id="editTireExistingImage">
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
            <div class="form-group">
                <label>Nouvelle image (laisser vide pour conserver l'actuelle)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
      </div>
    </div>

    <!-- Modale Ajout Agence -->
    <div id="modalLocation" class="modal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalLocation').style.display='none'">&times;</span>
        <h2>Ajouter une Agence</h2>
        <form action="action.php" method="post">
            <input type="hidden" name="action" value="add_location">
            <div class="form-group">
                <label>Nom de l'agence (ex: Treichville)</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Adresse Physique</label>
                <input type="text" name="address" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Téléphone d'appel</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Horaires d'ouverture</label>
                <input type="text" name="hours" class="form-control" placeholder="ex: 07h30 - 19h00" required>
            </div>
            <div class="form-group">
                <label>Lien vers Google Maps (URL)</label>
                <input type="text" name="map_url" class="form-control" required>
            </div>
            <button type="submit" class="btn">Enregistrer</button>
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

        function openEditTire(id, brand, model, width, ratio, rim, category, condition, price, desc, image) {
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
            document.getElementById('editTireExistingImage').value = image;
            document.getElementById('modalEditTire').style.display = 'block';
        }
    </script>

    <!-- Section Vider le Cache -->
    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 40px 0;">
    <div style="background: linear-gradient(135deg, #1a1a2e, #16213e); border: 1px solid #f72585; border-radius: 12px; padding: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 40px;">
        <div>
            <h3 style="margin: 0 0 8px 0; color: #f72585; display:flex; align-items:center; gap: 10px;">
                <i class="fa-solid fa-broom"></i> Vider le Cache du Site
            </h3>
            <p style="margin: 0; color: #94a3b8; font-size: 0.9rem;">
                Force tous les visiteurs à télécharger la dernière version du CSS et du JavaScript.<br>
                À utiliser après chaque modification pour que les changements soient visibles immédiatement.
            </p>
            <?php if(isset($_GET['cache_cleared']) && $_GET['cache_cleared'] == 1): ?>
            <div style="margin-top: 12px; padding: 10px 16px; background: rgba(6,214,160,0.15); border: 1px solid #06d6a0; border-radius: 8px; color: #06d6a0; font-weight: 600; font-size: 0.88rem;">
                <i class="fa-solid fa-circle-check"></i> Cache vidé avec succès ! La nouvelle version sera chargée par tous les visiteurs.
            </div>
            <?php endif; ?>
        </div>
        <form method="post" action="action.php" onsubmit="return confirm('Vider le cache maintenant ? Tous les visiteurs verront la dernière version du site.')">
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" style="background: linear-gradient(135deg, #f72585, #b5179e); color: white; border: none; padding: 14px 28px; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 20px rgba(247,37,133,0.4);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fa-solid fa-broom"></i> Vider les Caches
            </button>
        </form>
    </div>

</body>
</html>
