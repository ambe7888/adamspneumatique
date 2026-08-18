<?php
require 'admin/db.php';
$cacheVersionFile = __DIR__ . '/assets/cache_version.txt';
$cacheVer = file_exists($cacheVersionFile) ? trim(file_get_contents($cacheVersionFile)) : '4.0';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$product = null;
$error = null;
$devisParam = '';
$priceText = '';
$titleText = '';
$badgeText = '';
$badgeClass = '';

if ($id > 0 && in_array($type, ['tire', 'rim', 'accessory'])) {
    if ($type === 'tire') {
        $stmt = $pdo->prepare("SELECT * FROM tires WHERE id = ? AND (is_hidden = 0 OR is_hidden IS NULL)");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $product['_itemType'] = 'tire';
            $devisParam = $product['brand'] . ' ' . $product['width'] . '/' . $product['ratio'] . ' ' . $product['rim'] . ' (' . $product['model'] . ')';
            $priceText = number_format((int)$product['price'], 0, ',', ' ') . ' FCFA';
            $titleText = $product['width'] . '/' . $product['ratio'] . ' ' . $product['rim'] . ' - ' . $product['model'];
            $badgeText = ($product['condition_type'] === 'new') ? 'Neuf' : 'Occasion';
            $badgeClass = ($product['condition_type'] === 'new') ? 'badge-new' : 'badge-occ';
        }
    } elseif ($type === 'rim') {
        $stmt = $pdo->prepare("SELECT * FROM rims WHERE id = ? AND (is_hidden = 0 OR is_hidden IS NULL)");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $product['_itemType'] = 'rim';
            $devisParam = 'Jante ' . $product['brand'] . ' ' . $product['model'] . ' ' . $product['diameter'];
            $priceText = number_format((int)$product['price'], 0, ',', ' ') . ' FCFA';
            $titleText = $product['model'] . ' (' . $product['diameter'] . ')';
            $badgeText = $product['type'] ?? 'Jante Alu';
            $badgeClass = 'badge-new';
        }
    } elseif ($type === 'accessory') {
        $stmt = $pdo->prepare("SELECT * FROM accessories WHERE id = ? AND (is_hidden = 0 OR is_hidden IS NULL)");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $product['_itemType'] = 'accessory';
            $devisParam = 'Accessoire : ' . $product['name'];
            $priceText = number_format((int)$product['price'], 0, ',', ' ') . ' FCFA';
            $titleText = $product['name'];
            $badgeText = 'En stock';
            $badgeClass = 'badge-new';
        }
    }
}

if (!$product) {
    $error = "Produit introuvable ou indisponible.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? htmlspecialchars($titleText) : 'Produit introuvable' ?> - Adams Pneumatique</title>
    
    <meta name="description" content="<?= $product ? htmlspecialchars($product['description'] ?? 'Découvrez nos produits chez Adams Pneumatique.') : 'Produit introuvable.' ?>">
    
    <!-- Font Awesome Icons 6.5.1 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= htmlspecialchars($cacheVer) ?>">
    
    <style>
        .product-hero {
            padding: 130px 0 45px 0;
            background: radial-gradient(circle at 50% 20%, rgba(239, 35, 60, 0.12) 0%, rgba(15, 23, 42, 0.95) 70%);
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        .product-details-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: var(--bg-card);
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .product-image {
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            border-radius: var(--radius-md);
            padding: 20px;
            border: 1px dashed rgba(255,255,255,0.1);
        }
        .product-image img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }
        .product-info h1 {
            margin-bottom: 10px;
            font-size: 2.2rem;
            color: #fff;
        }
        .product-info .brand-tag {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .product-info .price {
            font-size: 2rem;
            color: var(--primary-gold);
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-info p.description {
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 1.05rem;
        }
        .product-specs {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
            background: #1e293b;
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        .product-specs li {
            padding: 12px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #fff;
            display: flex;
            align-items: center;
        }
        .product-specs li:last-child {
            border-bottom: none;
        }
        .product-specs li span.label {
            color: #94a3b8;
            width: 150px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .product-specs li span.value {
            font-weight: 500;
        }
        .product-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .product-details-container {
                grid-template-columns: 1fr;
                padding: 25px;
            }
            .product-info h1 {
                font-size: 1.8rem;
            }
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container top-bar-content desktop-top-bar" id="dynamic-top-bar-desktop">
            <div class="status-badge">
                <span class="pulse-dot"></span>
                <span>CHARGEMENT...</span>
            </div>
        </div>
        <div class="container top-bar-content mobile-top-bar" id="dynamic-top-bar-mobile">
            <span style="color: var(--accent-green);">Chargement...</span>
        </div>
    </div>

    <!-- Header / Navbar -->
    <header class="header">
        <div class="container navbar">
            <a href="index.php" class="logo">
                <div class="logo-icon"><i class="fa-solid fa-dharmachakra"></i></div>
                <div class="logo-text">
                    <h1>ADAMS PNEUMATIQUE</h1>
                    <span class="desktop-subtitle">SERVICES AUTOMOBILES</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="nav-links" id="nav-links-menu">
                <a href="index.php#accueil">Accueil</a>
                <a href="index.php#services">Nos Services</a>
                <a href="catalogue.php">Catalogue Complet</a>
                <a href="index.php#contact">Contact & Accès</a>
            </nav>

            <div class="nav-actions">
                <a href="index.php#devis" class="btn btn-primary desktop-btn-devis">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Demander un devis</span>
                </a>
                <button class="mobile-toggle" id="mobile-toggle-btn" aria-label="Menu Mobile"><i class="fa-solid fa-bars"></i></button>
            </div>
            
            <a href="index.php#devis" class="btn btn-primary mobile-btn-devis">
                <i class="fa-solid fa-file-signature"></i> Devis
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="product-hero">
        <div class="container">
            <div class="tag"><i class="fa-solid fa-circle-info"></i> Détails du Produit</div>
            <h1 style="font-size: 2.6rem; margin: 15px 0; color: #fff;">Fiche <span class="text-gold">Produit</span></h1>
            <a href="catalogue.php" class="btn btn-secondary" style="margin-top: 15px;"><i class="fa-solid fa-arrow-left"></i> Retour au catalogue</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="section-padding" style="padding-top: 40px; padding-bottom: 60px;">
        <div class="container">
            
            <?php if ($error): ?>
                <div style="text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: 12px; border: 1px dashed #ef233c;">
                    <div style="font-size: 3rem; color: #ef233c; margin-bottom: 15px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h3 style="color: #fff; margin-bottom: 10px;">Erreur</h3>
                    <p style="color: #94a3b8;"><?= htmlspecialchars($error) ?></p>
                    <a href="catalogue.php" class="btn btn-primary" style="margin-top: 20px;">Voir tous les produits</a>
                </div>
            <?php else: ?>
                <div class="product-details-container">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($titleText) ?>">
                        <?php else: ?>
                            <div style="color: #64748b; font-size: 4rem;"><i class="fa-solid fa-image"></i></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <span class="brand-tag"><?= htmlspecialchars($product['brand'] ?? $product['category'] ?? 'Produit') ?></span>
                        <h1><?= htmlspecialchars($titleText) ?></h1>
                        
                        <div class="price">
                            <?= htmlspecialchars($priceText) ?>
                            <span class="tire-badge <?= htmlspecialchars($badgeClass) ?>" style="font-size: 0.9rem; padding: 4px 10px; position: relative; top: auto; right: auto; margin-left: 10px;"><?= htmlspecialchars($badgeText) ?></span>
                        </div>
                        
                        <?php if (!empty($product['description'])): ?>
                            <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <?php endif; ?>
                        
                        <ul class="product-specs">
                            <?php if ($type === 'tire'): ?>
                                <li><span class="label">Catégorie</span> <span class="value"><?= htmlspecialchars(strtoupper($product['category'] ?? '')) ?></span></li>
                                <li><span class="label">Largeur</span> <span class="value"><?= htmlspecialchars($product['width']) ?></span></li>
                                <li><span class="label">Série (Hauteur)</span> <span class="value"><?= htmlspecialchars($product['ratio']) ?></span></li>
                                <li><span class="label">Diamètre</span> <span class="value"><?= htmlspecialchars($product['rim']) ?></span></li>
                            <?php elseif ($type === 'rim'): ?>
                                <li><span class="label">Diamètre</span> <span class="value"><?= htmlspecialchars($product['diameter']) ?></span></li>
                                <li><span class="label">Entraxe</span> <span class="value"><?= htmlspecialchars($product['bolt_pattern']) ?></span></li>
                                <li><span class="label">Matière</span> <span class="value"><?= htmlspecialchars($product['type']) ?></span></li>
                            <?php elseif ($type === 'accessory'): ?>
                                <li><span class="label">Catégorie</span> <span class="value"><?= htmlspecialchars($product['category']) ?></span></li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="product-actions">
                            <a href="#quick-order" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 1.1rem; padding: 12px;">
                                <i class="fa-solid fa-cart-shopping"></i> Commander
                            </a>
                            <a href="https://wa.me/2250709105592?text=<?= urlencode('Bonjour, je suis intéressé par ce produit : ' . $titleText . ' (' . $priceText . '). Lien: https://adamspneumatique.ci/produit.php?type=' . $type . '&id=' . $id) ?>" target="_blank" class="btn btn-secondary" style="flex: 1; text-align: center; font-size: 1.1rem; padding: 12px; background: #25D366; border-color: #25D366; color: white;">
                                <i class="fa-brands fa-whatsapp"></i> Poser une question
                            </a>
                        </div>

                        <div id="quick-order" style="margin-top: 35px; background: rgba(0,0,0,0.2); padding: 25px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.05);">
                            <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.2rem;"><i class="fa-solid fa-bolt text-gold"></i> Commande Rapide</h3>
                            <form id="quick-order-form">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 5px; display: block;">Nom & Prénom</label>
                                        <input type="text" class="form-control" id="qo-name" placeholder="Votre nom complet" required style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 5px; display: block;">Téléphone</label>
                                        <input type="tel" class="form-control" id="qo-phone" placeholder="Votre numéro" required style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 5px; display: block;">Quantité</label>
                                        <input type="number" class="form-control" id="qo-qty" min="1" max="20" value="1" required style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 5px; display: block;">Total Estimatif</label>
                                        <div class="form-control" id="qo-total" style="background: rgba(0,0,0,0.3); color: var(--primary-gold); font-weight: bold; border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center;">
                                            <?= htmlspecialchars($priceText) ?>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="qo-submit-wa" class="btn btn-whatsapp" style="width: 100%; font-size: 1.05rem; padding: 12px; display: flex; justify-content: center; align-items: center; gap: 10px;">
                                    <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i> Envoyer ma commande
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </main>

    <!-- Floating WhatsApp Button -->
    <a href="#" target="_blank" class="floating-wa-btn" id="floating-wa-btn" aria-label="Discuter sur WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="logo" style="margin-bottom: 20px;">
                        <div class="logo-icon"><i class="fa-solid fa-dharmachakra"></i></div>
                        <div class="logo-text">
                            <h1>ADAMS PNEUMATIQUE</h1>
                            <span>SERVICES AUTOMOBILES</span>
                        </div>
                    </div>
                    <p>Le partenaire de référence pour la vente de pneus, jantes, batteries et géométrie 3D à Abidjan Treichville.</p>
                </div>

                <div class="footer-col">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="index.php#accueil">Accueil</a></li>
                        <li><a href="index.php#services">Nos Services</a></li>
                        <li><a href="catalogue.php">Catalogue Complet</a></li>
                        <li><a href="index.php#devis">Demande de Devis</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Services & Produits</h4>
                    <ul>
                        <li><a href="catalogue.php?type=tires">Pneus Neufs & Occasions</a></li>
                        <li><a href="catalogue.php?type=rims">Jantes Aluminium & Acier</a></li>
                        <li><a href="catalogue.php?type=accessories">Batteries & Accessoires</a></li>
                        <li><a href="index.php#services">Parallélisme & Géométrie 3D</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contact Rapide</h4>
                    <p>
                        <a href="#" target="_blank" style="color: var(--text-muted);" id="setting-footer-map-link">
                            <i class="fa-solid fa-location-dot"></i> <span id="setting-footer-address">Chargement...</span>
                        </a>
                    </p>
                    <p>
                        <a href="#" style="color: var(--text-muted);" id="setting-footer-phone-link">
                            <i class="fa-solid fa-phone"></i> Tél: <span id="setting-footer-phone-text">Chargement...</span>
                        </a>
                    </p>
                    <div style="margin-top: 15px;">
                        <a href="#" target="_blank" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.85rem;" id="setting-footer-facebook">
                            <i class="fa-brands fa-facebook"></i> Page Facebook
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Adams Pneumatique Services. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/main.js?v=<?= htmlspecialchars($cacheVer) ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique Formulaire Rapide
        const qtyInput = document.getElementById('qo-qty');
        const totalDisplay = document.getElementById('qo-total');
        const unitPrice = <?= json_encode((int)($product['price'] ?? 0)) ?>;
        
        if(qtyInput && totalDisplay) {
            qtyInput.addEventListener('input', function() {
                let qty = parseInt(this.value);
                if (isNaN(qty) || qty < 1) qty = 1;
                const total = qty * unitPrice;
                totalDisplay.textContent = total.toLocaleString('fr-FR') + ' FCFA';
            });
        }

        const submitWa = document.getElementById('qo-submit-wa');
        if(submitWa) {
            submitWa.addEventListener('click', function() {
                const name = document.getElementById('qo-name').value.trim();
                const phone = document.getElementById('qo-phone').value.trim();
                const qty = document.getElementById('qo-qty').value;
                
                if(!name || !phone) {
                    alert("Veuillez entrer votre nom et votre numéro de téléphone avant de continuer.");
                    document.getElementById('qo-name').focus();
                    return;
                }

                const productName = <?= json_encode($titleText) ?>;
                const productType = <?= json_encode($badgeText) ?>;
                const link = window.location.href;
                
                let message = "Bonjour Adams Pneumatique, je souhaite passer une commande.\n\n";
                message += "📦 *Détails du Produit :*\n";
                message += "- Désignation : " + productName + " (" + productType + ")\n";
                message += "- Quantité : " + qty + "\n";
                message += "- Lien : " + link + "\n\n";
                message += "👤 *Mes Coordonnées :*\n";
                message += "- Nom : " + name + "\n";
                message += "- Tél : " + phone + "\n";
                
                // Fetch the fallback wa number or the dynamic one
                const waNumber = (window.SITE_SETTINGS && window.SITE_SETTINGS['phone']) 
                    ? window.SITE_SETTINGS['phone'].replace(/\s/g, '').replace('+', '') 
                    : '2250709105592';
                
                const waUrl = "https://api.whatsapp.com/send?phone=" + waNumber + "&text=" + encodeURIComponent(message);
                window.open(waUrl, "_blank");
            });
        }
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
