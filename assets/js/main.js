/**
 * ADAMS PNEUMATIQUE SERVICES - Interactive JavaScript Module
 * Strictly enforcement of security rules (No innerHTML, XSS safe DOM manipulation)
 */

let TIRE_DATABASE = [];
let TIRE_CATEGORIES = [];

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const t = Date.now();
        const [tiresRes, servicesRes, categoriesRes, extraRes, testiRes, settingsRes, locationsRes] = await Promise.all([
            fetch(`admin/api.php?type=tires&t=${t}`),
            fetch(`admin/api.php?type=services&t=${t}`),
            fetch(`admin/api.php?type=categories&t=${t}`),
            fetch(`admin/api.php?type=extra_services&t=${t}`),
            fetch(`admin/api.php?type=testimonials&t=${t}`),
            fetch(`admin/api.php?type=settings&t=${t}`),
            fetch(`admin/api.php?type=locations&t=${t}`)
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
        if (extraRes.ok) {
            const extraData = await extraRes.json();
            window.EXTRA_SERVICES = extraData;
            renderExtraServices(extraData);
        }
        if (testiRes.ok) {
            const testiData = await testiRes.json();
            renderTestimonials(testiData);
        }
        if (settingsRes.ok) {
            const settingsData = await settingsRes.json();
            applySettings(settingsData);
        }
        if (locationsRes.ok) {
            const locationsData = await locationsRes.json();
            renderLocations(locationsData);
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
window.calculateDevis = function() {
    const qtyInput = document.getElementById('calc-qty');
    const categorySelect = document.getElementById('calc-category');
    const totalDisplay = document.getElementById('calc-total-display');
    if (!qtyInput || !totalDisplay) return;

    const qty = parseInt(qtyInput.value) || 1;
    let unitPrice = 30000;
    
    if (categorySelect) {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const detailsInput = document.getElementById('page-calc-details');
        
        if (detailsInput && detailsInput.hasAttribute('data-exact-price') && detailsInput.value.trim() !== '') {
            unitPrice = parseInt(detailsInput.getAttribute('data-exact-price'));
        } else if (selectedOption && selectedOption.hasAttribute('data-price')) {
            unitPrice = parseInt(selectedOption.getAttribute('data-price'));
        }
    }

    let total = qty * unitPrice;

    // Ajouter le prix des options cochées
    const checkboxes = document.querySelectorAll('.extra-service-check');
    checkboxes.forEach(chk => {
        if (chk.checked) {
            const price = parseInt(chk.getAttribute('data-price')) || 0;
            const pType = chk.getAttribute('data-type');
            if (pType === 'per_tire') {
                total += qty * price;
            } else {
                total += price;
            }
        }
    });

    totalDisplay.textContent = total.toLocaleString('fr-FR') + ' FCFA';
};

function initDevisCalculator() {
    const qtyInput = document.getElementById('calc-qty');
    const categorySelect = document.getElementById('calc-category');

    if (qtyInput) qtyInput.addEventListener('input', window.calculateDevis);
    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            const detailsInput = document.getElementById('page-calc-details');
            if (detailsInput) detailsInput.removeAttribute('data-exact-price');
            window.calculateDevis();
        });
    }
    
    const container = document.getElementById('extra-services-container');
    if (container) {
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('extra-service-check')) {
                window.calculateDevis();
            }
        });
    }

    window.calculateDevis();
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

/* -------------------------------------------------------------------------- */
/* 7. Dynamic Data Renderers                                                  */
/* -------------------------------------------------------------------------- */
function renderExtraServices(services) {
    const container = document.getElementById('extra-services-container');
    if (!container) return;
    
    container.replaceChildren();
    
    services.forEach(s => {
        if (s.is_hidden == 1) return;
        
        const label = document.createElement('label');
        label.style.fontWeight = '400';
        label.style.fontSize = '0.95rem';
        label.style.cursor = 'pointer';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'extra-service-check';
        checkbox.setAttribute('data-price', s.price);
        checkbox.setAttribute('data-type', s.price_type);
        if (s.is_checked == 1) checkbox.checked = true;
        
        const priceLabel = s.price_type === 'per_tire' ? `${s.price} FCFA / pneu` : `${s.price} FCFA Forfait`;
        
        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(` ${s.title} (+${priceLabel})`));
        
        container.appendChild(label);
    });
    
    if (window.calculateDevis) window.calculateDevis();
}

function renderTestimonials(testimonials) {
    const container = document.getElementById('testimonials-container');
    if (!container) return;
    
    container.replaceChildren();
    
    testimonials.forEach(t => {
        if (t.is_hidden == 1) return;
        
        const card = document.createElement('div');
        card.className = 'review-card';
        
        let starsHtml = '';
        const sCount = parseInt(t.stars) || 5;
        for (let i = 0; i < sCount; i++) {
            starsHtml += '<i class="fa-solid fa-star"></i>';
        }
        
        const firstLetter = t.author ? t.author.charAt(0).toUpperCase() : '?';
        
        card.innerHTML = `
            <div class="stars">${starsHtml}</div>
            <p class="review-text">"${t.text}"</p>
            <div class="reviewer">
                <div class="reviewer-avatar">${firstLetter}</div>
                <div>
                    <div style="font-weight: 700;">${t.author}</div>
                    <div style="font-size: 0.8rem; color: #94a3b8;">${t.role}</div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function applySettings(settingsData) {
    const settings = {};
    settingsData.forEach(s => {
        settings[s.setting_key] = s.setting_value;
    });
    
    if (settings.address) {
        const addressText = document.getElementById('setting-address-text');
        if (addressText) addressText.textContent = settings.address;
        
        const footerAddress = document.getElementById('setting-footer-address');
        if (footerAddress) footerAddress.textContent = settings.address;
    }
    
    if (settings.phone) {
        const phoneText = document.getElementById('setting-phone-text');
        if (phoneText) phoneText.textContent = settings.phone;
        
        const footerPhoneText = document.getElementById('setting-footer-phone-text');
        if (footerPhoneText) footerPhoneText.textContent = settings.phone;
    }
    
    if (settings.whatsapp) {
        const phoneLink = document.getElementById('setting-phone-link');
        if (phoneLink) phoneLink.href = 'tel:+' + settings.whatsapp.replace(/\s/g, '');
        
        const footerPhoneLink = document.getElementById('setting-footer-phone-link');
        if (footerPhoneLink) footerPhoneLink.href = 'tel:+' + settings.whatsapp.replace(/\s/g, '');
        
        const waBtn = document.getElementById('floating-wa-btn');
        if (waBtn) waBtn.href = 'https://wa.me/' + settings.whatsapp.replace(/\s/g, '');
    }
    
    if (settings.working_hours) {
        const hoursDiv = document.getElementById('setting-working-hours');
        if (hoursDiv) hoursDiv.textContent = settings.working_hours;
    }
    
    if (settings.map_url) {
        const embed = document.getElementById('setting-map-embed');
        if (embed) embed.src = settings.map_url;
        
        const addressLink = document.getElementById('setting-address-link');
        if (addressLink) addressLink.href = settings.map_url;
        
        const footerMapLink = document.getElementById('setting-footer-map-link');
        if (footerMapLink) footerMapLink.href = settings.map_url;
    }
    
    if (settings.video_url && settings.video_url.trim() !== '') {
        let url = settings.video_url;
        let videoId = '';
        if (url.includes('youtube.com/watch?v=')) {
            videoId = url.split('v=')[1].split('&')[0];
        } else if (url.includes('youtu.be/')) {
            videoId = url.split('youtu.be/')[1].split('?')[0];
        } else if (url.includes('youtube.com/embed/')) {
            videoId = url.split('embed/')[1].split('?')[0];
        }
        
        if (videoId) {
            const section = document.getElementById('presentation-video-section');
            const iframe = document.getElementById('presentation-video-iframe');
            if (section && iframe) {
                iframe.src = `https://www.youtube.com/embed/${videoId}?rel=0`;
                section.style.display = 'block';
            }
        }
    }
    
    if (settings.facebook_url) {
        const fbBtn = document.getElementById('setting-footer-facebook');
        if (fbBtn) {
            if (settings.facebook_url.trim() === '') {
                fbBtn.style.display = 'none';
            } else {
                fbBtn.href = settings.facebook_url;
                fbBtn.style.display = 'inline-block';
            }
        }
    }
}

function renderLocations(locations) {
    const desktopBar = document.getElementById('dynamic-top-bar-desktop');
    const mobileBar = document.getElementById('dynamic-top-bar-mobile');
    
    if (!desktopBar || !mobileBar) return;
    if (!locations || locations.length === 0) {
        desktopBar.innerHTML = '<span><i class="fa-solid fa-clock"></i> Aucun horaire défini.</span>';
        mobileBar.innerHTML = '<span>Aucun horaire défini.</span>';
        return;
    }
    
    desktopBar.replaceChildren();
    
    // Desktop layout
    locations.forEach(loc => {
        const item = document.createElement('div');
        item.className = 'location-item';
        
        let mapLink = loc.map_url ? `href="${loc.map_url}" target="_blank"` : `href="#"`;
        let phoneLink = loc.phone ? `href="tel:${loc.phone.replace(/\s/g, '')}"` : `href="#"`;
        
        item.innerHTML = `
            <a ${mapLink} style="color: var(--text-muted); text-decoration: none;" class="location-name">
                <i class="fa-solid fa-location-dot"></i> ${loc.name}
            </a>
            <a ${phoneLink} style="color: var(--text-muted); text-decoration: none;">
                <i class="fa-solid fa-phone"></i> ${loc.phone}
            </a>
            <span style="color: var(--accent-green);"><i class="fa-solid fa-clock"></i> ${loc.hours}</span>
        `;
        desktopBar.appendChild(item);
    });
    
    // Mobile layout (Ticker / Scroller or just first element with a "Voir +")
    mobileBar.replaceChildren();
    
    // We'll create a simple automatic slider if there's more than 1, otherwise just display the first one.
    if (locations.length === 1) {
        const loc = locations[0];
        mobileBar.innerHTML = `<span style="color: var(--accent-green);">${loc.hours}</span> <span style="margin: 0 4px; color: var(--border-color);">/</span> <a href="${loc.map_url}" target="_blank" style="color: #fff;">${loc.name}</a> <span style="margin: 0 4px; color: var(--border-color);">/</span> <a href="tel:${loc.phone.replace(/\s/g, '')}" style="color: var(--primary-gold); font-weight: 700;">${loc.phone}</a>`;
    } else {
        const slider = document.createElement('div');
        slider.style.display = 'flex';
        slider.style.transition = 'transform 0.5s ease-in-out';
        slider.style.width = '100%';
        
        locations.forEach(loc => {
            const slide = document.createElement('div');
            slide.style.minWidth = '100%';
            slide.style.textAlign = 'center';
            slide.innerHTML = `<span style="color: var(--accent-green);">${loc.hours}</span> <span style="margin: 0 4px; color: var(--border-color);">/</span> <a href="${loc.map_url}" target="_blank" style="color: #fff;">${loc.name}</a> <span style="margin: 0 4px; color: var(--border-color);">/</span> <a href="tel:${loc.phone.replace(/\s/g, '')}" style="color: var(--primary-gold); font-weight: 700;">${loc.phone}</a>`;
            slider.appendChild(slide);
        });
        
        mobileBar.style.overflow = 'hidden';
        mobileBar.appendChild(slider);
        
        let currentSlide = 0;
        setInterval(() => {
            currentSlide = (currentSlide + 1) % locations.length;
            slider.style.transform = \`translateX(-\${currentSlide * 100}%)\`;
        }, 4000); // Change every 4 seconds
    }
}
