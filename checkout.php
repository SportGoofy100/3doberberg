<?php
header('Content-Type: application/json');

// config.php wird NICHT im Repo geführt (siehe .gitignore) -- einmalig manuell per FTP hochladen.
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Shop ist noch nicht konfiguriert (config.php fehlt).']);
    exit;
}
require_once $configPath;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Warenkorb ist leer.']);
    exit;
}

// Produkte serverseitig laden -- Preise kommen NIE vom Client, sondern immer aus products.json,
// damit niemand über die Browser-Konsole Preise manipulieren kann.
$products = json_decode(file_get_contents(__DIR__ . '/products.json'), true);
$productsById = [];
foreach ($products as $p) {
    $productsById[$p['id']] = $p;
}

$lineItems = [];
foreach ($items as $item) {
    $id = $item['id'] ?? null;
    $qty = max(1, (int)($item['qty'] ?? 1));

    if (!$id || !isset($productsById[$id]) || $productsById[$id]['active'] === false) {
        continue; // unbekannte oder deaktivierte Produkt-ID ignorieren
    }

    $product = $productsById[$id];
    $lineItems[] = [
        'price_data' => [
            'currency' => 'eur',
            'unit_amount' => (int) round($product['price'] * 100),
            'product_data' => [
                'name' => $product['name'],
            ],
        ],
        'quantity' => $qty,
    ];
}

if (empty($lineItems)) {
    http_response_code(400);
    echo json_encode(['error' => 'Keine gültigen Artikel im Warenkorb.']);
    exit;
}

// Pauschale Versandkosten -- passe Betrag/Länder bei Bedarf an.
$lineItems[] = [
    'price_data' => [
        'currency' => 'eur',
        'unit_amount' => 490, // 4,90 € Versand
        'product_data' => [
            'name' => 'Versand (DHL Päckchen)',
        ],
    ],
    'quantity' => 1,
];

$fields = [
    'mode' => 'payment',
    'success_url' => SHOP_BASE_URL . '/shop-erfolg.html?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => SHOP_BASE_URL . '/shop.html',
    'shipping_address_collection' => [
        'allowed_countries' => ['DE', 'AT', 'CH'],
    ],
    'line_items' => $lineItems,
];

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
    ],
    CURLOPT_POSTFIELDS => http_build_query($fields),
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode !== 200 || empty($result['url'])) {
    http_response_code(500);
    echo json_encode(['error' => $result['error']['message'] ?? 'Stripe-Fehler beim Erstellen der Bestellung.']);
    exit;
}

echo json_encode(['url' => $result['url']]);
