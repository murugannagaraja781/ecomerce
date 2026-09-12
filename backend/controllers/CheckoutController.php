<?php
/**
 * Checkout, Coupon and Razorpay Payment Controller
 * Enforces server-side price validation, Razorpay HMAC SHA256 signature check,
 * and Atomic MySQL Order Placement Transaction with ROLLBACK safety.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/NotificationService.php';

class CheckoutController extends BaseController {

    /**
     * Validate and Apply Coupon
     */
    public function applyCoupon(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $validator = Validator::make($data)->required('code');
        if ($validator->fails()) {
            Response::error("Coupon code is required", $validator->errors(), 422);
        }

        $code = strtoupper(trim($data['code']));
        $cartTotal = (float)($data['cart_amount'] ?? 0.00);

        $stmt = $this->db->prepare("SELECT * FROM `coupons` 
            WHERE `code` = ? AND `is_active` = 1 
            AND `start_date` <= NOW() AND `expiry_date` >= NOW()");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            Response::error("Invalid or expired coupon code", [], 400);
        }

        if ($cartTotal < (float)$coupon['min_order_amount']) {
            Response::error("This coupon requires a minimum cart value of ₹" . number_format($coupon['min_order_amount'], 2), [], 400);
        }

        // Check per-user limit with prepared statement
        $uStmt = $this->db->prepare("SELECT COUNT(*) FROM `coupon_usage` WHERE `coupon_id` = ? AND `user_id` = ?");
        $uStmt->execute([(int)$coupon['id'], (int)$user['id']]);
        $usageCount = (int)$uStmt->fetchColumn();
        if ($usageCount >= (int)$coupon['per_user_limit']) {
            Response::error("You have already reached the maximum usage limit for this coupon", [], 400);
        }

        // Calculate discount amount
        $discount = 0.00;
        if ($coupon['discount_type'] === 'PERCENTAGE') {
            $discount = round(($cartTotal * (float)$coupon['discount_value']) / 100, 2);
            if (!empty($coupon['max_discount_amount']) && $discount > (float)$coupon['max_discount_amount']) {
                $discount = (float)$coupon['max_discount_amount'];
            }
        } else {
            $discount = min($cartTotal, (float)$coupon['discount_value']);
        }

        Response::success([
            'coupon_id'       => (int)$coupon['id'],
            'code'            => $coupon['code'],
            'discount_type'   => $coupon['discount_type'],
            'discount_amount' => $discount,
            'description'     => $coupon['description'],
            'final_amount'    => max(0.00, round($cartTotal - $discount, 2))
        ], "Coupon '{$code}' applied successfully! You saved ₹" . number_format($discount, 2));
    }

    /**
     * Create Multi-Gateway Payment Order (Razorpay / Cashfree / PhonePe / Paytm / PayU)
     */
    public function createRazorpayOrder(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $addressId = (int)($data['address_id'] ?? 0);
        $couponCode = trim($data['coupon_code'] ?? '');
        $requestedGateway = trim($data['gateway'] ?? PaymentService::getActiveGateway());

        // Verify Address
        $addrStmt = $this->db->prepare("SELECT * FROM `addresses` WHERE `id` = ? AND `user_id` = ? AND `deleted_at` IS NULL");
        $addrStmt->execute([$addressId, $user['id']]);
        $address = $addrStmt->fetch();

        if (!$address) {
            Response::error("Please select a valid delivery address", [], 400);
        }

        // Fetch Cart Items & Validate Stock
        $cartSummary = $this->calculateServerCart($user['id'], $couponCode);
        if (empty($cartSummary['items'])) {
            Response::error("Your cart is empty", [], 400);
        }
        if ($cartSummary['has_out_of_stock']) {
            Response::error("Some items in your cart are currently out of stock. Please update your cart.", [], 400);
        }

        $receiptId = 'rcpt_' . time() . '_' . $user['id'];
        $customer = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '9876543210'
        ];

        $paymentOrder = PaymentService::createOrder($cartSummary['total_payable'], $receiptId, $customer, $requestedGateway);
        $paymentOrder['summary'] = $cartSummary;

        Response::success($paymentOrder, "Payment order initialized via " . ($paymentOrder['gateway_name'] ?? 'Gateway'));
    }

    /**
     * Verify Multi-Gateway Payment & Atomic Order Placement
     */
    public function verifyPaymentAndPlaceOrder(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $paymentMethod = strtoupper(trim($data['payment_method'] ?? PaymentService::getActiveGateway()));

        $validator = Validator::make($data)->required('address_id');
        if ($paymentMethod === 'RAZORPAY') {
            $validator->required('razorpay_order_id')->required('razorpay_payment_id');
        }

        if ($validator->fails()) {
            Response::error("Payment verification parameters missing", $validator->errors(), 422);
        }

        $addressId = (int)$data['address_id'];
        $razorpayOrderId = trim($data['razorpay_order_id'] ?? $data['order_id'] ?? ('cod_ord_' . bin2hex(random_bytes(6))));
        $razorpayPaymentId = trim($data['razorpay_payment_id'] ?? $data['payment_id'] ?? ('cod_pay_' . bin2hex(random_bytes(6))));
        $razorpaySignature = trim($data['razorpay_signature'] ?? $data['signature'] ?? '');
        $couponCode = trim($data['coupon_code'] ?? '');

        // Verify payment with PaymentService
        $isVerified = PaymentService::verifyPayment([
            'payment_method'      => $paymentMethod,
            'order_id'            => $razorpayOrderId,
            'razorpay_order_id'   => $razorpayOrderId,
            'payment_id'          => $razorpayPaymentId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'signature'           => $razorpaySignature,
            'razorpay_signature'  => $razorpaySignature
        ]);

        if (!$isVerified) {
            Response::error("Payment verification failed. Please try again.", [], 400);
        }

        // Place Order inside Atomic MySQL Transaction
        $order = $this->executeAtomicOrderPlacement(
            $user,
            $addressId,
            $paymentMethod,
            $razorpayOrderId,
            $razorpayPaymentId,
            $razorpaySignature,
            $couponCode
        );

        // Send FCM Push Notification
        NotificationService::sendPush(
            "Order Confirmed! #{$order['order_number']}",
            "Your order for ₹" . number_format($order['total_payable'], 2) . " has been placed successfully. Delivery expected by {$order['expected_delivery_date']}.",
            null,
            ['order_id' => $order['order_id'], 'order_number' => $order['order_number']]
        );

        Response::success($order, "Order placed successfully! Thank you for shopping with us.", 201);
    }

    /**
     * Atomic MySQL Order Placement Transaction
     */
    private function executeAtomicOrderPlacement(
        array $user,
        int $addressId,
        string $paymentMethod,
        ?string $razorpayOrderId,
        ?string $razorpayPaymentId,
        ?string $razorpaySignature,
        string $couponCode
    ): array {
        $userId = $user['id'];

        // 1. Begin Transaction
        $this->db->beginTransaction();

        try {
            // 2. Fetch & Lock Address
            $addrStmt = $this->db->prepare("SELECT * FROM `addresses` WHERE `id` = ? AND `user_id` = ? AND `deleted_at` IS NULL");
            $addrStmt->execute([$addressId, $userId]);
            $address = $addrStmt->fetch();

            if (!$address) {
                throw new Exception("Delivery address could not be found.");
            }

            // 3. Lock Cart Items
            $cartStmt = $this->db->prepare("SELECT id FROM `carts` WHERE `user_id` = ?");
            $cartStmt->execute([$userId]);
            $cartId = $cartStmt->fetchColumn();

            if (!$cartId) {
                throw new Exception("Cart not found.");
            }

            $itemsStmt = $this->db->prepare("SELECT ci.*, 
                p.title as product_title, p.base_price, p.base_mrp, p.seller_id as default_seller_id,
                pv.title as variant_title, pv.sku, pv.price as variant_price, pv.mrp as variant_mrp, pv.stock as variant_stock
                FROM `cart_items` ci
                JOIN `products` p ON ci.product_id = p.id
                LEFT JOIN `product_variants` pv ON ci.variant_id = pv.id
                WHERE ci.cart_id = ?
                FOR UPDATE");
            $itemsStmt->execute([$cartId]);
            $items = $itemsStmt->fetchAll();

            if (empty($items)) {
                throw new Exception("Your cart is empty.");
            }

            // 4. Validate stock & compute totals
            $totalMrp = 0.00;
            $totalSelling = 0.00;
            $sellerId = $items[0]['seller_id'] ?? $items[0]['default_seller_id'];

            foreach ($items as $it) {
                $qty = (int)$it['quantity'];
                $availableStock = $it['variant_id'] ? (int)$it['variant_stock'] : 50;

                if ($availableStock < $qty) {
                    throw new Exception("Insufficient stock for product '{$it['product_title']}'. Available: {$availableStock}");
                }

                $price = (float)($it['variant_price'] ?? $it['base_price']);
                $mrp = (float)($it['variant_mrp'] ?? $it['base_mrp']);

                $totalSelling += ($price * $qty);
                $totalMrp += ($mrp * $qty);
            }

            // 5. Coupon Discount
            $couponDiscount = 0.00;
            $couponId = null;
            if (!empty($couponCode)) {
                $cpStmt = $this->db->prepare("SELECT * FROM `coupons` WHERE `code` = ? AND `is_active` = 1 AND `start_date` <= NOW() AND `expiry_date` >= NOW()");
                $cpStmt->execute([$couponCode]);
                $coupon = $cpStmt->fetch();

                if ($coupon && $totalSelling >= (float)$coupon['min_order_amount']) {
                    $couponId = (int)$coupon['id'];
                    if ($coupon['discount_type'] === 'PERCENTAGE') {
                        $couponDiscount = round(($totalSelling * (float)$coupon['discount_value']) / 100, 2);
                        if (!empty($coupon['max_discount_amount'])) {
                            $couponDiscount = min($couponDiscount, (float)$coupon['max_discount_amount']);
                        }
                    } else {
                        $couponDiscount = min($totalSelling, (float)$coupon['discount_value']);
                    }
                }
            }

            $deliveryCharge = ($totalSelling >= 499.00) ? 0.00 : 40.00;
            $totalDiscount = max(0.00, $totalMrp - $totalSelling);
            $totalPayable = max(0.00, round(($totalSelling - $couponDiscount) + $deliveryCharge, 2));

            $isCod = ($paymentMethod === 'COD');
            $initialOrderStatus = $isCod ? 'PLACED' : 'CONFIRMED';
            $orderPaymentStatus = $isCod ? 'PENDING' : 'PAID';
            $paymentRecordStatus = $isCod ? 'PENDING' : 'CAPTURED';

            // 6. Generate Order Number
            $orderNumber = 'OD' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
            $expectedDelivery = date('Y-m-d', strtotime('+4 days'));

            // 7. Insert Order
            $orderStmt = $this->db->prepare("INSERT INTO `orders` 
                (`order_number`, `user_id`, `seller_id`, `status`, `total_mrp`, `total_discount`, `coupon_discount`, `delivery_charge`, `tax_amount`, `total_payable`, `payment_status`, `payment_method`, `expected_delivery_date`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?)");

            $orderStmt->execute([
                $orderNumber,
                $userId,
                $sellerId,
                $initialOrderStatus,
                $totalMrp,
                $totalDiscount,
                $couponDiscount,
                $deliveryCharge,
                $totalPayable,
                $orderPaymentStatus,
                $paymentMethod,
                $expectedDelivery
            ]);
            $orderId = (int)$this->db->lastInsertId();

            // 8. Insert Order Items & Deduct Inventory
            $oitemStmt = $this->db->prepare("INSERT INTO `order_items` 
                (`order_id`, `product_id`, `variant_id`, `seller_id`, `product_title`, `variant_title`, `sku`, `price`, `mrp`, `quantity`, `total_price`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($items as $it) {
                $unitPrice = (float)($it['variant_price'] ?? $it['base_price']);
                $unitMrp = (float)($it['variant_mrp'] ?? $it['base_mrp']);
                $qty = (int)$it['quantity'];
                $itemTotal = round($unitPrice * $qty, 2);

                $oitemStmt->execute([
                    $orderId,
                    $it['product_id'],
                    $it['variant_id'],
                    $it['seller_id'] ?? $sellerId,
                    $it['product_title'],
                    $it['variant_title'],
                    $it['sku'],
                    $unitPrice,
                    $unitMrp,
                    $qty,
                    $itemTotal,
                    $initialOrderStatus
                ]);

                // Deduct Variant Stock and Log Inventory Transaction
                if ($it['variant_id']) {
                    $this->db->prepare("UPDATE `product_variants` SET `stock` = GREATEST(0, `stock` - ?) WHERE `id` = ?")
                        ->execute([$qty, $it['variant_id']]);

                    // Fetch inventory record to get inventory_id and previous_quantity
                    $invStmt = $this->db->prepare("SELECT id, quantity FROM `inventory` WHERE `variant_id` = ? FOR UPDATE");
                    $invStmt->execute([$it['variant_id']]);
                    $invRec = $invStmt->fetch();

                    if ($invRec) {
                        $prevQty = (int)$invRec['quantity'];
                        $newStock = max(0, $prevQty - $qty);
                        $this->db->prepare("UPDATE `inventory` SET `quantity` = ? WHERE `id` = ?")
                            ->execute([$newStock, $invRec['id']]);

                        // Audit Log in inventory_transactions table
                        $this->db->prepare("INSERT INTO `inventory_transactions` 
                            (`inventory_id`, `order_id`, `type`, `quantity_change`, `previous_quantity`, `new_quantity`, `notes`) 
                            VALUES (?, ?, 'SALE_CONFIRMED', ?, ?, ?, ?)")
                            ->execute([$invRec['id'], $orderId, -$qty, $prevQty, $newStock, "Customer purchase Order #{$orderNumber}"]);
                    }
                }
            }

            // 9. Snapshot Order Address
            $oaddrStmt = $this->db->prepare("INSERT INTO `order_addresses` 
                (`order_id`, `full_name`, `phone`, `pincode`, `address_line1`, `address_line2`, `landmark`, `city`, `state`, `address_type`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $oaddrStmt->execute([
                $orderId,
                $address['full_name'],
                $address['phone'],
                $address['pincode'],
                $address['address_line1'],
                $address['address_line2'],
                $address['landmark'],
                $address['city'],
                $address['state'],
                $address['address_type']
            ]);

            // 10. Record Payment
            $payStmt = $this->db->prepare("INSERT INTO `payments` 
                (`order_id`, `user_id`, `payment_method`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `amount`, `currency`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'INR', ?)");

            $payStmt->execute([
                $orderId,
                $userId,
                $paymentMethod,
                $razorpayOrderId ?: ($isCod ? 'COD_' . $orderNumber : null),
                $razorpayPaymentId ?: ($isCod ? 'COD_PENDING' : null),
                $razorpaySignature ?: null,
                $totalPayable,
                $paymentRecordStatus
            ]);

            // 11. Initial Tracking History
            $histStmt = $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `updated_by_user_id`) VALUES (?, ?, ?, ?)");
            $histStmt->execute([$orderId, 'PLACED', $isCod ? 'Order placed with Cash On Delivery' : 'Order created and payment authorized', $userId]);

            if (!$isCod) {
                $histStmt->execute([$orderId, 'CONFIRMED', 'Order confirmed by seller. Packing started.', 1]);
            }

            // 12. Record Coupon Usage
            if ($couponId) {
                $this->db->prepare("INSERT INTO `coupon_usage` (`coupon_id`, `user_id`, `order_id`, `discount_amount`) VALUES (?, ?, ?, ?)")
                    ->execute([$couponId, $userId, $orderId, $couponDiscount]);
            }

            // 13. Clear Cart
            $this->db->prepare("DELETE FROM `cart_items` WHERE `cart_id` = ?")->execute([$cartId]);

            // 14. Commit Transaction
            $this->db->commit();

            return [
                'order_id'               => $orderId,
                'order_number'           => $orderNumber,
                'total_payable'          => $totalPayable,
                'expected_delivery_date' => $expectedDelivery,
                'status'                 => 'CONFIRMED'
            ];

        } catch (Exception $e) {
            // Rollback on any failure
            $this->db->rollBack();
            Response::error("Order placement failed: " . $e->getMessage(), [], 500);
        }
    }

    private function calculateServerCart(int $userId, string $couponCode = ''): array {
        $cartStmt = $this->db->prepare("SELECT id FROM `carts` WHERE `user_id` = ?");
        $cartStmt->execute([$userId]);
        $cartId = $cartStmt->fetchColumn();

        if (!$cartId) {
            return ['items' => [], 'has_out_of_stock' => false, 'total_payable' => 0.00];
        }

        $itemsStmt = $this->db->prepare("SELECT ci.*, 
            p.title as product_title, p.base_price, p.base_mrp,
            pv.title as variant_title, pv.sku, pv.price as variant_price, pv.mrp as variant_mrp, pv.stock as variant_stock
            FROM `cart_items` ci
            JOIN `products` p ON ci.product_id = p.id
            LEFT JOIN `product_variants` pv ON ci.variant_id = pv.id
            WHERE ci.cart_id = ?");
        $itemsStmt->execute([$cartId]);
        $items = $itemsStmt->fetchAll();

        $totalMrp = 0.00;
        $totalSelling = 0.00;
        $outOfStock = false;

        foreach ($items as $it) {
            $qty = (int)$it['quantity'];
            $stock = $it['variant_id'] ? (int)$it['variant_stock'] : 50;
            if ($stock < $qty) {
                $outOfStock = true;
            }
            $price = (float)($it['variant_price'] ?? $it['base_price']);
            $mrp = (float)($it['variant_mrp'] ?? $it['base_mrp']);
            $totalSelling += ($price * $qty);
            $totalMrp += ($mrp * $qty);
        }

        $couponDiscount = 0.00;
        if (!empty($couponCode)) {
            $cpStmt = $this->db->prepare("SELECT * FROM `coupons` WHERE `code` = ? AND `is_active` = 1 AND `start_date` <= NOW() AND `expiry_date` >= NOW()");
            $cpStmt->execute([$couponCode]);
            $coupon = $cpStmt->fetch();
            if ($coupon && $totalSelling >= (float)$coupon['min_order_amount']) {
                if ($coupon['discount_type'] === 'PERCENTAGE') {
                    $couponDiscount = round(($totalSelling * (float)$coupon['discount_value']) / 100, 2);
                    if (!empty($coupon['max_discount_amount'])) {
                        $couponDiscount = min($couponDiscount, (float)$coupon['max_discount_amount']);
                    }
                } else {
                    $couponDiscount = min($totalSelling, (float)$coupon['discount_value']);
                }
            }
        }

        $deliveryCharge = ($totalSelling >= 499.00 || empty($items)) ? 0.00 : 40.00;
        $totalPayable = max(0.00, round(($totalSelling - $couponDiscount) + $deliveryCharge, 2));

        return [
            'items'            => $items,
            'has_out_of_stock' => $outOfStock,
            'total_mrp'        => round($totalMrp, 2),
            'subtotal'         => round($totalSelling, 2),
            'coupon_discount'  => round($couponDiscount, 2),
            'delivery_charge'  => round($deliveryCharge, 2),
            'total_payable'    => round($totalPayable, 2)
        ];
    }

    /**
     * Razorpay Webhook Handler
     * Verifies webhook HMAC signature and updates order status idempotently
     */
    public function handleWebhook(): void {
        $webhookSecret = defined('RAZORPAY_WEBHOOK_SECRET') ? RAZORPAY_WEBHOOK_SECRET : RAZORPAY_KEY_SECRET;
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (empty($signature) || empty($payload)) {
            Response::error("Missing webhook signature or payload", [], 400);
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            Logger::error("Razorpay webhook signature verification failed");
            Response::error("Invalid webhook signature", [], 400);
        }

        $data = json_decode($payload, true);
        if (!$data || !isset($data['event'])) {
            Response::error("Invalid webhook payload format", [], 400);
        }

        $event = $data['event'];
        Logger::info("Razorpay webhook received: {$event}");

        if ($event === 'payment.captured') {
            $paymentEntity = $data['payload']['payment']['entity'] ?? [];
            $rzpOrderId = $paymentEntity['order_id'] ?? null;
            $rzpPaymentId = $paymentEntity['id'] ?? null;

            if ($rzpOrderId) {
                $stmt = $this->db->prepare("SELECT * FROM `payments` WHERE `razorpay_order_id` = ?");
                $stmt->execute([$rzpOrderId]);
                $payment = $stmt->fetch();

                if ($payment && $payment['status'] !== 'CAPTURED') {
                    $this->db->prepare("UPDATE `payments` SET `status` = 'CAPTURED', `razorpay_payment_id` = ? WHERE `id` = ?")
                        ->execute([$rzpPaymentId, $payment['id']]);

                    $this->db->prepare("UPDATE `orders` SET `payment_status` = 'PAID', `status` = 'CONFIRMED' WHERE `id` = ?")
                        ->execute([$payment['order_id']]);

                    $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`) VALUES (?, 'CONFIRMED', 'Payment verified via Razorpay Webhook')")
                        ->execute([$payment['order_id']]);
                }
            }
        } elseif ($event === 'payment.failed') {
            $paymentEntity = $data['payload']['payment']['entity'] ?? [];
            $rzpOrderId = $paymentEntity['order_id'] ?? null;
            if ($rzpOrderId) {
                $this->db->prepare("UPDATE `payments` SET `status` = 'FAILED' WHERE `razorpay_order_id` = ?")
                    ->execute([$rzpOrderId]);
            }
        }

        Response::success(['status' => 'acknowledged', 'event' => $event], "Webhook processed successfully");
    }
}
