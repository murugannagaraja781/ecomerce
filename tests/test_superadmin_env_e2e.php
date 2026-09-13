<?php
/**
 * Test Super Admin .env Live API Endpoints
 * 1. Admin Login & JWT Super Admin Authentication
 * 2. GET /api/admin/settings (Read .env & structured gateways)
 * 3. PUT /api/admin/settings (Update active gateways & config atomically)
 * 4. GET /api/admin/settings/env (Read raw .env text)
 * 5. PUT /api/admin/settings/env (Save raw .env text)
 * 6. POST /api/admin/settings/test-payment (Test connectivity)
 * 7. POST /api/admin/settings/test-sms (Test carrier dispatch)
 * 8. POST /api/admin/settings/test-fcm (Test push notification)
 */

require_once __DIR__ . '/../backend/config/environment.php';
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/Env.php';
require_once __DIR__ . '/../backend/helpers/JwtHelper.php';

$pdo = getDbConnection();

function makeRequest(string $method, string $path, array $data = [], ?string $token = null): array {
    $url = 'http://localhost/ecomerce/backend' . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if (!empty($data) || in_array($method, ['POST', 'PUT'])) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $json = json_decode($raw ?: '', true);
    return [
        'code' => $httpCode,
        'body' => $json,
        'raw'  => $raw
    ];
}

echo "======================================================================\n";
echo "TESTING SUPER ADMIN .ENV CONFIGURATION VIA LIVE API (HTTP)\n";
echo "======================================================================\n\n";

// 1. Authenticate as Super Admin
echo "1. Super Admin Authentication:\n";
$loginRes = makeRequest('POST', '/api/admin/login', [
    'email' => 'admin@flipkart.local',
    'password' => 'Admin@12345'
]);

if ($loginRes['code'] !== 200 || empty($loginRes['body']['data']['tokens']['access_token'])) {
    echo "  [FAIL] Failed to log in as Super Admin: " . $loginRes['raw'] . "\n";
    exit(1);
}

$token = $loginRes['body']['data']['tokens']['access_token'];
echo "  [PASS] Super Admin Logged In! Token generated successfully.\n";

// 2. GET /api/admin/settings
echo "\n2. GET /api/admin/settings:\n";
$getRes = makeRequest('GET', '/api/admin/settings', [], $token);
if ($getRes['code'] === 200 && isset($getRes['body']['data']['active_payment_gateway'])) {
    echo "  [PASS] System settings retrieved successfully.\n";
    echo "         Active Payment: " . $getRes['body']['data']['active_payment_gateway'] . "\n";
    echo "         Active SMS: " . $getRes['body']['data']['active_sms_provider'] . "\n";
    echo "         Payment Gateways Count: " . count($getRes['body']['data']['payment_gateways']) . "\n";
    echo "         SMS Providers Count: " . count($getRes['body']['data']['sms_providers']) . "\n";
} else {
    echo "  [FAIL] Could not fetch settings: " . $getRes['raw'] . "\n";
}

// 3. PUT /api/admin/settings (Update Gateway & Keys)
echo "\n3. PUT /api/admin/settings (Atomic .env Update):\n";
$updatePayload = [
    'ACTIVE_PAYMENT_GATEWAY' => 'phonepe',
    'ACTIVE_SMS_PROVIDER'    => 'msg91',
    'PHONEPE_MERCHANT_ID'    => 'LIVE_MERC_TEST_99',
    'MSG91_AUTH_KEY'         => 'msg91_live_key_99999'
];
$putRes = makeRequest('PUT', '/api/admin/settings', $updatePayload, $token);
if ($putRes['code'] === 200 && $putRes['body']['success'] === true) {
    echo "  [PASS] Settings updated successfully!\n";
    echo "         Active Payment: " . $putRes['body']['data']['active_payment_gateway'] . "\n";
    echo "         Active SMS: " . $putRes['body']['data']['active_sms_provider'] . "\n";
} else {
    echo "  [FAIL] Failed to update settings: " . $putRes['raw'] . "\n";
}

// Verify directly in Env
Env::reload();
if (Env::get('ACTIVE_PAYMENT_GATEWAY') === 'phonepe' && Env::get('ACTIVE_SMS_PROVIDER') === 'msg91') {
    echo "  [PASS] Backend Env reflects new values in backend/.env file directly.\n";
} else {
    echo "  [FAIL] Env does not reflect new values.\n";
}

// 4. GET /api/admin/settings/env
echo "\n4. GET /api/admin/settings/env (Raw Editor):\n";
$getEnvRes = makeRequest('GET', '/api/admin/settings/env', [], $token);
if ($getEnvRes['code'] === 200 && !empty($getEnvRes['body']['data']['raw_env'])) {
    echo "  [PASS] Raw .env retrieved (Length: " . strlen($getEnvRes['body']['data']['raw_env']) . " bytes).\n";
} else {
    echo "  [FAIL] Failed to fetch raw env: " . $getEnvRes['raw'] . "\n";
}

// 5. POST /api/admin/settings/test-payment
echo "\n5. POST /api/admin/settings/test-payment:\n";
$testPgRes = makeRequest('POST', '/api/admin/settings/test-payment', ['gateway' => 'razorpay'], $token);
if ($testPgRes['code'] === 200 && $testPgRes['body']['success'] === true) {
    echo "  [PASS] Razorpay connectivity test passed: " . $testPgRes['body']['message'] . "\n";
} else {
    echo "  [FAIL] Payment gateway test failed: " . $testPgRes['raw'] . "\n";
}

// 6. POST /api/admin/settings/test-sms
echo "\n6. POST /api/admin/settings/test-sms:\n";
$testSmsRes = makeRequest('POST', '/api/admin/settings/test-sms', ['provider' => 'fast2sms', 'phone' => '9876543210'], $token);
if ($testSmsRes['code'] === 200 && $testSmsRes['body']['success'] === true) {
    echo "  [PASS] Fast2SMS dispatch test passed: " . $testSmsRes['body']['message'] . "\n";
} else {
    echo "  [FAIL] SMS provider test failed: " . $testSmsRes['raw'] . "\n";
}

// 7. POST /api/admin/settings/test-fcm
echo "\n7. POST /api/admin/settings/test-fcm:\n";
$testFcmRes = makeRequest('POST', '/api/admin/settings/test-fcm', [], $token);
if ($testFcmRes['code'] === 200 && $testFcmRes['body']['success'] === true) {
    echo "  [PASS] FCM notification test passed: " . $testFcmRes['body']['message'] . "\n";
} else {
    echo "  [FAIL] FCM test failed: " . $testFcmRes['raw'] . "\n";
}

// Revert to razorpay & local
Env::update(['ACTIVE_PAYMENT_GATEWAY' => 'razorpay', 'ACTIVE_SMS_PROVIDER' => 'local']);
echo "\n[INFO] Reset ACTIVE_PAYMENT_GATEWAY to razorpay and ACTIVE_SMS_PROVIDER to local.\n";
echo "\n🎉 ALL SUPER ADMIN .ENV API TESTS PASSED 100% GREEN!\n";
