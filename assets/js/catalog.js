/**
 * ADAMS PNEUMATIQUE SERVICES - Catalog Page Module (catalog.js)
 * Real-time reactive multi-criteria filtering for Tires, Rims, and Accessories.
 */

document.addEventListener('DOMContentLoaded', async () => {
    // Ne s'exécute que sur la page catalogue
    const gridContainer = document.getElementById('full-catalog-grid');
    if (!gridContainer) return;

    const BASE_URL = window.location.origin + '/';
    const apiBase = BASE_URL + 'admin/api.php';
    const t = Date.now();

    const fetchData = async (url) => {
        try {
            const res = await fetch(url);
            if (!res.ok) return [];
            return await res.json();
        } catch (e) {
            console.error('Fetch error:', url, e);
            return [];
        }
    };

    // Chargement parallèle de toutes les données
    const [tiresData, rimsData, accessoriesData, categoriesData] = await Promise.all([
        fetchData(`${apiBase}?type=tires&t=${t}`),
        fetchData(`${apiBase}?type=rims&t=${t}`),
        fetchData(`${apiBase}?type=accessories&t=${t}`),
        fetchData(`${apiBase}?type=categories&t=${t}`)
    ]);

    const state = {
        allTires: (tiresData || []).filter(item => parseInt(item.is_hidden || 0) === 0),
        allRims: (rimsData || []).filter(item => parseInt(item.is_hidden || 0) === 0),
        allAccessories: (accessoriesData || []).filter(item => parseInt(item.is_hidden || 0) === 0),
        categories: categoriesData || [],
        activeType: 'all', // 'all', 'tires', 'rims', 'accessories'
        searchTerm: '',
        sortBy: 'default',
        filters: {
            tireCategory: 'all',
            tireCondition: 'all',
            tireWidth: 'all',
            tireRatio: 'all',
            tireRim: 'all',
            rimDiameter: 'all',
            rimBolt: 'all',
            rimType: 'all',
            accCategory: 'all'
        }
    };

    // Populate Sub-Filter Select Options
    populateFilterOptions(state);

    // Parse URL parameter ?type=...
    const urlParams = new URLSearchParams(window.location.search);
    const initialType = urlParams.get('type');
    if (initialType && ['tires', 'rims', 'accessories'].includes(initialType)) {
        state.activeType = initialType;
        updateActiveTabUI(initialType);
    }

    // Bind Event Listeners
    initEventListeners(state);

    // Initial Render
    applyFiltersAndRender(state);
});

function populateFilterOptions(state) {
    // 1. Tire Categories
    const catSelect = document.getElementById('filter-tire-category');
    if (catSelect && state.categories.length) {
        state.categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.slug;
            opt.textContent = cat.name;
            catSelect.appendChild(opt);
        });
    }

    // 2. Tire Widths, Ratios, Rims
    const widthSelect = document.getElementById('filter-tire-width');
    const ratioSelect = document.getElementById('filter-tire-ratio');
    const rimSelect = document.getElementById('filter-tire-rim');

    const widths = [...new Set(state.allTires.map(t => t.width).filter(Boolean))].sort((a,b) => a-b);
    const ratios = [...new Set(state.allTires.map(t => t.ratio).filter(Boolean))].sort((a,b) => a-b);
    const rims = [...new Set(state.allTires.map(t => t.rim).filter(Boolean))].sort();

    if (widthSelect) widths.forEach(w => { const opt = document.createElement('option'); opt.value = w; opt.textContent = w; widthSelect.appendChild(opt); });
    if (ratioSelect) ratios.forEach(r => { const opt = document.createElement('option'); opt.value = r; opt.textContent = r; ratioSelect.appendChild(opt); });
    if (rimSelect) rims.forEach(r => { const opt = document.createElement('option'); opt.value = r; opt.textContent = r; rimSelect.appendChild(opt); });

    // 3. Rim Diameters & Bolt Patterns
    const rimDiamSelect = document.getElementById('filter-rim-diameter');
    const rimBoltSelect = document.getElementById('filter-rim-bolt');

    const rimDiams = [...new Set(state.allRims.map(r => r.diameter).filter(Boolean))].sort();
    const rimBolts = [...new Set(state.allRims.map(r => r.bolt_pattern).filter(Boolean))].sort();

    if (rimDiamSelect) rimDiams.forEach(d => { const opt = document.createElement('option'); opt.value = d; opt.textContent = d; rimDiamSelect.appendChild(opt); });
    if (rimBoltSelect) rimBolts.forEach(b => { const opt = document.createElement('option'); opt.value = b; opt.textContent = b; rimBoltSelect.appendChild(opt); });

    // 4. Accessory Categories
    const accCatSelect = document.getElementById('filter-acc-category');
    const accCats = [...new Set(state.allAccessories.map(a => a.category).filter(Boolean))].sort();
    if (accCatSelect) accCats.forEach(c => { const opt = document.createElement('option'); opt.value = c; opt.textContent = c; accCatSelect.appendChild(opt); });
}

function updateActiveTabUI(type) {
    document.querySelectorAll('.catalog-type-btn').forEach(btn => {
        if (btn.getAttribute('data-type') === type) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const subTires = document.getElementById('sub-filters-tires');
    const subRims = document.getElementById('sub-filters-rims');
    const subAcc = document.getElementById('sub-filters-accessories');

    if (subTires) subTires.style.display = (type === 'tires' || type === 'all') ? 'grid' : 'none';
    if (subRims) subRims.style.display = (type === 'rims') ? 'grid' : 'none';
    if (subAcc) subAcc.style.display = (type === 'accessories') ? 'grid' : 'none';
}

function initEventListeners(state) {
    // Type Tabs
    document.querySelectorAll('.catalog-type-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-type');
            state.activeType = type;
            updateActiveTabUI(type);
            applyFiltersAndRender(state);
        });
    });

    // Search Input
    const searchInput = document.getElementById('catalog-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            state.searchTerm = e.target.value.trim().toLowerCase();
            applyFiltersAndRender(state);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById('catalog-sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            state.sortBy = e.target.value;
            applyFiltersAndRender(state);
        });
    }

    // Sub-filters Tires
    const bindSelect = (id, key) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', (e) => {
                state.filters[key] = e.target.value;
                applyFiltersAndRender(state);
            });
        }
    };

    bindSelect('filter-tire-category', 'tireCategory');
    bindSelect('filter-tire-condition', 'tireCondition');
    bindSelect('filter-tire-width', 'tireWidth');
    bindSelect('filter-tire-ratio', 'tireRatio');
    bindSelect('filter-tire-rim', 'tireRim');
    bindSelect('filter-rim-diameter', 'rimDiameter');
    bindSelect('filter-rim-bolt', 'rimBolt');
    bindSelect('filter-rim-type', 'rimType');
    bindSelect('filter-acc-category', 'accCategory');

    // Reset Button
    const resetBtn = document.getElementById('reset-filters-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            state.searchTerm = '';
            state.sortBy = 'default';
            state.activeType = 'all';
            Object.keys(state.filters).forEach(k => state.filters[k] = 'all');

            if (searchInput) searchInput.value = '';
            if (sortSelect) sortSelect.value = 'default';
            
            document.querySelectorAll('.sub-filters-container select').forEach(sel => sel.value = 'all');
            updateActiveTabUI('all');
            applyFiltersAndRender(state);
        });
    }
}

function applyFiltersAndRender(state) {
    let items = [];

    // 1. Gather by Active Type
    if (state.activeType === 'all' || state.activeType === 'tires') {
        const filteredTires = state.allTires.filter(tire => {
            if (state.filters.tireCategory !== 'all' && tire.category !== state.filters.tireCategory) return false;
            if (state.filters.tireCondition !== 'all' && tire.condition_type !== state.filters.tireCondition) return false;
            if (state.filters.tireWidth !== 'all' && tire.width !== state.filters.tireWidth) return false;
            if (state.filters.tireRatio !== 'all' && tire.ratio !== state.filters.tireRatio) return false;
            if (state.filters.tireRim !== 'all' && tire.rim !== state.filters.tireRim) return false;
            return true;
        }).map(t => ({ ...t, _itemType: 'tire' }));
        items.push(...filteredTires);
    }

    if (state.activeType === 'all' || state.activeType === 'rims') {
        const filteredRims = state.allRims.filter(rim => {
            if (state.filters.rimDiameter !== 'all' && rim.diameter !== state.filters.rimDiameter) return false;
            if (state.filters.rimBolt !== 'all' && rim.bolt_pattern !== state.filters.rimBolt) return false;
            if (state.filters.rimType !== 'all' && rim.type !== state.filters.rimType) return false;
            return true;
        }).map(r => ({ ...r, _itemType: 'rim' }));
        items.push(...filteredRims);
    }

    if (state.activeType === 'all' || state.activeType === 'accessories') {
        const filteredAcc = state.allAccessories.filter(acc => {
            if (state.filters.accCategory !== 'all' && acc.category !== state.filters.accCategory) return false;
            return true;
        }).map(a => ({ ...a, _itemType: 'accessory' }));
        items.push(...filteredAcc);
    }

    // 2. Filter by Search Term
    if (state.searchTerm) {
        const s = state.searchTerm;
        items = items.filter(item => {
            if (item._itemType === 'tire') {
                const combined = `${item.brand} ${item.model} ${item.width}/${item.ratio} ${item.rim} ${item.category} ${item.description || ''}`.toLowerCase();
                return combined.includes(s);
            } else if (item._itemType === 'rim') {
                const combined = `${item.brand} ${item.model} ${item.diameter} ${item.bolt_pattern} ${item.type} ${item.description || ''}`.toLowerCase();
                return combined.includes(s);
            } else if (item._itemType === 'accessory') {
                const combined = `${item.name} ${item.category} ${item.description || ''}`.toLowerCase();
                return combined.includes(s);
            }
            return false;
        });
    }

    // 3. Sorting
    if (state.sortBy === 'price_asc') {
        items.sort((a, b) => parseInt(a.price || 0) - parseInt(b.price || 0));
    } else if (state.sortBy === 'price_desc') {
        items.sort((a, b) => parseInt(b.price || 0) - parseInt(a.price || 0));
    } else if (state.sortBy === 'brand_asc') {
        items.sort((a, b) => {
            const nameA = (a.brand || a.name || '').toLowerCase();
            const nameB = (b.brand || b.name || '').toLowerCase();
            return nameA.localeCompare(nameB);
        });
    }

    // 4. Update Counter
    const countEl = document.getElementById('results-count-text');
    if (countEl) {
        countEl.innerHTML = `<strong>${items.length}</strong> produit${items.length > 1 ? 's' : ''} trouvé${items.length > 1 ? 's' : ''}`;
    }

    // 5. Render Grid
    renderProductsGrid(items);
}

function renderProductsGrid(items) {
    const grid = document.getElementById('full-catalog-grid');
    if (!grid) return;

    grid.replaceChildren();

    if (items.length === 0) {
        const empty = document.createElement('div');
        empty.style.gridColumn = '1 / -1';
        empty.style.textAlign = 'center';
        empty.style.padding = '60px 20px';
        empty.style.background = 'var(--bg-card)';
        empty.style.borderRadius = 'var(--radius-md)';
        empty.style.border = '1px dashed var(--border-color)';
        
        empty.innerHTML = `
            <div style="font-size: 3rem; color: #64748b; margin-bottom: 15px;"><i class="fa-solid fa-box-open"></i></div>
            <h3 style="color: #ffffff; margin-bottom: 10px;">Aucun produit trouvé</h3>
            <p style="color: #94a3b8; max-width: 500px; margin: 0 auto 20px auto;">
                Aucun article ne correspond exactement à vos critères de recherche. Essayez de réinitialiser les filtres ou contactez-nous pour une commande spéciale !
            </p>
        `;
        grid.appendChild(empty);
        return;
    }

    items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'tire-item';

        const header = document.createElement('div');
        header.className = 'tire-item-header';

        const brandTag = document.createElement('span');
        brandTag.className = 'tire-brand-tag';

        const badge = document.createElement('span');
        badge.className = 'tire-badge';

        let titleText = '';
        let specsText = '';
        let descText = item.description || '';
        let priceText = `${parseInt(item.price || 0).toLocaleString('fr-FR')} FCFA`;
        let devisParam = '';

        if (item._itemType === 'tire') {
            brandTag.textContent = item.brand;
            badge.className += item.condition_type === 'new' ? ' badge-new' : ' badge-occ';
            badge.textContent = item.condition_type === 'new' ? 'Neuf' : 'Occasion';
            titleText = `${item.width}/${item.ratio} ${item.rim} - ${item.model}`;
            specsText = `Catégorie : ${(item.category || '').toUpperCase()}`;
            devisParam = `${item.brand} ${item.width}/${item.ratio} ${item.rim} (${item.model})`;
        } else if (item._itemType === 'rim') {
            brandTag.textContent = item.brand;
            badge.className += ' badge-new';
            badge.textContent = item.type || 'Jante Alu';
            titleText = `${item.model} (${item.diameter})`;
            specsText = `Diamètre : ${item.diameter} | Entraxe : ${item.bolt_pattern}`;
            devisParam = `Jante ${item.brand} ${item.model} ${item.diameter}`;
        } else if (item._itemType === 'accessory') {
            brandTag.textContent = item.category || 'Accessoire';
            badge.className += ' badge-new';
            badge.textContent = 'En stock';
            titleText = item.name;
            specsText = `Accessoire auto garanti`;
            devisParam = `Accessoire : ${item.name}`;
        }

        header.appendChild(brandTag);
        header.appendChild(badge);
        card.appendChild(header);

        // Image
        if (item.image) {
            const imgWrapper = document.createElement('div');
            imgWrapper.style.textAlign = 'center';
            imgWrapper.style.margin = '15px 0';
            const img = document.createElement('img');
            img.src = item.image;
            img.alt = titleText;
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.maxHeight = '140px';
            img.style.objectFit = 'contain';
            img.style.borderRadius = '8px';
            imgWrapper.appendChild(img);
            card.appendChild(imgWrapper);
        }

        // Title
        const title = document.createElement('h4');
        title.textContent = titleText;
        card.appendChild(title);

        // Specs
        const specs = document.createElement('div');
        specs.className = 'tire-specs';
        const specSpan = document.createElement('span');
        specSpan.textContent = specsText;
        specs.appendChild(specSpan);
        card.appendChild(specs);

        // Description
        if (descText) {
            const desc = document.createElement('p');
            desc.style.fontSize = '0.85rem';
            desc.style.color = '#94a3b8';
            desc.style.marginBottom = '15px';
            desc.textContent = descText;
            card.appendChild(desc);
        }

        // Price
        const priceRow = document.createElement('div');
        priceRow.className = 'tire-price';
        priceRow.style.marginBottom = '15px';

        const priceVal = document.createElement('div');
        priceVal.className = 'price-val';
        priceVal.textContent = priceText;
        
        priceRow.appendChild(priceVal);
        card.appendChild(priceRow);

        // Action Buttons
        const actionRow = document.createElement('div');
        actionRow.style.display = 'flex';
        actionRow.style.gap = '10px';

        const detailsBtn = document.createElement('a');
        detailsBtn.className = 'btn btn-secondary';
        detailsBtn.style.padding = '8px 10px';
        detailsBtn.style.fontSize = '0.82rem';
        detailsBtn.style.flex = '1';
        detailsBtn.style.textAlign = 'center';
        detailsBtn.href = `produit.php?type=${encodeURIComponent(item._itemType)}&id=${encodeURIComponent(item.id)}`;
        
        const iconEye = document.createElement('i');
        iconEye.className = 'fa-solid fa-eye';
        iconEye.style.marginRight = '6px';
        detailsBtn.appendChild(iconEye);
        detailsBtn.appendChild(document.createTextNode('Détails'));

        const orderBtn = document.createElement('a');
        orderBtn.className = 'btn btn-primary';
        orderBtn.style.padding = '8px 10px';
        orderBtn.style.fontSize = '0.82rem';
        orderBtn.style.flex = '1';
        orderBtn.style.textAlign = 'center';
        orderBtn.href = `index.php?order=${encodeURIComponent(devisParam)}&price=${encodeURIComponent(item.price)}#devis`;
        
        const iconCalc = document.createElement('i');
        iconCalc.className = 'fa-solid fa-cart-shopping';
        iconCalc.style.marginRight = '6px';
        orderBtn.appendChild(iconCalc);
        orderBtn.appendChild(document.createTextNode('Commander'));

        actionRow.appendChild(detailsBtn);
        actionRow.appendChild(orderBtn);
        card.appendChild(actionRow);

        grid.appendChild(card);
    });
}