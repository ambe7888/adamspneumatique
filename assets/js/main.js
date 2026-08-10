/**
 * ADAMS PNEUMATIQUE SERVICES - Interactive JavaScript Module
 * Strictly enforcement of security rules (No innerHTML, XSS safe DOM manipulation)
 */

let TIRE_DATABASE = [];
let TIRE_CATEGORIES = [];

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const [tiresRes, servicesRes, categoriesRes] = await Promise.all([
            fetch('admin/api.php?type=tires'),
            fetch('admin/api.php?type=services'),
            fetch('admin/api.php?type=categories')
        ]);
        
        if (categoriesRes.ok) {
            TIRE_CATEGORIES = await categoriesRes.json();
            renderDynamicCategories();
        }
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

function renderDynamicCategories() {
    const tabsContainer = document.getElementById('catalog-tabs-container');
    const selectCalc = document.getElementById('calc-category');

    if (tabsContainer) {
        tabsContainer.replaceChildren();
        
        const btnAll = document.createElement('button');
        btnAll.className = 'tab-btn active';
        btnAll.setAttribute('data-category', 'all');
        btnAll.innerHTML = '<i class="fa-solid fa-border-all"></i> Tous les Pneus';
        tabsContainer.appendChild(btnAll);

        TIRE_CATEGORIES.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'tab-btn';
            btn.setAttribute('data-category', cat.slug);
            btn.innerHTML = `<i class="fa-solid ${cat.icon}"></i> ${cat.name}`;
            tabsContainer.appendChild(btn);
        });
    }

    if (selectCalc) {
        selectCalc.replaceChildren();
        TIRE_CATEGORIES.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.slug;
            opt.setAttribute('data-price', cat.base_price);
            opt.textContent = `${cat.name} (env. ${parseInt(cat.base_price).toLocaleString('fr-FR')} FCFA/pneu)`;
            selectCalc.appendChild(opt);
        });
    }
}

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
        
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const detailsInput = document.getElementById('page-calc-details');
            if (detailsInput) {
                detailsInput.value = `Service : ${service.title}`;
            }
            const devisSec = document.getElementById('devis');
            if (devisSec) {
                devisSec.scrollIntoView({ behavior: 'smooth' });
            }
        });
        
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
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        
        let unitPrice = 30000;
        const detailsInput = document.getElementById('page-calc-details');
        
        if (detailsInput && detailsInput.hasAttribute('data-exact-price') && detailsInput.value.trim() !== '') {
            unitPrice = parseInt(detailsInput.getAttribute('data-exact-price'));
        } else if (selectedOption && selectedOption.hasAttribute('data-price')) {
            unitPrice = parseInt(selectedOption.getAttribute('data-price'));
        }

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
    categorySelect.addEventListener('change', () => {
        const detailsInput = document.getElementById('page-calc-details');
        if (detailsInput) detailsInput.removeAttribute('data-exact-price');
        calculate();
    });
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

        if (tire.image) {
            const tireImgWrapper = document.createElement('div');
            tireImgWrapper.style.textAlign = 'center';
            tireImgWrapper.style.margin = '15px 0';
            const tireImg = document.createElement('img');
            tireImg.src = tire.image;
            tireImg.alt = `${tire.brand} ${tire.model}`;
            tireImg.style.maxWidth = '100%';
            tireImg.style.height = 'auto';
            tireImg.style.maxHeight = '150px';
            tireImg.style.objectFit = 'contain';
            tireImg.style.borderRadius = '8px';
            tireImgWrapper.appendChild(tireImg);
            itemCard.appendChild(tireImgWrapper);
        }

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
                detailsInput.setAttribute('data-exact-price', tire.price);
            }
            
            const catSelect = document.getElementById('calc-category');
            if (catSelect) {
                catSelect.value = tire.category;
            }
            
            const qtyInput = document.getElementById('calc-qty');
            if (qtyInput) {
                qtyInput.dispatchEvent(new Event('input'));
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
