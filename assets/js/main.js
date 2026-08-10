/**
 * ADAMS PNEUMATIQUE SERVICES - Interactive JavaScript Module
 * Strictly enforcement of security rules (No innerHTML, XSS safe DOM manipulation)
 */

let TIRE_DATABASE = [];

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const [tiresRes, servicesRes] = await Promise.all([
            fetch('admin/api.php?type=tires'),
            fetch('admin/api.php?type=services')
        ]);
        
        if (tiresRes.ok) TIRE_DATABASE = await tiresRes.json();
        if (servicesRes.ok) {
            const servicesData = await servicesRes.json();
            renderServices(servicesData);
        }
    } catch (error) {
        console.error("Erreur lors du chargement des données:", error);
    }

    initTireFinder();
    initDevisCalculator();
    initCatalogFilter();
    initMobileNav();
    initSmoothScroll();
    initPageDevisSubmission();
});

function renderServices(services) {
    const container = document.getElementById('services-grid-container');
    if (!container) return;
    
    container.replaceChildren();
    
    services.forEach(service => {
        const card = document.createElement('div');
        card.className = 'service-card';
        
        const imgDiv = document.createElement('div');
        imgDiv.className = 'service-img';
        const img = document.createElement('img');
        img.src = service.image;
        img.alt = service.title;
        imgDiv.appendChild(img);
        
        const bodyDiv = document.createElement('div');
        bodyDiv.className = 'service-body';
        
        const iconDiv = document.createElement('div');
        iconDiv.className = 'service-icon';
        const icon = document.createElement('i');
        icon.className = `fa-solid ${service.icon}`;
        iconDiv.appendChild(icon);
        
        const title = document.createElement('h3');
        title.textContent = service.title;
        
        const desc = document.createElement('p');
        desc.textContent = service.desc;
        
        const link = document.createElement('a');
        link.className = 'service-link';
        link.href = service.link;
        link.textContent = service.linkText + ' ';
        const linkIcon = document.createElement('i');
        linkIcon.className = 'fa-solid fa-arrow-right';
        link.appendChild(linkIcon);
        
        bodyDiv.appendChild(iconDiv);
        bodyDiv.appendChild(title);
        bodyDiv.appendChild(desc);
        bodyDiv.appendChild(link);
        
        card.appendChild(imgDiv);
        card.appendChild(bodyDiv);
        
        container.appendChild(card);
    });
}

/* -------------------------------------------------------------------------- */
/* 1. Tire Finder Logic                                                      */
/* -------------------------------------------------------------------------- */
function initTireFinder() {
    const finderForm = document.getElementById('tire-finder-form');
    if (!finderForm) return;

    finderForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const width = document.getElementById('search-width').value;
        const ratio = document.getElementById('search-ratio').value;
        const rim = document.getElementById('search-rim').value;
        const type = document.getElementById('search-type').value;

        const filtered = TIRE_DATABASE.filter(tire => {
            const matchWidth = !width || tire.width === width;
            const matchRatio = !ratio || tire.ratio === ratio;
            const matchRim = !rim || tire.rim === rim;
            const matchType = !type || tire.category === type;
            return matchWidth && matchRatio && matchRim && matchType;
        });

        renderCatalogItems(filtered);

        const catalogSec = document.getElementById('catalogue');
        if (catalogSec) {
            catalogSec.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

/* -------------------------------------------------------------------------- */
/* 2. Interactive Devis Calculator (Calcul en temps réel)                   */
/* -------------------------------------------------------------------------- */
function initDevisCalculator() {
    const qtyInput = document.getElementById('calc-qty');
    const categorySelect = document.getElementById('calc-category');
    const montageCheck = document.getElementById('calc-montage');
    const geometrieCheck = document.getElementById('calc-geometrie');
    const azoteCheck = document.getElementById('calc-azote');
    const totalDisplay = document.getElementById('calc-total-display');

    if (!qtyInput || !totalDisplay) return;

    function calculate() {
        const qty = parseInt(qtyInput.value) || 1;
        const category = categorySelect.value;
        
        let unitPrice = 30000;
        if (category === 'suv') unitPrice = 55000;
        if (category === 'utilitaire') unitPrice = 45000;
        if (category === 'poids-lourd') unitPrice = 120000;

        let total = qty * unitPrice;

        if (montageCheck && montageCheck.checked) {
            total += qty * 2500;
        }

        if (geometrieCheck && geometrieCheck.checked) {
            total += 15000;
        }

        if (azoteCheck && azoteCheck.checked) {
            total += qty * 1000;
        }

        totalDisplay.textContent = total.toLocaleString('fr-FR') + ' FCFA';
    }

    qtyInput.addEventListener('input', calculate);
    categorySelect.addEventListener('change', calculate);
    if (montageCheck) montageCheck.addEventListener('change', calculate);
    if (geometrieCheck) geometrieCheck.addEventListener('change', calculate);
    if (azoteCheck) azoteCheck.addEventListener('change', calculate);

    calculate();
}

/* -------------------------------------------------------------------------- */
/* 3. Catalog Render & Tab Filter (Secure Safe DOM)                           */
/* -------------------------------------------------------------------------- */
function initCatalogFilter() {
    renderCatalogItems(TIRE_DATABASE);

    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filterCategory = btn.getAttribute('data-category');
            if (filterCategory === 'all') {
                renderCatalogItems(TIRE_DATABASE);
            } else {
                const filtered = TIRE_DATABASE.filter(t => t.category === filterCategory || t.condition === filterCategory);
                renderCatalogItems(filtered);
            }
        });
    });
}

function renderCatalogItems(items) {
    const gridContainer = document.getElementById('catalog-grid-container');
    if (!gridContainer) return;

    gridContainer.replaceChildren();

    if (items.length === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'no-results';
        emptyMsg.style.gridColumn = '1 / -1';
        emptyMsg.style.textAlign = 'center';
        emptyMsg.style.padding = '40px';
        emptyMsg.style.color = '#94a3b8';
        emptyMsg.textContent = 'Aucun pneu ne correspond à vos critères exacts. Contactez-nous pour une recherche spécifique !';
        gridContainer.appendChild(emptyMsg);
        return;
    }

    items.forEach(tire => {
        const itemCard = document.createElement('div');
        itemCard.className = 'tire-item';

        const itemHeader = document.createElement('div');
        itemHeader.className = 'tire-item-header';

        const brandTag = document.createElement('span');
        brandTag.className = 'tire-brand-tag';
        brandTag.textContent = tire.brand;

        const badge = document.createElement('span');
        badge.className = tire.condition === 'new' ? 'tire-badge badge-new' : 'tire-badge badge-occ';
        badge.textContent = tire.condition === 'new' ? 'Neuf' : 'Occasion';

        itemHeader.appendChild(brandTag);
        itemHeader.appendChild(badge);
        itemCard.appendChild(itemHeader);

        const title = document.createElement('h4');
        title.textContent = `${tire.width}/${tire.ratio} ${tire.rim} - ${tire.model}`;
        itemCard.appendChild(title);

        const specs = document.createElement('div');
        specs.className = 'tire-specs';
        const spec1 = document.createElement('span');
        spec1.textContent = `Catégorie: ${tire.category.toUpperCase()}`;
        specs.appendChild(spec1);
        itemCard.appendChild(specs);

        const desc = document.createElement('p');
        desc.style.fontSize = '0.85rem';
        desc.style.color = '#94a3b8';
        desc.style.marginBottom = '15px';
        desc.textContent = tire.desc;
        itemCard.appendChild(desc);

        const priceRow = document.createElement('div');
        priceRow.className = 'tire-price';

        const priceVal = document.createElement('div');
        priceVal.className = 'price-val';
        priceVal.textContent = `${tire.price.toLocaleString('fr-FR')} FCFA`;

        const orderBtn = document.createElement('a');
        orderBtn.className = 'btn btn-primary';
        orderBtn.style.padding = '8px 14px';
        orderBtn.style.fontSize = '0.8rem';
        orderBtn.href = '#devis';

        const iconCalc = document.createElement('i');
        iconCalc.className = 'fa-solid fa-calculator';
        iconCalc.style.marginRight = '6px';
        orderBtn.appendChild(iconCalc);

        const btnText = document.createTextNode('Commander');
        orderBtn.appendChild(btnText);

        orderBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const detailsInput = document.getElementById('page-calc-details');
            if (detailsInput) {
                detailsInput.value = `${tire.brand} ${tire.width}/${tire.ratio} ${tire.rim} (${tire.model})`;
            }
            const devisSec = document.getElementById('devis');
            if (devisSec) {
                devisSec.scrollIntoView({ behavior: 'smooth' });
            }
        });

        priceRow.appendChild(priceVal);
        priceRow.appendChild(orderBtn);

        itemCard.appendChild(priceRow);

        gridContainer.appendChild(itemCard);
    });
}

/* -------------------------------------------------------------------------- */
/* 4. Page Devis Form Submission (WhatsApp & Email Options)                   */
/* -------------------------------------------------------------------------- */
function initPageDevisSubmission() {
    const pageForm = document.getElementById('page-devis-form');
    const btnWa = document.getElementById('btn-submit-form-whatsapp');
    const responseMsg = document.getElementById('page-devis-response-msg');

    if (!pageForm) return;

    if (btnWa) {
        btnWa.addEventListener('click', () => {
            const name = document.getElementById('page-calc-name').value.trim();
            const phone = document.getElementById('page-calc-phone').value.trim();
            const categorySelect = document.getElementById('calc-category');
            const categoryText = categorySelect ? categorySelect.options[categorySelect.selectedIndex].text : '';
            const details = document.getElementById('page-calc-details').value.trim();
            const qty = document.getElementById('calc-qty').value;

            const montageCheck = document.getElementById('calc-montage');
            const geometrieCheck = document.getElementById('calc-geometrie');
            const azoteCheck = document.getElementById('calc-azote');
            const totalDisplay = document.getElementById('calc-total-display');

            if (!name || !phone) {
                alert('Veuillez remplir au moins votre Nom et Numéro de téléphone.');
                document.getElementById('page-calc-name').focus();
                return;
            }

            const formattedMsg = `Bonjour Adams Pneumatique, je souhaite recevoir un devis :\n\n` +
                                 `👤 Nom : ${name}\n` +
                                 `📞 Téléphone : ${phone}\n` +
                                 `🚗 Catégorie : ${categoryText}\n` +
                                 `🔧 Pneu / Dimensions : ${details || 'Non précisé'}\n` +
                                 `🔢 Quantité : ${qty} pneu(s)\n` +
                                 `🛠️ Montage/Équilibrage : ${montageCheck && montageCheck.checked ? 'Oui' : 'Non'}\n` +
                                 `📐 Géométrie 3D : ${geometrieCheck && geometrieCheck.checked ? 'Oui' : 'Non'}\n` +
                                 `💨 Gonflage Azote : ${azoteCheck && azoteCheck.checked ? 'Oui' : 'Non'}\n` +
                                 `💰 Estimation Total TTC : ${totalDisplay ? totalDisplay.textContent : ''}`;

            window.open(`https://wa.me/2250709105592?text=${encodeURIComponent(formattedMsg)}`, '_blank');
        });
    }

    pageForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const name = document.getElementById('page-calc-name').value.trim();
        const phone = document.getElementById('page-calc-phone').value.trim();
        const categorySelect = document.getElementById('calc-category');
        const categoryText = categorySelect ? categorySelect.options[categorySelect.selectedIndex].text : '';
        const details = document.getElementById('page-calc-details').value.trim();
        const qty = document.getElementById('calc-qty').value;
        const totalDisplay = document.getElementById('calc-total-display');

        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('service', `Devis pour ${categoryText} (Qté: ${qty})`);
        formData.append('message', `Dimensions/Détails: ${details}\nEstimation Total: ${totalDisplay ? totalDisplay.textContent : ''}`);

        fetch('send_mail.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!responseMsg) return;
            responseMsg.style.display = 'block';
            if (data.status === 'success') {
                responseMsg.style.color = '#06d6a0';
                responseMsg.textContent = 'Votre demande de devis a été envoyée par email avec succès !';
                pageForm.reset();
            } else {
                responseMsg.style.color = '#ef233c';
                responseMsg.textContent = data.message;
            }
        })
        .catch(() => {
            if (!responseMsg) return;
            responseMsg.style.display = 'block';
            responseMsg.style.color = '#ef233c';
            responseMsg.textContent = 'Erreur lors de l envoi email. Veuillez utiliser le bouton WhatsApp.';
        });
    });
}

/* -------------------------------------------------------------------------- */
/* 5. Mobile Navigation Drawer Toggle                                         */
/* -------------------------------------------------------------------------- */
function initMobileNav() {
    const toggleBtn = document.getElementById('mobile-toggle-btn');
    const navLinks = document.getElementById('nav-links-menu');

    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-active');
        });

        // Close drawer when clicking any link inside
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('mobile-active');
            });
        });
    }
}

/* -------------------------------------------------------------------------- */
/* 6. Smooth Scroll Helper                                                    */
/* -------------------------------------------------------------------------- */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                targetEl.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}
