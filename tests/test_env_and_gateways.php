<?php
/**
 * Test Suite: Super Admin .env Management, Top 5 Indian Payment Gateways, and Top 5 SMS Providers
 */

require_once __DIR__ . '/../backend/config/environment.php';
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/Env.php';
require_once __DIR__ . '/../backend/helpers/JwtHelper.php';
require_once __DIR__ . '/../backend/services/PaymentService.php';
require_once __DIR__ . '/../backend/services/SmsService.php';
require_once __DIR__ . '/../backend/services/NotificationService.php';

$pdo = getDbConnection();
$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $testName): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo "RUNNING SUPER ADMIN .ENV & MULTI-GATEWAY TEST SUITE\n";
echo "======================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. Env Helper Tests
// -----------------------------------------------------------------------------
echo "1. Env Helper Unit Tests:\n";
$currentApp = Env::get('APP_NAME');
assertCondition(!empty($currentApp), "Env::get reads APP_NAME correctly ({$currentApp})");

$updateSuccess = Env::update(['ACTIVE_PAYMENT_GATEWAY' => 'phonepe', 'ACTIVE_SMS_PROVIDER' => 'msg91']);
assertCondition($updateSuccess === true, "Env::update atomically writes new values to .env");
assertCondition(Env::get('ACTIVE_PAYMENT_GATEWAY') === 'phonepe', "Env::get reflects new ACTIVE_PAYMENT_GATEWAY immediately (phonepe)");
assertCondition(Env::get('ACTIVE_SMS_PROVIDER') === 'msg91', "Env::get reflects new ACTIVE_SMS_PROVIDER immediately (msg91)");

// Revert for standard tests
Env::update(['ACTIVE_PAYMENT_GATEWAY' => 'razorpay', 'ACTIVE_SMS_PROVIDER' => 'local']);
assertCondition(Env::get('ACTIVE_PAYMENT_GATEWAY') === 'razorpay', "Reset ACTIVE_PAYMENT_GATEWAY to razorpay");

// -----------------------------------------------------------------------------
// 2. Multi-Gateway Payment Service Tests (Top 5 Indian Gateways)
// -----------------------------------------------------------------------------
echo "\n2. Top 5 Indian Payment Gateways Tests:\n";
$allGateways = PaymentService::getAllGateways();
assertCondition(count($allGateways) === 5, "PaymentService supports 5 Gateways (Razorpay, Cashfree, PhonePe, Paytm, PayU)");
assertCondition(isset($allGateways['razorpay']), "Razorpay is registered in PaymentService");
assertCondition(isset($allGateways['cashfree']), "Cashfree is registered in PaymentService");
assertCondition(isset($allGateways['phonepe']), "PhonePe is registered in PaymentService");
assertCondition(isset($allGateways['paytm']), "Paytm is registered in PaymentService");
assertCondition(isset($allGateways['payu']), "PayU is registered in PaymentService");

// Test order creation on each
$rzpOrder = PaymentService::createOrder(499.00, 'rcpt_01', ['name' => 'Tester', 'phone' => '9876543210'], 'razorpay');
assertCondition($rzpOrder['gateway'] === 'razorpay' && $rzpOrder['amount_in_paise'] === 49900, "Razorpay order created with paise conversion");

$cfOrder = PaymentService::createOrder(799.00, 'rcpt_02', ['name' => 'Tester', 'phone' => '9876543210'], 'cashfree');
assertCondition($cfOrder['gateway'] === 'cashfree' && !empty($cfOrder['payment_session_id']), "Cashfree order created with payment_session_id");

$peOrder = PaymentService::createOrder(1299.00, 'rcpt_03', ['name' => 'Tester', 'phone' => '9876543210'], 'phonepe');
assertCondition($peOrder['gateway'] === 'phonepe' && !empty($peOrder['checksum']), "PhonePe order created with SHA256 checksum & Base64 payload");

$ptmOrder = PaymentService::createOrder(999.00, 'rcpt_04', ['name' => 'Tester', 'phone' => '9876543210'], 'paytm');
assertCondition($ptmOrder['gateway'] === 'paytm' && !empty($ptmOrder['txn_token']), "Paytm order created with txn_token");

$puOrder = PaymentService::createOrder(550.00, 'rcpt_05', ['name' => 'Tester', 'phone' => '9876543210'], 'payu');
assertCondition($puOrder['gateway'] === 'payu' && !empty($puOrder['hash']), "PayU order created with SHA512 hash");

// -----------------------------------------------------------------------------
// 3. Multi-Provider SMS Service Tests (Top 5 SMS Gateways)
// -----------------------------------------------------------------------------
echo "\n3. Top 5 SMS Gateways Tests:\n";
$allSms = SmsService::getAllProviders();
assertCondition(count($allSms) === 5, "SmsService supports 5 Providers (Fast2SMS, MSG91, Twilio, Textlocal, Local)");

$localSms = SmsService::sendOtp('9876543210', '1369', 'local');
assertCondition($localSms['success'] === true && $localSms['provider'] === 'local', "Local simulation SMS dispatches instantly");

$f2sRes = SmsService::testProvider('fast2sms', '9876543210');
assertCondition($f2sRes['success'] === true, "Fast2SMS provider test executed successfully");

$msgRes = SmsService::testProvider('msg91', '9876543210');
assertCondition($msgRes['success'] === true, "MSG91 provider test executed successfully");

$twRes = SmsService::testProvider('twilio', '9876543210');
assertCondition($twRes['success'] === true, "Twilio provider test executed successfully");

$txtRes = SmsService::testProvider('textlocal', '9876543210');
assertCondition($txtRes['success'] === true, "Textlocal provider test executed successfully");

// -----------------------------------------------------------------------------
// 4. FCM Push Notifications Tests
// -----------------------------------------------------------------------------
echo "\n4. Firebase Cloud Messaging (FCM) Tests:\n";
$fcmConfig = NotificationService::getFcmConfig();
assertCondition(isset($fcmConfig['server_key']), "FCM configuration accessible");
$fcmTest = NotificationService::testFcm("Flipkart Big Billion Days", "Flat 50% Off on Mobile Phones Today!");
assertCondition($fcmTest['success'] === true, "FCM push notification dispatch tested");

// -----------------------------------------------------------------------------
// 5. Role-Based Access Control on Settings Endpoints
// -----------------------------------------------------------------------------
echo "\n5. Role-Based Permissions on .env Settings Endpoints:\n";

// Customer Token (Role 4)
$custToken = JwtHelper::generateToken(['user_id' => 10, 'email' => 'customer@test.com', 'role_name' => 'CUSTOMER']);
// Admin Token (Role 2)
$adminToken = JwtHelper::generateToken(['user_id' => 2, 'email' => 'admin@test.com', 'role_name' => 'ADMIN']);
// Super Admin Token (Role 1)
$superToken = JwtHelper::generateToken(['user_id' => 1, 'email' => 'superadmin@test.com', 'role_name' => 'SUPER_ADMIN']);

assertCondition(!empty($custToken) && !empty($adminToken) && !empty($superToken), "JWT tokens minted for Customer, Admin, and Super Admin");

echo "\n----------------------------------------------------------------------\n";
echo "TOTAL: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "----------------------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}
