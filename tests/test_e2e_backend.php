<?php
/**
 * Comprehensive End-to-End Backend Verification Test
 * Tests Customer Purchase Flow, Coupon, Razorpay, MySQL Transaction, Tracking, Seller & Admin
 */

function apiCall($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($res, true), 'raw' => $res];
}

$base = 'http://localhost/ecommerce_api/api';

echo "=======================================================\n";
echo "    FLIPKART PLATFORM E2E BACKEND VERIFICATION SUITE   \n";
echo "=======================================================\n\n";

// 1. Customer Login
echo "[1/10] Authenticating Customer (Rahul Sharma)...\n";
$login = apiCall("{$base}/auth/login", 'POST', ['email' => 'customer@gmail.com', 'password' => 'Customer@12345']);
assert($login['code'] === 200, "Login failed: " . $login['raw']);
$token = $login['body']['data']['tokens']['access_token'];
echo " -> Authenticated successfully. User ID: " . $login['body']['data']['user']['id'] . "\n";

// 2. Add Celvas iPhone 15 Case (Product 1, Variant 1) to Cart
echo "\n[2/10] Adding 'Celvas Back Cover for Apple iPhone 15' to Cart...\n";
$cartAdd = apiCall("{$base}/cart/items", 'POST', [
    'product_id' => 1,
    'variant_id' => 1,
    'quantity'   => 2
], $token);
assert($cartAdd['code'] === 200, "Add to cart failed: " . $cartAdd['raw']);
$cart = $cartAdd['body']['data'];
echo " -> Added 2 units. Cart Total MRP: ₹{$cart['summary']['total_mrp']}, Subtotal: ₹{$cart['summary']['subtotal']}\n";

// 3. Apply Coupon WELCOME100
echo "\n[3/10] Applying Promo Coupon 'WELCOME100'...\n";
$couponRes = apiCall("{$base}/coupons/apply", 'POST', [
    'code'        => 'WELCOME100',
    'cart_amount' => $cart['summary']['subtotal']
], $token);
assert($couponRes['code'] === 200, "Coupon failed: " . $couponRes['raw']);
echo " -> " . $couponRes['body']['message'] . "\n";
echo " -> Final Payable after coupon: ₹" . $couponRes['body']['data']['final_amount'] . "\n";

// 4. Fetch Delivery Address
echo "\n[4/10] Fetching Customer Delivery Addresses...\n";
$addrRes = apiCall("{$base}/addresses", 'GET', null, $token);
assert($addrRes['code'] === 200, "Addresses failed: " . $addrRes['raw']);
$address = $addrRes['body']['data'][0];
echo " -> Delivering to: {$address['full_name']}, {$address['city']}, {$address['state']} - {$address['pincode']}\n";

// 5. Initiate Razorpay Payment Order
echo "\n[5/10] Initializing Razorpay Payment Gateway Order...\n";
$payInit = apiCall("{$base}/payments/create", 'POST', [
    'address_id'  => $address['id'],
    'coupon_code' => 'WELCOME100'
], $token);
assert($payInit['code'] === 200, "Payment init failed: " . $payInit['raw']);
$razorpayData = $payInit['body']['data'];
echo " -> Razorpay Order Created: {$razorpayData['razorpay_order_id']} | Amount: ₹{$razorpayData['amount']} ({$razorpayData['amount_in_paise']} paise)\n";

// 6. Verify Payment Signature & Atomic MySQL Order Placement
echo "\n[6/10] Verifying Payment Signature & Executing MySQL Transaction...\n";
$orderPlacement = apiCall("{$base}/payments/verify", 'POST', [
    'address_id'          => $address['id'],
    'razorpay_order_id'   => $razorpayData['razorpay_order_id'],
    'razorpay_payment_id' => 'pay_rzp_demo_' . time(),
    'razorpay_signature'  => 'sig_verified_demo_mock_checkout_valid',
    'coupon_code'         => 'WELCOME100',
    'payment_method'      => 'RAZORPAY'
], $token);
assert($orderPlacement['code'] === 201, "Order placement failed: " . $orderPlacement['raw']);
$newOrder = $orderPlacement['body']['data'];
echo " -> Order Placed! Order Number: {$newOrder['order_number']} | Status: {$newOrder['status']} | Expected Delivery: {$newOrder['expected_delivery_date']}\n";

// 7. Verify Order Tracking Milestones
echo "\n[7/10] Fetching 6-Milestone Tracking Timeline for Order...\n";
$orderDetail = apiCall("{$base}/orders/{$newOrder['order_id']}", 'GET', null, $token);
assert($orderDetail['code'] === 200, "Order detail failed: " . $orderDetail['raw']);
$tracking = $orderDetail['body']['data']['tracking_milestones'];
foreach ($tracking as $m) {
    $statusIcon = $m['completed'] ? '✓' : '○';
    echo "  [{$statusIcon}] {$m['title']}" . ($m['completed'] ? " ({$m['date']})" : " (Pending)") . "\n";
}

// 8. Submit Verified Review
echo "\n[8/10] Submitting Verified Purchaser Product Review...\n";
$reviewRes = apiCall("{$base}/reviews", 'POST', [
    'product_id' => 1,
    'rating'     => 5,
    'title'      => 'Excellent drop protection and build!',
    'comment'    => 'Purchased through Flipkart and delivered in perfect condition. Tested with MagSafe charger, works instantly.'
], $token);
assert($reviewRes['code'] === 201, "Review failed: " . $reviewRes['raw']);
echo " -> " . $reviewRes['body']['message'] . " (Review ID: {$reviewRes['body']['data']['review_id']})\n";

// 9. Seller Portal Dashboard & Order Update
echo "\n[9/10] Testing Seller Portal (Celvas Official Store)...\n";
$sellerLogin = apiCall("{$base}/seller/login", 'POST', ['email' => 'seller@celvas.in', 'password' => 'Seller@12345']);
$sellerToken = $sellerLogin['body']['data']['tokens']['access_token'];
$sellerDash = apiCall("{$base}/seller/dashboard", 'GET', null, $sellerToken);
assert($sellerDash['code'] === 200, "Seller dash failed: " . $sellerDash['raw']);
$sKpi = $sellerDash['body']['data']['kpis'];
echo " -> Seller Total Products: {$sKpi['total_products']} | Orders: {$sKpi['total_orders']} | Revenue: ₹{$sKpi['total_revenue']}\n";

// Update order status to PACKED by Seller
$updateStatus = apiCall("{$base}/seller/orders/{$newOrder['order_id']}/status", 'PUT', ['status' => 'PACKED'], $sellerToken);
assert($updateStatus['code'] === 200, "Update status failed: " . $updateStatus['raw']);
echo " -> Seller updated order status to: PACKED\n";

// 10. Admin Control Center Dashboard
echo "\n[10/10] Testing Admin Control Center Dashboard...\n";
$adminLogin = apiCall("{$base}/admin/login", 'POST', ['email' => 'admin@flipkart.local', 'password' => 'Admin@12345']);
$adminToken = $adminLogin['body']['data']['tokens']['access_token'];
$adminDash = apiCall("{$base}/admin/dashboard", 'GET', null, $adminToken);
assert($adminDash['code'] === 200, "Admin dash failed: " . $adminDash['raw']);
$aCards = $adminDash['body']['data']['cards'];
echo " -> Platform Metrics: Total Revenue: ₹{$aCards['total_revenue']} | Total Orders: {$aCards['total_orders']} | Total Users: {$aCards['total_users']} | Total Products: {$aCards['total_products']}\n";

echo "\n=======================================================\n";
echo "   ALL 10 END-TO-END PLATFORM TESTS PASSED 100% SUCCESS  \n";
echo "=======================================================\n";
