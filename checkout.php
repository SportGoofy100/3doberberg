<?php
require_once __DIR__ . '/config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customerName = $_POST['customer_name'] ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';
        $customerAddress = $_POST['customer_address'] ?? '';
        $customerPhone = $_POST['customer_phone'] ?? '';
        $cartItems = json_decode($_POST['cart_items'] ?? '[]', true);
        $totalAmount = (float)($_POST['total_amount'] ?? 0);
        
        if (empty($customerName) || empty($customerEmail) || empty($cartItems) || $totalAmount <= 0) {
            throw new Exception('Ungueltige Bestelldaten');
        }
        
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, address, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$customerName, $customerEmail, $customerAddress, $customerPhone]);
        $customerId = $pdo->lastInsertId();
        
        $orderId = 'ORD-' . strtoupper(uniqid());
        $stmt = $pdo->prepare("INSERT INTO orders (id, customer_id, total_amount, status, payment_method, created_at) VALUES (?, ?, ?, 'pending', 'stripe', NOW())");
        $stmt->execute([$orderId, $customerId, $totalAmount * 100]);
        
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price'] * 100]);
        }
        
        echo json_encode(['success' => true, 'orderId' => $orderId]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasse - 3D Oberberg</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        :root { --primary: #8B5CF6; --bg: #FBF8F2; --surface: #FFFFFF; --ink: #1E1B14; --ink2: #6B6455; --border: rgba(30,27,20,0.09); --r: 8px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--ink); line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .checkout-header { padding: 20px 0; margin-bottom: 30px; border-bottom: 1px solid var(--border); }
        .checkout-logo { font-family: 'Fraunces', sans-serif; font-weight: 900; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .checkout-logo img { height: 40px; width: auto; }
        .checkout-steps { display: flex; justify-content: center; margin-bottom: 40px; gap: 0; }
        .step { flex: 1; text-align: center; padding: 15px; position: relative; }
        .step:not(:last-child)::after { content: ''; position: absolute; top: 50%; right: 0; width: 100%; height: 2px; background: var(--border); z-index: 1; }
        .step-number { width: 30px; height: 30px; background: var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 600; color: var(--ink2); position: relative; z-index: 2; }
        .step.active .step-number { background: var(--primary); color: white; }
        .step.completed .step-number { background: #10B981; color: white; }
        .step-label { font-size: 0.85rem; color: var(--ink2); font-weight: 500; }
        .step.active .step-label { color: var(--primary); }
        .step.completed .step-label { color: #10B981; }
        .checkout-content { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; }
        @media (max-width: 768px) { .checkout-content { grid-template-columns: 1fr; gap: 30px; } }
        .checkout-section { background: var(--surface); border-radius: var(--r); padding: 30px; border: 1px solid var(--border); }
        .checkout-section h2 { font-family: 'Fraunces', sans-serif; font-size: 1.3rem; font-weight: 700; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: var(--ink2); margin-bottom: 8px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--r); font-size: 1rem; font-family: inherit; background: var(--bg); }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
        .cart-summary { border-top: 1px solid var(--border); padding-top: 20px; margin-top: 20px; }
        .cart-item-summary { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
        .cart-item-summary:last-child { border-bottom: none; }
        .cart-item-name { font-weight: 500; }
        .cart-item-qty { color: var(--ink3); font-size: 0.9rem; }
        .cart-item-price { font-weight: 600; }
        .total-row { display: flex; justify-content: space-between; padding: 15px 0; font-size: 1.1rem; border-top: 2px solid var(--border); margin-top: 10px; }
        .total-label { font-weight: 600; }
        .total-value { font-family: 'Fraunces', sans-serif; font-weight: 700; color: var(--primary); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; background: var(--primary); color: white; text-decoration: none; border-radius: var(--r); font-weight: 600; font-family: inherit; transition: all 0.3s; border: none; cursor: pointer; width: 100%; }
        .btn:hover { background: #7C3AED; transform: translateY(-2px); }
        .btn-secondary { background: var(--surface); color: var(--primary); border: 2px solid var(--primary); }
        .btn-secondary:hover { background: var(--primary); color: white; }
        .btn:disabled { background: var(--border); cursor: not-allowed; opacity: 0.7; }
        #card-element { padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--r); background: var(--bg); margin-bottom: 15px; }
        #card-errors { color: #EF4444; font-size: 0.85rem; margin-top: 10px; min-height: 20px; }
        .loading { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(139,92,246,0.2); border-radius: 50%; border-top-color: var(--primary); animation: spin 1s ease-in-out infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .empty-cart { text-align: center; padding: 40px; color: var(--ink3); }
        .empty-cart i { font-size: 3rem; margin-bottom: 20px; color: var(--border); }
    </style>
</head>
<body>
    <div class="container">
        <header class="checkout-header"><a href="shop.html" class="checkout-logo"><img src="3D%20Oberberg_Logo_kl.png" alt="3D Oberberg"></a></header>
        <div class="checkout-steps">
            <div class="step active" id="step1"><div class="step-number">1</div><div class="step-label">Warenkorb</div></div>
            <div class="step" id="step2"><div class="step-number">2</div><div class="step-label">Kundendaten</div></div>
            <div class="step" id="step3"><div class="step-number">3</div><div class="step-label">Zahlung</div></div>
            <div class="step" id="step4"><div class="step-number">4</div><div class="step-label">Bestaetigung</div></div>
        </div>
        <form id="checkoutForm">
            <div class="checkout-content">
                <div class="checkout-section">
                    <h2>Warenkorb</h2>
                    <div id="cartSummary"><div class="empty-cart"><i class="fas fa-shopping-cart"></i><p>Ihr Warenkorb ist leer</p></div></div>
                    <div class="total-row"><span class="total-label">Gesamt:</span><span class="total-value" id="totalAmount">0,00 €</span></div>
                </div>
                <div class="checkout-section">
                    <h2>Kundendaten</h2>
                    <div class="form-group"><label for="customer_name">Voller Name *</label><input type="text" id="customer_name" name="customer_name" required></div>
                    <div class="form-group"><label for="customer_email">E-Mail-Adresse *</label><input type="email" id="customer_email" name="customer_email" required></div>
                    <div class="form-group"><label for="customer_phone">Telefonnummer</label><input type="tel" id="customer_phone" name="customer_phone"></div>
                    <div class="form-group"><label for="customer_address">Adresse *</label><textarea id="customer_address" name="customer_address" required></textarea></div>
                    <div class="form-group"><label for="customer_notes">Hinweise (optional)</label><textarea id="customer_notes" name="customer_notes" placeholder="z. B. besondere Wuensche..."></textarea></div>
                </div>
            </div>
            <div class="checkout-section" id="paymentSection" style="display: none;">
                <h2>Zahlungsmethode</h2>
                <div id="cardPayment" style="display: block;">
                    <div class="form-group"><label>Kartendaten</label><div id="card-element"></div><div id="card-errors"></div></div>
                </div>
            </div>
            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <button type="button" class="btn btn-secondary" id="backBtn" onclick="goBack()" style="display: none;">Zurueck</button>
                <button type="button" class="btn" id="continueBtn" onclick="goToStep(2)"><span id="continueText">Weiter zu Kundendaten</span><span id="continueLoading" style="display: none;"></span></button>
                <button type="submit" class="btn" id="payBtn" style="display: none;"><span id="payText">Zahlung abschliessen</span><span id="payLoading" style="display: none;"></span></button>
            </div>
        </form>
    </div>
    <script>
        let currentStep = 1; let stripe; let cardElement;
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        stripe = Stripe('<?php echo $stripePublishableKey; ?>');
        const steps = [document.getElementById('step1'), document.getElementById('step2'), document.getElementById('step3'), document.getElementById('step4')];
        const continueBtn = document.getElementById('continueBtn');
        const continueText = document.getElementById('continueText');
        const continueLoading = document.getElementById('continueLoading');
        const payBtn = document.getElementById('payBtn');
        const payText = document.getElementById('payText');
        const payLoading = document.getElementById('payLoading');
        const backBtn = document.getElementById('backBtn');
        const paymentSection = document.getElementById('paymentSection');
        const cartSummary = document.getElementById('cartSummary');
        const totalAmount = document.getElementById('totalAmount');
        function goToStep(step) {
            steps.forEach((s, i) => { s.classList.remove('active', 'completed'); if (i < step) s.classList.add('completed'); if (i === step - 1) s.classList.add('active'); });
            currentStep = step;
            if (step === 1) { backBtn.style.display = 'none'; continueBtn.style.display = 'block'; payBtn.style.display = 'none'; continueText.textContent = 'Weiter zu Kundendaten'; }
            else if (step === 2) { backBtn.style.display = 'block'; continueBtn.style.display = 'block'; payBtn.style.display = 'none'; continueText.textContent = 'Weiter zur Zahlung'; }
            else if (step === 3) { backBtn.style.display = 'block'; continueBtn.style.display = 'none'; payBtn.style.display = 'block'; paymentSection.style.display = 'block';
                const elements = stripe.elements();
                const style = { base: { color: '#1E1B14', fontFamily: 'Plus Jakarta Sans, sans-serif', fontSize: '16px', '::placeholder': { color: '#AFA89A' } }, invalid: { color: '#EF4444' } };
                cardElement = elements.create('card', { style: style });
                cardElement.mount('#card-element');
                cardElement.on('change', function(event) { const displayError = document.getElementById('card-errors'); if (event.error) displayError.textContent = event.error.message; else displayError.textContent = ''; });
            }
        }
        function goBack() { if (currentStep > 1) goToStep(currentStep - 1); }
        function updateCartSummary() {
            if (cart.length === 0) { cartSummary.innerHTML = '<div class="empty-cart"><i class="fas fa-shopping-cart"></i><p>Ihr Warenkorb ist leer</p><a href="shop.html" class="btn" style="margin-top:20px;">Zurueck zum Shop</a></div>'; return false; }
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            totalAmount.textContent = formatCurrency(total);
            cartSummary.innerHTML = cart.map(item => `<div class="cart-item-summary"><div><div class="cart-item-name">${item.name}</div><div class="cart-item-qty">Menge: ${item.quantity}</div></div><div class="cart-item-price">${formatCurrency(item.price * item.quantity)}</div></div>`).join('') + `<div class="total-row" style="margin-top:20px;"><span class="total-label">Zwischensumme:</span><span class="total-value">${formatCurrency(total)}</span></div><div class="total-row"><span class="total-label">Versand:</span><span class="total-value">0,00 €</span></div>`;
            return true;
        }
        continueBtn.addEventListener('click', () => {
            if (currentStep === 1) { if (!updateCartSummary()) { window.location.href = 'shop.html'; return; } goToStep(2); }
            else if (currentStep === 2) {
                const name = document.getElementById('customer_name').value; const email = document.getElementById('customer_email').value; const address = document.getElementById('customer_address').value;
                if (!name || !email || !address) { alert('Bitte fuellen Sie alle Pflichtfelder aus.'); return; } goToStep(3);
            }
        });
        document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
            e.preventDefault(); payBtn.disabled = true; payText.style.display = 'none'; payLoading.style.display = 'inline-block'; payLoading.innerHTML = '<div class="loading"></div>';
            try {
                const response = await fetch('checkout.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ customer_name: document.getElementById('customer_name').value, customer_email: document.getElementById('customer_email').value, customer_address: document.getElementById('customer_address').value, customer_phone: document.getElementById('customer_phone').value, cart_items: JSON.stringify(cart), total_amount: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) }) });
                const result = await response.json();
                if (!result.success) throw new Error(result.error || 'Fehler beim Erstellen der Bestellung');
                localStorage.removeItem('cart');
                window.location.href = `shop-erfolg.html?order_id=${result.orderId}&total=${cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)}&items=${cart.reduce((sum, item) => sum + item.quantity, 0)}`;
            } catch (err) { alert('Zahlung fehlgeschlagen: ' + err.message); payBtn.disabled = false; payText.style.display = 'inline'; payLoading.style.display = 'none'; }
        });
        function formatCurrency(amount) { return amount.toFixed(2).replace('.', ',') + ' €'; }
        updateCartSummary(); goToStep(1);
    </script>
</body>
</html>