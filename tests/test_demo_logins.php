<?php
$baseUrl = 'http://localhost/ecommerce_api/api';

function verifyOtpLogin($phone, $otp) {
    global $baseUrl;
    
    // 1. Send OTP
    $ch = curl_init("{$baseUrl}/auth/send-otp");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['phone' => $phone]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $sendRes = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    // 2. Verify OTP
    $ch = curl_init("{$baseUrl}/auth/verify-otp");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['phone' => $phone, 'otp' => $otp]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $verifyRes = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    return ['send' => $sendRes, 'verify' => $verifyRes];
}

echo "=== 1. USER LOGIN (Phone: 8000000001, OTP: 123456) ===\n";
$t1 = verifyOtpLogin('8000000001', '123456');
echo "Send OTP: " . ($t1['send']['message'] ?? 'err') . " (Demo OTP: " . ($t1['send']['data']['demo_otp'] ?? 'none') . ")\n";
echo "Verify OTP Status: " . ($t1['verify']['success'] ? 'SUCCESS' : 'FAIL') . "\n";
echo "Logged In User: " . ($t1['verify']['data']['user']['name'] ?? '') . " | Role: " . ($t1['verify']['data']['user']['role'] ?? '') . "\n\n";

echo "=== 2. ADMIN LOGIN (Phone: 9000000001, OTP: 123456) ===\n";
$t2 = verifyOtpLogin('9000000001', '123456');
echo "Send OTP: " . ($t2['send']['message'] ?? 'err') . " (Demo OTP: " . ($t2['send']['data']['demo_otp'] ?? 'none') . ")\n";
echo "Verify OTP Status: " . ($t2['verify']['success'] ? 'SUCCESS' : 'FAIL') . "\n";
echo "Logged In User: " . ($t2['verify']['data']['user']['name'] ?? '') . " | Role: " . ($t2['verify']['data']['user']['role'] ?? '') . "\n\n";

echo "=== 3. SUPER ADMIN LOGIN (Phone: 9876543210, OTP: 1369) ===\n";
$t3 = verifyOtpLogin('9876543210', '1369');
echo "Send OTP: " . ($t3['send']['message'] ?? 'err') . " (Demo OTP: " . ($t3['send']['data']['demo_otp'] ?? 'none') . ")\n";
echo "Verify OTP Status: " . ($t3['verify']['success'] ? 'SUCCESS' : 'FAIL') . "\n";
echo "Logged In User: " . ($t3['verify']['data']['user']['name'] ?? '') . " | Role: " . ($t3['verify']['data']['user']['role'] ?? '') . "\n\n";
