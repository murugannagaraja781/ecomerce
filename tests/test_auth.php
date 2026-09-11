<?php
/**
 * Automated Auth API Test Suite
 */

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

$baseUrl = 'http://localhost/ecommerce_api/api';

echo "=== 1. Testing Customer Login ===\n";
$login = makeRequest("{$baseUrl}/auth/login", 'POST', [
    'email' => 'customer@gmail.com',
    'password' => 'Customer@12345'
]);
echo "Status: {$login['code']}\n";
assert($login['code'] === 200, "Customer login must return 200");
$customerToken = $login['body']['data']['tokens']['access_token'];
$customerRefreshToken = $login['body']['data']['tokens']['refresh_token'];
echo "Customer Logged In: " . $login['body']['data']['user']['name'] . " (" . $login['body']['data']['user']['role'] . ")\n";

echo "\n=== 2. Testing Customer Profile (Protected) ===\n";
$profile = makeRequest("{$baseUrl}/auth/profile", 'GET', null, $customerToken);
echo "Status: {$profile['code']}\n";
assert($profile['code'] === 200, "Profile must return 200");
echo "Email: " . $profile['body']['data']['user']['email'] . ", Orders: " . $profile['body']['data']['stats']['orders_count'] . "\n";

echo "\n=== 3. Testing Token Refresh ===\n";
$refresh = makeRequest("{$baseUrl}/auth/refresh", 'POST', ['refresh_token' => $customerRefreshToken]);
echo "Status: {$refresh['code']}\n";
assert($refresh['code'] === 200, "Token refresh must return 200");
echo "New Token: " . substr($refresh['body']['data']['access_token'], 0, 25) . "...\n";

echo "\n=== 4. Testing Seller Login ===\n";
$sellerLogin = makeRequest("{$baseUrl}/seller/login", 'POST', [
    'email' => 'seller@celvas.in',
    'password' => 'Seller@12345'
]);
echo "Status: {$sellerLogin['code']}\n";
assert($sellerLogin['code'] === 200, "Seller login must return 200");
echo "Seller Store: " . $sellerLogin['body']['data']['extra']['seller']['store_name'] . "\n";

echo "\n=== 5. Testing Admin Login ===\n";
$adminLogin = makeRequest("{$baseUrl}/admin/login", 'POST', [
    'email' => 'admin@flipkart.local',
    'password' => 'Admin@12345'
]);
echo "Status: {$adminLogin['code']}\n";
assert($adminLogin['code'] === 200, "Admin login must return 200");
echo "Admin Name: " . $adminLogin['body']['data']['user']['name'] . " (" . $adminLogin['body']['data']['user']['role'] . ")\n";

echo "\n=== ALL AUTH TESTS PASSED PERFECTLY ===\n";
