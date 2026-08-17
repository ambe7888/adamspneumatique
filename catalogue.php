<?php
$cacheVersionFile = __DIR__ . '/assets/cache_version.txt';
$cacheVer = file_exists($cacheVersionFile) ? trim(file_get_contents($cacheVersionFile)) : '4.0';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue Complet | Pneus, Jantes & Accessoires - Adams Pneumatique Services Abidjan</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Explorez l'ensemble de notre catalogue de pneus neufs et d'occasion, jantes aluminium/acier et accessoires automobiles chez Adams Pneumatique à Abidjan Treichville.">
    <meta name="keywords" content="catalogue pneus abidjan, jantes alu abidjan, accessoires auto abidjan, prix pneus treichville, batteries varta abidjan">
    <meta name="author" content="Adams Pneumatique Services">
    <meta name="robots" content="index, follow">
    
    <!-- Font Awesome Icons 6.5.1 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= htmlspecialchars($cacheVer) ?>">
    
    <style>
        /* Styles spécifiques pour la page Catalogue */
        .catalog-hero {
            padding: 130px 0 45px 0;
            background: radial-gradient(circle at 50% 20%, rgba(239, 35, 60, 0.12) 0%, rgba(15, 23, 42, 0.95) 70%);
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        .catalog-filter-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 25px;
            margin-top: -30px;
            margin-bottom: 35px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            position: relative;
            z-index: 10;
        }
        .catalog-type-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        .catalog-type-btn {
            padding: 12px 24px;
            border-radius: var(--radius-full);
            background: #1e293b;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .catalog-type-btn:hover {
            color: #ffffff;
            border-color: var(--primary-gold);
            background: #334155;
        }
        .catalog-type-btn.active {
            background: var(--grad-gold);
            color: var(--text-dark);
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(255, 183, 3, 0.35);
        }
        .search-and-sort {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .search-and-sort {
                grid-template-columns: 1fr;
            }
        }
        .sub-filters-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            padding-top: 15px;
            border-top: 1px dashed rgba(255,255,255,0.1);
        }
        .catalog-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .results-count {
            font-size: 1.1rem;
            color: #94a3b8;
        }
        .results-count strong {
            color: var(--primary-gold);
        }
        .reset-filter-btn {
            background: transparent;
            border: 1px solid #ef233c;
            color: #ef233c;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reset-filter-btn:hover {
            background: #ef233c;
            color: white;
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
                <a href="catalogue.php" class="active">Catalogue Complet</a>
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

    <!-- Catalog Hero Section -->
    <section class="catalog-hero">
        <div class="container">
            <div class="tag"><i class="fa-solid fa-boxes-stacked"></i> Notre Stock Disponible</div>
            <h1 style="font-size: 2.6rem; margin: 15px 0; color: #fff;">Catalogue <span class="text-gold">Pneus, Jantes & Accessoires</span></h1>
            <p style="max-width: 650px; margin: 0 auto; color: var(--text-muted); font-size: 1.05rem;">
                Consultez l'ensemble de nos références certifiées avec prix transparents en FCFA. Utilisez les filtres ci-dessous pour trouver exactement votre dimension ou votre équipement.
            </p>
        </div>
    </section>

    <!-- Main Content / Filters & Grid -->
    <main class="section-padding" style="padding-top: 0;">
        <div class="container">
            
            <!-- Filters Card -->
            <div class="catalog-filter-card">
                <!-- Type Selection Tabs -->
                <div class="catalog-type-tabs">
                    <button class="catalog-type-btn active" data-type="all">
                        <i class="fa-solid fa-border-all"></i> Tous les Produits
                    </button>
                    <button class="catalog-type-btn" data-type="tires">
                        <i class="fa-solid fa-dharmachakra"></i> Pneus
                    </button>
                    <button class="catalog-type-btn" data-type="rims">
                        <i class="fa-solid fa-life-ring"></i> Jantes
                    </button>
                    <button class="catalog-type-btn" data-type="accessories">
                        <i class="fa-solid fa-car-battery"></i> Accessoires
                    </button>
                </div>

                <!-- Global Search & Sort -->
                <div class="search-and-sort">
                    <div class="form-group" style="margin-bottom:0;">
                        <input type="text" id="catalog-search-input" class="form-control" placeholder="🔍 Rechercher par marque, modèle, référence, dimension..." style="font-size: 1rem; padding: 12px 16px;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <select id="catalog-sort-select" class="form-control" style="font-size: 0.95rem; padding: 12px 16px;">
                            <option value="default">Trier par : Recommandés</option>
                            <option value="price_asc">Prix : Moins cher au plus cher</option>
                            <option value="price_desc">Prix : Plus cher au moins cher</option>
                            <option value="brand_asc">Marque / Nom : A à Z</option>
                        </select>
                    </div>
                </div>

                <!-- Sub Filters: Tires (Visible when type == 'tires' or 'all') -->
                <div id="sub-filters-tires" class="sub-filters-container">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Catégorie Pneu</label>
                        <select id="filter-tire-category" class="form-control">
                            <option value="all">Toutes catégories</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">État</label>
                        <select id="filter-tire-condition" class="form-control">
                            <option value="all">Neuf & Occasion</option>
                            <option value="new">Neuf uniquement</option>
                            <option value="used">Occasion uniquement</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Largeur</label>
                        <select id="filter-tire-width" class="form-control">
                            <option value="all">Toutes largeurs</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Série (Hauteur)</label>
                        <select id="filter-tire-ratio" class="form-control">
                            <option value="all">Toutes séries</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Diamètre (Jante)</label>
                        <select id="filter-tire-rim" class="form-control">
                            <option value="all">Tous diamètres</option>
                        </select>
                    </div>
                </div>

                <!-- Sub Filters: Rims (Visible when type == 'rims') -->
                <div id="sub-filters-rims" class="sub-filters-container" style="display: none;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Diamètre Jante</label>
                        <select id="filter-rim-diameter" class="form-control">
                            <option value="all">Tous diamètres</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Entraxe</label>
                        <select id="filter-rim-bolt" class="form-control">
                            <option value="all">Tous entraxes</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Matériau</label>
                        <select id="filter-rim-type" class="form-control">
                            <option value="all">Tous types (Alu/Acier)</option>
                            <option value="Aluminium">Aluminium (Alu)</option>
                            <option value="Acier">Acier (Tôle)</option>
                        </select>
                    </div>
                </div>

                <!-- Sub Filters: Accessories (Visible when type == 'accessories') -->
                <div id="sub-filters-accessories" class="sub-filters-container" style="display: none;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size: 0.8rem; color: #94a3b8;">Catégorie d'accessoire</label>
                        <select id="filter-acc-category" class="form-control">
                            <option value="all">Toutes catégories</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Results Info Header -->
            <div class="catalog-results-header">
                <div class="results-count">
                    <span id="results-count-text">Chargement des produits...</span>
                </div>
                <button id="reset-filters-btn" class="reset-filter-btn">
                    <i class="fa-solid fa-rotate-left"></i> Réinitialiser les filtres
                </button>
            </div>

            <!-- Products Grid Container -->
            <div class="catalog-grid" id="full-catalog-grid">
                <!-- Injected by JavaScript -->
            </div>

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
    <script src="assets/js/catalog.js?v=<?= htmlspecialchars($cacheVer) ?>"></script>
</body>
</html>