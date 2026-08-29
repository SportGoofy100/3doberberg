<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        /* Modernes, responsives Design mit CSS-Variablen */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        /* containers, header, nav, sections, cards, tables, modals... */
    </style>
</head>
<body>
    <div class="admin-container">
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

        <!-- Produkte-Sektion -->
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

        <!-- Bestellungen-Sektion -->
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
                    <tr><td colspan="7" class="empty-state"><div class="loading"></div><p>Bestellungen werden geladen...</p></td></tr>
                </tbody>
            </table>
        </section>

        <!-- Statistiken-Sektion -->
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

    <!-- Modal für Produktbearbeitung -->
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
                <!-- Weitere Felder: Material, Bestand, Beschreibung, Bild-Upload -->
                <div class="form-group drop-zone" id="imageDropZone">
                    <label>Bild hochladen (Drag & Drop)</label>
                    <p style="margin-top: 10px; color: var(--ink3);">Ziehe ein Bild hierher oder klicke zum Auswählen</p>
                    <input type="file" id="productImage" accept="image/*" style="display: none;">
                    <img id="imagePreview" alt="Vorschau">
                </div>
                <!-- Buttons: Speichern, Abbrechen -->
            </form>
        </div>
    </div>

    <!-- Modal für Bestelldetails -->
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

    <!-- JavaScript für Dynamik -->
    <script>
        // Funktionen für:
        // - Produktladen, Anzeigen, Bearbeiten, Löschen
        // - Bestellungen laden, Status aktualisieren, Details anzeigen
        // - Statistiken berechnen
        // - Drag & Drop für Bilder
        // - Modal-Steuerung
        // - Währungsformatierung mit Intl.NumberFormat
    </script>
</body>
</html>
