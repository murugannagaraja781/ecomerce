<?php
/**
 * Orders, Tracking, Returns and Verified Reviews Controller
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';

class OrderController extends BaseController {

    /**
     * Get Customer Orders with Filtering (Tabs)
     */
    public function getOrders(): void {
        $user = AuthMiddleware::authenticate(true);
        $userId = $user['id'];
        $status = $_GET['status'] ?? null;

        $sql = "SELECT o.*, 
            oa.full_name as recipient_name, oa.city, oa.state, oa.pincode,
            (SELECT COUNT(*) FROM `order_items` oi WHERE oi.order_id = o.id) as total_items,
            (SELECT product_title FROM `order_items` oi WHERE oi.order_id = o.id LIMIT 1) as sample_product_title,
            (SELECT (SELECT image_url FROM `product_images` pi WHERE pi.product_id = oi.product_id AND pi.is_primary = 1 LIMIT 1) 
             FROM `order_items` oi WHERE oi.order_id = o.id LIMIT 1) as sample_product_image
            FROM `orders` o
            LEFT JOIN `order_addresses` oa ON o.id = oa.order_id
            WHERE o.user_id = ?";

        $bindings = [$userId];

        if (!empty($status) && $status !== 'ALL') {
            $sql .= " AND o.status = ?";
            $bindings[] = strtoupper($status);
        }

        $sql .= " ORDER BY o.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $orders = $stmt->fetchAll();

        Response::success($orders, "Orders retrieved");
    }

    /**
     * Get Order Detail & 6-Step Visual Tracking Timeline
     */
    public function getOrderById(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $orderIdOrNumber = $params['id'];

        $stmt = $this->db->prepare("SELECT o.*, s.store_name as seller_name 
            FROM `orders` o 
            LEFT JOIN `sellers` s ON o.seller_id = s.id
            WHERE (o.id = ? OR o.order_number = ?) AND (o.user_id = ? OR ? IN (1, 2))");
        $stmt->execute([$orderIdOrNumber, $orderIdOrNumber, $user['id'], $user['role_id']]);
        $order = $stmt->fetch();

        if (!$order) {
            Response::error("Order not found", [], 404);
        }

        $orderId = (int)$order['id'];

        // Order Items
        $iStmt = $this->db->prepare("SELECT oi.*, 
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = oi.product_id AND pi.is_primary = 1 LIMIT 1) as image_url
            FROM `order_items` oi WHERE oi.order_id = ?");
        $iStmt->execute([$orderId]);
        $order['items'] = $iStmt->fetchAll();

        // Delivery Address
        $aStmt = $this->db->prepare("SELECT * FROM `order_addresses` WHERE `order_id` = ?");
        $aStmt->execute([$orderId]);
        $order['delivery_address'] = $aStmt->fetch();

        // Payment Info
        $pStmt = $this->db->prepare("SELECT payment_method, razorpay_order_id, razorpay_payment_id, status, amount, created_at FROM `payments` WHERE `order_id` = ? ORDER BY id DESC LIMIT 1");
        $pStmt->execute([$orderId]);
        $order['payment'] = $pStmt->fetch();

        // Visual Tracking Timeline
        $tStmt = $this->db->prepare("SELECT * FROM `order_status_history` WHERE `order_id` = ? ORDER BY id ASC");
        $tStmt->execute([$orderId]);
        $order['timeline'] = $tStmt->fetchAll();

        // 6 Flipkart standard milestones
        $milestones = [
            'PLACED'           => ['title' => 'Order Placed', 'completed' => false, 'date' => null],
            'CONFIRMED'        => ['title' => 'Order Confirmed', 'completed' => false, 'date' => null],
            'PACKED'           => ['title' => 'Packed by Seller', 'completed' => false, 'date' => null],
            'SHIPPED'          => ['title' => 'Shipped / In Transit', 'completed' => false, 'date' => null],
            'OUT_FOR_DELIVERY' => ['title' => 'Out for Delivery', 'completed' => false, 'date' => null],
            'DELIVERED'        => ['title' => 'Delivered', 'completed' => false, 'date' => null]
        ];

        foreach ($order['timeline'] as $entry) {
            $st = $entry['status'];
            if (isset($milestones[$st])) {
                $milestones[$st]['completed'] = true;
                $milestones[$st]['date'] = $entry['created_at'];
                $milestones[$st]['notes'] = $entry['notes'];
            }
        }

        $order['tracking_milestones'] = array_values($milestones);

        Response::success($order, "Order details");
    }

    /**
     * Customer Cancel Order
     */
    public function cancelOrder(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $orderId = (int)$params['id'];
        $data = $this->getRequestData();

        $reason = trim($data['reason'] ?? 'Changed mind / ordered by mistake');

        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE `id` = ? AND `user_id` = ?");
        $stmt->execute([$orderId, $user['id']]);
        $order = $stmt->fetch();

        if (!$order) {
            Response::error("Order not found", [], 404);
        }

        if (in_array($order['status'], ['DELIVERED', 'CANCELLED', 'SHIPPED', 'OUT_FOR_DELIVERY'])) {
            Response::error("Order cannot be cancelled at this stage ({$order['status']}).", [], 400);
        }

        $this->db->beginTransaction();
        try {
            // Update order status
            $this->db->prepare("UPDATE `orders` SET `status` = 'CANCELLED', `cancellation_reason` = ?, `cancelled_at` = NOW() WHERE `id` = ?")
                ->execute([$reason, $orderId]);

            // Restock items
            $items = $this->db->query("SELECT * FROM `order_items` WHERE `order_id` = {$orderId}")->fetchAll();
            foreach ($items as $it) {
                if ($it['variant_id']) {
                    $this->db->prepare("UPDATE `product_variants` SET `stock` = `stock` + ? WHERE `id` = ?")
                        ->execute([$it['quantity'], $it['variant_id']]);
                    $this->db->prepare("UPDATE `inventory` SET `quantity` = `quantity` + ? WHERE `variant_id` = ?")
                        ->execute([$it['quantity'], $it['variant_id']]);
                }
            }

            // Tracking history
            $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `updated_by_user_id`) VALUES (?, ?, ?, ?)")
                ->execute([$orderId, 'CANCELLED', 'Cancelled by customer: ' . $reason, $user['id']]);

            $this->db->commit();
            Response::success(null, "Order cancelled successfully. If paid online, your refund will be processed to original source within 3-5 business days.");
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error("Cancellation failed: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Submit Return Request
     */
    public function createReturn(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('order_id')
            ->required('order_item_id')
            ->required('reason');

        if ($validator->fails()) {
            Response::error("Return request parameters missing", $validator->errors(), 422);
        }

        $orderId = (int)$data['order_id'];
        $orderItemId = (int)$data['order_item_id'];
        $reason = trim($data['reason']);
        $details = trim($data['details'] ?? '');
        $imageUrls = $data['image_urls'] ?? [];

        // Verify order item
        $stmt = $this->db->prepare("SELECT oi.*, o.user_id as order_user_id 
            FROM `order_items` oi 
            JOIN `orders` o ON oi.order_id = o.id 
            WHERE oi.id = ? AND oi.order_id = ? AND o.user_id = ?");
        $stmt->execute([$orderItemId, $orderId, $user['id']]);
        $item = $stmt->fetch();

        if (!$item) {
            Response::error("Order item not found or does not belong to your account", [], 404);
        }

        $returnNumber = 'RET' . date('Ymd') . rand(1000, 9999);

        $this->db->prepare("INSERT INTO `returns` 
            (`return_number`, `order_id`, `order_item_id`, `user_id`, `seller_id`, `reason`, `details`, `image_urls`, `status`, `refund_amount`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'REQUESTED', ?)")
            ->execute([
                $returnNumber,
                $orderId,
                $orderItemId,
                $user['id'],
                $item['seller_id'],
                $reason,
                $details,
                json_encode($imageUrls),
                $item['total_price']
            ]);

        // Update order status to RETURN_REQUESTED
        $this->db->prepare("UPDATE `orders` SET `status` = 'RETURN_REQUESTED' WHERE `id` = ?")->execute([$orderId]);

        Response::success(['return_number' => $returnNumber], "Return request submitted successfully. Our team is reviewing it.", 201);
    }

    /**
     * Post Verified Customer Review
     */
    public function addReview(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('product_id')
            ->required('rating')
            ->required('title')
            ->required('comment');

        if ($validator->fails()) {
            Response::error("Review validation failed", $validator->errors(), 422);
        }

        $productId = (int)$data['product_id'];
        $rating = max(1, min(5, (int)$data['rating']));
        $title = trim($data['title']);
        $comment = trim($data['comment']);

        // Check if verified purchaser
        $vpStmt = $this->db->prepare("SELECT COUNT(*) FROM `order_items` oi 
            JOIN `orders` o ON oi.order_id = o.id 
            WHERE oi.product_id = ? AND o.user_id = ?");
        $vpStmt->execute([$productId, $user['id']]);
        $isVp = ($vpStmt->fetchColumn() > 0) ? 1 : 0;

        $stmt = $this->db->prepare("INSERT INTO `reviews` 
            (`product_id`, `user_id`, `rating`, `title`, `comment`, `is_verified_purchase`, `status`) 
            VALUES (?, ?, ?, ?, ?, ?, 'APPROVED')");
        $stmt->execute([$productId, $user['id'], $rating, $title, $comment, $isVp]);
        $reviewId = (int)$this->db->lastInsertId();

        // Recalculate average rating on products table
        $avgStmt = $this->db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as rev_count FROM `reviews` WHERE `product_id` = ? AND `status` = 'APPROVED'");
        $avgStmt->execute([$productId]);
        $stats = $avgStmt->fetch();

        $this->db->prepare("UPDATE `products` SET `rating` = ?, `review_count` = ? WHERE `id` = ?")
            ->execute([round((float)$stats['avg_rating'], 2), (int)$stats['rev_count'], $productId]);

        Response::success(['review_id' => $reviewId], "Review posted successfully! Thank you for your feedback.", 201);
    }

    /**
     * Get Public Reviews for Product
     */
    public function getProductReviews(array $params): void {
        $productId = (int)$params['id'];
        $stmt = $this->db->prepare("SELECT r.*, u.name as user_name, u.avatar_url as user_avatar 
            FROM `reviews` r
            JOIN `users` u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'APPROVED'
            ORDER BY r.id DESC");
        $stmt->execute([$productId]);
        Response::success($stmt->fetchAll(), "Product reviews retrieved");
    }
}
