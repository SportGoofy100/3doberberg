<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="3D%20Oberberg_Logo_kl.png">
    <title>3D Oberberg – Admin-Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;0,9..144,900;1,9..144,400;1,9..144,700&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8B5CF6;
            --primary-light: rgba(139, 92, 246, 0.12);
            --secondary: #22D3EE;
            --bg: #FBF8F2;
            --bg2: #F3EFE6;
            --surface: #FFFFFF;
            --ink: #1E1B14;
            --ink2: #6B6455;
            --ink3: #AFA89A;
            --border: rgba(30, 27, 20, 0.09);
            --r: 8px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* --- Header & Navigation --- */
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        .admin-logo {
            font-family: 'Fraunces', sans-serif;
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-nav { display: flex; gap: 15px; }
        .admin-nav a {
            padding: 10px 20px;
            background: var(--surface);
            color: var(--ink2);
            text-decoration: none;
            border-radius: var(--r);
            border: 1px solid var(--border);
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* --- Sections (Tabs) --- */
        .admin-section {
            display: none;
            background: var(--surface);
            border-radius: var(--r);
            padding: 30px;
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .admin-section.active { display: block; }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .section-title {
            font-family: 'Fraunces', sans-serif;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--ink);
        }

        /* --- Product Form --- */
        .product-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--ink2); }
        .form-group input, .form-group textarea, .form-group select {
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--r);
            font-size: 0.9rem;
            font-family: inherit;
            background: var(--bg);
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-group textarea { min-height: 100px; resize: vertical; }

        /* --- Drop Zone for Images --- */
        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: var(--r);
            padding: 40px;
            text-align: center;
            cursor: pointer;
            background: var(--bg2);
            transition: all 0.2s;
            grid-column: span 2;
        }
        .drop-zone:hover, .drop-zone.drag-over {
            border-color: var(--primary);
            background: var(--primary-light);
        }
        .drop-zone img {
            max-width: 100%;
            max-height: 200px;
            margin-top: 15px;
            border-radius: var(--r);
            display: none;
        }

        /* --- Buttons --- */
        .btn {
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--r);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover { background: #7C3AED; transform: translateY(-1px); }
        .btn-secondary {
            background: var(--bg);
            color: var(--ink2);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: var(--bg2); border-color: var(--primary); color: var(--primary); }
        .btn-danger { background: #EF4444; }
        .btn-danger:hover { background: #DC2626; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }

        /* --- Product Grid --- */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card {
            background: var(--bg);
            border-radius: var(--r);
            padding: 20px;
            border: 1px solid var(--border);
            position: relative;
        }
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: var(--r);
            margin-bottom: 15px;
            background: var(--bg2);
        }
        .product-card h3 {
            font-family: 'Fraunces', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: var(--ink);
        }
        .product-card .price {
            font-family: 'Fraunces', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .product-card .details { font-size: 0.85rem; color: var(--ink2); margin-bottom: 15px; line-height: 1.5; }
        .product-card .stock {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .product-card .stock.in-stock { background: rgba(34, 211, 238, 0.12); color: var(--secondary); }
        .product-card .stock.out-of-stock { background: rgba(239, 68, 68, 0.12); color: #EF4444; }
        .product-card .actions { display: flex; gap: 10px; margin-top: 15px; }

        /* --- Orders Table --- */
        .orders-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .orders-table th, .orders-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border); }
        .orders-table th {
            background: var(--bg2);
            font-weight: 600;
            color: var(--ink2);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .orders-table tr:hover { background: var(--bg2); }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-shipped { background: #DBEAFE; color: #1E40AF; }
        .status-completed { background: #D1FAE5; color: #065F46; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }
        .order-items { font-size: 0.85rem; color: var(--ink2); max-width: 300px; }

        /* --- Stats Grid --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card {
            background: var(--bg);
            border-radius: var(--r);
            padding: 20px;
            border: 1px solid var(--border);
        }
        .stat-card .stat-value {
            font-family: 'Fraunces', sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--primary);
            margin: 10px 0;
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--ink2);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* --- Empty State & Loading --- */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--ink3); }
        .empty-state h3 { font-family: 'Fraunces', sans-serif; font-size: 1.2rem; margin-bottom: 10px; }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(139, 92, 246, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* --- Modal --- */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: var(--surface);
            border-radius: var(--r);
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--ink2);
            padding: 5px;
        }
        .modal-close:hover { color: var(--ink); }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .admin-header { flex-direction: column; gap: 15px; }
            .product-form { grid-template-columns: 1fr; }
            .drop-zone { grid-column: span 1; }
            .admin-nav { flex-wrap: wrap; justify-content: center; }
            .orders-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header mit Logo und Navigation -->
        <header class="admin-header">
            <div class="admin-logo">
                <img src="3D%20Oberberg_Logo_kl.png" alt="3D Oberberg Logo" style="height: 40px; width: auto;">
                <span>Admin-Panel</span>
            </div>
            <nav class="admin-nav">
                <a href="#products" class="active" data-section="products">Produkte</a>
                <a href="#orders" data-section="orders">Bestellungen</a>
                <a href="#stats" data-section="stats">Statistiken</a>
                <a href="shop.html" target="_blank">→ Shop anzeigen</a>
            </nav>
        </header>

        <!-- ===== SECTION: PRODUKTE ===== -->
        <section id="products" class="admin-section active">
            <div class="section-header">
                <h2 class="section-title">📦 Produkte verwalten</h2>
                <button class="btn" id="addProductBtn"><span>+</span> Neues Produkt hinzufügen</button>
            </div>
            <div class="product-grid" id="productGrid">
                <div class="empty-state">
                    <div class="loading"></div>
                    <p>Produkte werden geladen...</p>
                </div>
            </div>
        </section>

        <!-- ===== SECTION: BESTELLUNGEN ===== -->
        <section id="orders" class="admin-section">
            <div class="section-header">
                <h2 class="section-title">📋 Bestellungen</h2>
            </div>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Bestell-ID</th>
                        <th>Datum</th>
                        <th>Kunde</th>
                        <th>Artikel</th>
                        <th>Gesamt</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody id="ordersList">
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div class="loading"></div>
                            <p>Bestellungen werden geladen...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ===== SECTION: STATISTIKEN ===== -->
        <section id="stats" class="admin-section">
            <div class="section-header">
                <h2 class="section-title">📊 Statistiken</h2>
            </div>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-label">Gesamtumsatz</div>
                    <div class="stat-value" id="totalRevenue">–</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Bestellungen</div>
                    <div class="stat-value" id="totalOrders">–</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Beliebtestes Produkt</div>
                    <div class="stat-value" id="topProduct">–</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Aktive Produkte</div>
                    <div class="stat-value" id="activeProducts">–</div>
                </div>
            </div>
        </section>
    </div>

    <!-- ===== MODAL: PRODUKT HINZUFÜGEN/BEARBEITEN ===== -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Produkt hinzufügen</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <form id="productForm" class="product-form">
                <input type="hidden" id="productId">
                <div class="form-group">
                    <label for="productName">Produktname *</label>
                    <input type="text" id="productName" required>
                </div>
                <div class="form-group">
                    <label for="productPrice">Preis (€) *</label>
                    <input type="number" step="0.01" id="productPrice" min="0" required>
                </div>
                <div class="form-group">
                    <label for="productCategory">Kategorie</label>
                    <select id="productCategory">
                        <option value="">– Keine Kategorie –</option>
                        <option value="Schlüsselanhänger">Schlüsselanhänger</option>
                        <option value="Funktionsteile">Funktionsteile</option>
                        <option value="Prototypen">Prototypen</option>
                        <option value="Werbeartikel">Werbeartikel</option>
                        <option value="Dekoration">Dekoration</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productMaterial">Material</label>
                    <input type="text" id="productMaterial" placeholder="z. B. PLA, PETG">
                </div>
                <div class="form-group">
                    <label for="productStock">Bestand</label>
                    <input type="number" id="productStock" min="0" value="999">
                </div>
                <div class="form-group drop-zone" id="imageDropZone">
                    <label>Bild hochladen (Drag & Drop)</label>
                    <p style="margin-top: 10px; color: var(--ink3);">Ziehe ein Bild hierher oder klicke zum Auswählen</p>
                    <input type="file" id="productImage" accept="image/*" style="display: none;">
                    <img id="imagePreview" alt="Vorschau">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label for="productDescription">Beschreibung</label>
                    <textarea id="productDescription"></textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label><input type="checkbox" id="productActive" checked> Produkt aktiv</label>
                </div>
                <div class="form-group" style="grid-column: span 2; margin-top: 20px;">
                    <button type="submit" class="btn" id="saveProductBtn">
                        <span class="btn-text">Speichern</span>
                        <span class="loading" style="display: none;" id="saveLoading"></span>
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelProductBtn">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL: BESTELLUNG ANZEIGEN ===== -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Bestellung #<span id="orderIdDisplay"></span></h3>
                <button class="modal-close" id="orderModalClose">&times;</button>
            </div>
            <div id="orderDetails">
                <div class="loading"></div>
                <p>Bestelldetails werden geladen...</p>
            </div>
        </div>
    </div>

    <!-- ===== JavaScript (Inline) ===== -->
    <script>
        let products = [];
        let orders = [];
        let currentProductIndex = null;
        let currentImageFile = null;

        // DOM-Elemente
        const productGrid = document.getElementById('productGrid');
        const ordersList = document.getElementById('ordersList');
        const productModal = document.getElementById('productModal');
        const orderModal = document.getElementById('orderModal');
        const addProductBtn = document.getElementById('addProductBtn');
        const modalClose = document.getElementById('modalClose');
        const orderModalClose = document.getElementById('orderModalClose');
        const productForm = document.getElementById('productForm');
        const imageDropZone = document.getElementById('imageDropZone');
        const productImage = document.getElementById('productImage');
        const imagePreview = document.getElementById('imagePreview');
        const saveProductBtn = document.getElementById('saveProductBtn');
        const saveLoading = document.getElementById('saveLoading');
        const btnText = document.querySelector('#saveProductBtn .btn-text');

        // Navigation zwischen Sections
        document.querySelectorAll('.admin-nav a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.admin-nav a').forEach(a => a.classList.remove('active'));
                document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
                link.classList.add('active');
                document.getElementById(link.dataset.section).classList.add('active');
            });
        });

        // ===== PRODUKTE =====
        async function loadProducts() {
            try {
                const response = await fetch('admin/get_products.php');
                products = await response.json();
                renderProducts();
            } catch (error) {
                productGrid.innerHTML = `<div class="empty-state"><h3>Fehler beim Laden</h3><p>${error.message}</p><button class="btn" onclick="loadProducts()">Erneut versuchen</button></div>`;
            }
        }

        function renderProducts() {
            if (products.length === 0) {
                productGrid.innerHTML = `<div class="empty-state"><h3>Keine Produkte</h3><p>Füge dein erstes Produkt hinzu.</p><button class="btn" onclick="openProductModal()">+ Neues Produkt</button></div>`;
                return;
            }
            productGrid.innerHTML = products.map((product, index) => `
                <div class="product-card">
                    <img src="${product.image ? product.image : '3D%20Oberberg_Logo_kl.png'}" alt="${product.name}" onerror="this.src='3D%20Oberberg_Logo_kl.png'">
                    <h3>${product.name}</h3>
                    <div class="price">${formatCurrency(product.price)}</div>
                    <div class="details">
                        ${product.category ? `<span style="color: var(--ink3); font-size: 0.85rem;">${product.category}</span><br>` : ''}
                        ${product.material ? `Material: ${product.material}<br>` : ''}
                        ${product.description ? product.description.substring(0, 80) + '...' : ''}
                    </div>
                    <div class="stock ${product.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                        ${product.stock > 0 ? `✅ ${product.stock} auf Lager` : '❌ Nicht auf Lager'}
                    </div>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm" onclick="editProduct(${index})">Bearbeiten</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(${index})">Löschen</button>
                    </div>
                </div>
            `).join('');
        }

        function openProductModal(product = null) {
            currentProductIndex = product ? products.findIndex(p => p.id === product.id) : null;
            document.getElementById('modalTitle').textContent = product ? 'Produkt bearbeiten' : 'Neues Produkt hinzufügen';
            document.getElementById('productId').value = product ? product.id : '';
            document.getElementById('productName').value = product ? product.name : '';
            document.getElementById('productPrice').value = product ? product.price : '';
            document.getElementById('productCategory').value = product ? product.category : '';
            document.getElementById('productMaterial').value = product ? product.material : '';
            document.getElementById('productStock').value = product ? product.stock : 999;
            document.getElementById('productDescription').value = product ? product.description : '';
            document.getElementById('productActive').checked = product ? product.active !== false : true;
            if (product && product.image) {
                imagePreview.src = product.image;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
            }
            currentImageFile = null;
            productModal.classList.add('active');
        }

        // Modal-Event-Listener
        modalClose.addEventListener('click', () => productModal.classList.remove('active'));
        orderModalClose.addEventListener('click', () => orderModal.classList.remove('active'));
        document.getElementById('cancelProductBtn').addEventListener('click', () => productModal.classList.remove('active'));
        addProductBtn.addEventListener('click', () => openProductModal());

        // Drag & Drop für Bilder
        imageDropZone.addEventListener('click', () => productImage.click());
        productImage.addEventListener('change', handleImageUpload);
        ['dragover', 'dragenter'].forEach(event => imageDropZone.addEventListener(event, e => { e.preventDefault(); imageDropZone.classList.add('drag-over'); }));
        ['dragleave', 'drop'].forEach(event => imageDropZone.addEventListener(event, () => imageDropZone.classList.remove('drag-over')));
        imageDropZone.addEventListener('drop', e => { e.preventDefault(); if (e.dataTransfer.files.length) { productImage.files = e.dataTransfer.files; handleImageUpload({ target: productImage }); } });

        async function handleImageUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            currentImageFile = file;
            const reader = new FileReader();
            reader.onload = event => { imagePreview.src = event.target.result; imagePreview.style.display = 'block'; };
            reader.readAsDataURL(file);
        }

        // Produkt speichern
        productForm.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('id', document.getElementById('productId').value);
            formData.append('name', document.getElementById('productName').value);
            formData.append('price', document.getElementById('productPrice').value);
            formData.append('category', document.getElementById('productCategory').value);
            formData.append('material', document.getElementById('productMaterial').value);
            formData.append('stock', document.getElementById('productStock').value);
            formData.append('description', document.getElementById('productDescription').value);
            formData.append('active', document.getElementById('productActive').checked);
            if (currentImageFile) formData.append('image', currentImageFile);

            saveProductBtn.disabled = true;
            btnText.style.display = 'none';
            saveLoading.style.display = 'inline-block';

            try {
                const response = await fetch('admin/save_product.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    await loadProducts();
                    productModal.classList.remove('active');
                    productForm.reset();
                    imagePreview.style.display = 'none';
                    currentImageFile = null;
                } else alert('Fehler: ' + result.error);
            } catch (error) { alert('Fehler: ' + error.message); }
            finally {
                saveProductBtn.disabled = false;
                btnText.style.display = 'inline';
                saveLoading.style.display = 'none';
            }
        });

        function editProduct(index) { openProductModal(products[index]); }
        async function deleteProduct(index) {
            if (!confirm(`"${products[index].name}" löschen?`)) return;
            try {
                const response = await fetch('admin/delete_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: products[index].id })
                });
                const result = await response.json();
                if (result.success) await loadProducts();
                else alert('Fehler: ' + result.error);
            } catch (error) { alert('Fehler: ' + error.message); }
        }

        // ===== BESTELLUNGEN =====
        async function loadOrders() {
            try {
                const response = await fetch('admin/get_orders.php');
                orders = await response.json();
                renderOrders();
                updateStats();
            } catch (error) {
                ordersList.innerHTML = `<tr><td colspan="7" class="empty-state"><h3>Fehler beim Laden</h3><p>${error.message}</p><button class="btn" onclick="loadOrders()">Erneut versuchen</button></td></tr>`;
            }
        }

        function renderOrders() {
            if (orders.length === 0) {
                ordersList.innerHTML = `<tr><td colspan="7" class="empty-state"><h3>Keine Bestellungen</h3><p>Es wurden noch keine Bestellungen getätigt.</p></td></tr>`;
                return;
            }
            ordersList.innerHTML = orders.map(order => {
                const itemsText = order.items.map(item => `${item.name} (x${item.quantity})`).join(', ');
                return `
                    <tr>
                        <td><strong>#${order.id.substring(0, 8)}...</strong></td>
                        <td>${new Date(order.date).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                        <td>${order.customer.name}<br><small>${order.customer.email}</small></td>
                        <td class="order-items" title="${itemsText}">${itemsText}</td>
                        <td><strong>${formatCurrency(order.total)}</strong></td>
                        <td>
                            <select class="status-badge status-${order.status.toLowerCase()}" onchange="updateOrderStatus('${order.id}', this.value)" style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; border: none;">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>⏳ Ausstehend</option>
                                <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>🚚 Versandt</option>
                                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>✅ Abgeschlossen</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>❌ Storniert</option>
                            </select>
                        </td>
                        <td><button class="btn btn-secondary btn-sm" onclick="viewOrder('${order.id}')">Anzeigen</button></td>
                    </tr>
                `;
            }).join('');
        }

        async function updateOrderStatus(orderId, status) {
            try {
                const response = await fetch('admin/update_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orderId, status })
                });
                const result = await response.json();
                if (result.success) await loadOrders();
            } catch (error) { console.error('Fehler:', error); }
        }

        async function viewOrder(orderId) {
            try {
                const response = await fetch(`admin/get_order.php?id=${orderId}`);
                const order = await response.json();
                document.getElementById('orderIdDisplay').textContent = order.id.substring(0, 8) + '...';
                const statusTexts = { pending: '⏳ Ausstehend', shipped: '🚚 Versandt', completed: '✅ Abgeschlossen', cancelled: '❌ Storniert' };
                let detailsHTML = `
                    <div style="margin-bottom: 20px;">
                        <h4>Kundendaten</h4>
                        <p><strong>Name:</strong> ${order.customer.name}</p>
                        <p><strong>E-Mail:</strong> ${order.customer.email}</p>
                        <p><strong>Adresse:</strong> ${order.customer.address || 'Nicht angegeben'}</p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <h4>Bestelldetails</h4>
                        <p><strong>Datum:</strong> ${new Date(order.date).toLocaleString('de-DE')}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${order.status.toLowerCase()}">${statusTexts[order.status] || order.status}</span></p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <h4>Artikel (${order.items.length})</h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <th style="text-align: left; padding: 8px;">Produkt</th>
                                    <th style="text-align: right; padding: 8px;">Preis</th>
                                    <th style="text-align: center; padding: 8px;">Menge</th>
                                    <th style="text-align: right; padding: 8px;">Gesamt</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items.map(item => `
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 8px;">${item.name}</td>
                                        <td style="text-align: right; padding: 8px;">${formatCurrency(item.price)}</td>
                                        <td style="text-align: center; padding: 8px;">${item.quantity}</td>
                                        <td style="text-align: right; padding: 8px;">${formatCurrency(item.price * item.quantity)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid var(--border);">
                                    <td colspan="3" style="padding: 8px; text-align: right;"><strong>Gesamt:</strong></td>
                                    <td style="text-align: right; padding: 8px;"><strong>${formatCurrency(order.total)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                if (order.shipping) detailsHTML += `
                    <div style="margin-bottom: 20px;">
                        <h4>Versand</h4>
                        <p><strong>Methode:</strong> ${order.shipping.method || 'Standard'}</p>
                        <p><strong>Kosten:</strong> ${formatCurrency(order.shipping.cost || 0)}</p>
                    </div>
                `;
                document.getElementById('orderDetails').innerHTML = detailsHTML;
                orderModal.classList.add('active');
            } catch (error) { alert('Fehler: ' + error.message); }
        }

        // ===== STATISTIKEN =====
        function updateStats() {
            const totalRevenue = orders.reduce((sum, order) => sum + order.total, 0);
            document.getElementById('totalRevenue').textContent = formatCurrency(totalRevenue);
            document.getElementById('totalOrders').textContent = orders.length;

            const productCounts = {};
            orders.forEach(order => order.items.forEach(item => productCounts[item.name] = (productCounts[item.name] || 0) + item.quantity));
            const topProduct = Object.entries(productCounts).sort((a, b) => b[1] - a[1])[0];
            document.getElementById('topProduct').textContent = topProduct ? `${topProduct[0]} (${topProduct[1]}x)` : 'Keine Daten';

            const activeCount = products.filter(p => p.active !== false).length;
            document.getElementById('activeProducts').textContent = activeCount;
        }

        // Hilfsfunktion: Währung formatieren
        function formatCurrency(amount) {
            return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount);
        }

        // Initial laden
        loadProducts();
        loadOrders();

        // Modal schließen beim Klicken außerhalb
        productModal.addEventListener('click', e => { if (e.target === productModal) productModal.classList.remove('active'); });
        orderModal.addEventListener('click', e => { if (e.target === orderModal) orderModal.classList.remove('active'); });
    </script>
</body>
</html>
