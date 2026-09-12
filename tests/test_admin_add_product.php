<?php
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true), 'raw' => $response];
}

$baseUrl = 'http://localhost/ecomerce/backend/api';

echo "=== 1. Admin Login ===\n";
$adminLogin = makeRequest("{$baseUrl}/admin/login", 'POST', [
    'email' => 'admin@flipkart.local',
    'password' => 'Admin@12345'
]);

echo "Login HTTP Code: {$adminLogin['code']}\n";
$token = $adminLogin['body']['data']['tokens']['access_token'] ?? null;
if (!$token) {
    die("❌ Admin login failed: " . print_r($adminLogin['raw'], true) . "\n");
}
echo "✅ Admin Login Token obtained.\n";

echo "\n=== 2. Creating New Product via POST /api/admin/products ===\n";
$newProduct = [
    'title'       => 'Apple iPhone 16 Pro Max 256GB Desert Titanium',
    'category_id' => 1,
    'brand_name'  => 'Apple',
    'base_price'  => 144999,
    'base_mrp'    => 159999,
    'stock'       => 25,
    'image_url'   => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800',
    'description' => 'Super Retina XDR OLED display, A18 Pro Bionic chip, 48MP fusion camera.'
];

$createRes = makeRequest("{$baseUrl}/admin/products", 'POST', $newProduct, $token);
echo "Create HTTP Code: {$createRes['code']}\n";
echo "Response:\n" . print_r($createRes['body'], true) . "\n";

if ($createRes['code'] === 201 && !empty($createRes['body']['success'])) {
    $newId = $createRes['body']['data']['id'];
    echo "🎉 SUCCESS: Product created in MySQL database with ID #{$newId}!\n";

    echo "\n=== 3. Verifying Product in Public Store Catalog ===\n";
    $verifyRes = makeRequest("{$baseUrl}/products/{$newId}");
    echo "Verify Status: {$verifyRes['code']}\n";
    if ($verifyRes['code'] === 200) {
        echo "✅ Verified: Product is live in customer catalog! Title: " . $verifyRes['body']['data']['title'] . "\n";
    } else {
        echo "⚠️ Note on public endpoint: " . print_r($verifyRes['raw'], true) . "\n";
    }
} else {
    echo "❌ FAILED to create product\n";
    exit(1);
}
