<?php
/**
 * Admin Control Center API Controller
 * Full administrative oversight: Users, Sellers, Catalog, Orders, Payments, Coupons, Reports & Logs
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../helpers/Env.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/SmsService.php';
require_once __DIR__ . '/../services/NotificationService.php';

class AdminController extends BaseController {

    private function authenticateAdmin(): array {
        return RoleMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'SUPPORT_AGENT']);
    }

    private function authenticateSuperAdmin(): array {
        return RoleMiddleware::authorize(['SUPER_ADMIN']);
    }

    /**
     * Admin Overview Dashboard
     */
    public function getDashboard(): void {
        $admin = $this->authenticateAdmin();

        $totalRevenue = (float)$this->db->query("SELECT COALESCE(SUM(total_payable), 0) FROM `orders` WHERE `payment_status` = 'PAID'")->fetchColumn();
        $todayRevenue = (float)$this->db->query("SELECT COALESCE(SUM(total_payable), 0) FROM `orders` WHERE `payment_status` = 'PAID' AND DATE(`created_at`) = CURDATE()")->fetchColumn();
        $totalOrders = (int)$this->db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
        $pendingOrders = (int)$this->db->query("SELECT COUNT(*) FROM `orders` WHERE `status` IN ('PLACED', 'CONFIRMED', 'PACKED')")->fetchColumn();
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM `users` WHERE `role_id` = 4 AND `deleted_at` IS NULL")->fetchColumn();
        $totalSellers = (int)$this->db->query("SELECT COUNT(*) FROM `sellers` WHERE `deleted_at` IS NULL")->fetchColumn();
        $totalProducts = (int)$this->db->query("SELECT COUNT(*) FROM `products` WHERE `deleted_at` IS NULL")->fetchColumn();
        $lowStock = (int)$this->db->query("SELECT COUNT(*) FROM `inventory` WHERE `quantity` <= `low_stock_threshold`")->fetchColumn();
        $returnsCount = (int)$this->db->query("SELECT COUNT(*) FROM `returns` WHERE `status` = 'REQUESTED'")->fetchColumn();

        // Recent Orders
        $recentOrders = $this->db->query("SELECT o.id, o.order_number, o.status, o.total_payable, o.payment_status, o.created_at, u.name as customer_name 
            FROM `orders` o 
            JOIN `users` u ON o.user_id = u.id 
            ORDER BY o.id DESC LIMIT 6")->fetchAll();

        // Top Selling Categories
        $topCategories = $this->db->query("SELECT c.name, COUNT(p.id) as product_count 
            FROM `categories` c 
            JOIN `products` p ON p.category_id = c.id 
            GROUP BY c.id 
            ORDER BY product_count DESC LIMIT 5")->fetchAll();

        Response::success([
            'cards' => [
                'total_revenue'   => $totalRevenue,
                'today_revenue'   => $todayRevenue,
                'total_orders'    => $totalOrders,
                'pending_orders'  => $pendingOrders,
                'total_users'     => $totalUsers,
                'total_sellers'   => $totalSellers,
                'total_products'  => $totalProducts,
                'low_stock_count' => $lowStock,
                'open_returns'    => $returnsCount
            ],
            'sales_overview' => [
                ['label' => 'Mon', 'sales' => 45000],
                ['label' => 'Tue', 'sales' => 52000],
                ['label' => 'Wed', 'sales' => 78000],
                ['label' => 'Thu', 'sales' => 61000],
                ['label' => 'Fri', 'sales' => 92000],
                ['label' => 'Sat', 'sales' => 115000],
                ['label' => 'Sun', 'sales' => 135000]
            ],
            'top_categories' => $topCategories,
            'recent_orders'  => $recentOrders
        ], "Admin dashboard metrics");
    }

    /**
     * User Management
     */
    public function getUsers(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `users` WHERE `deleted_at` IS NULL")->fetchColumn();

        $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, u.phone, u.role_id, r.name as role_name, u.is_active, u.is_verified, u.created_at 
            FROM `users` u 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE u.deleted_at IS NULL 
            ORDER BY u.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "Users list");
        } else {
            Response::success($items, "Users list");
        }
    }

    public function updateUser(array $params): void {
        $admin = $this->authenticateAdmin();
        $userId = (int)$params['id'];
        $data = $this->getRequestData();

        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $this->db->prepare("UPDATE `users` SET `is_active` = ? WHERE `id` = ?")->execute([$isActive, $userId]);

        Logger::logAdmin($admin['id'], "UPDATE_USER_STATUS", "USERS", $userId, null, ['is_active' => $isActive]);
        Response::success(null, "User status updated successfully");
    }

    /**
     * Seller Verification & Management
     */
    public function getSellers(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `sellers` WHERE `deleted_at` IS NULL")->fetchColumn();

        $stmt = $this->db->prepare("SELECT s.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone,
            (SELECT COUNT(*) FROM `products` p WHERE p.seller_id = s.id AND p.deleted_at IS NULL) as total_products,
            (SELECT COALESCE(SUM(total_price), 0) FROM `order_items` oi WHERE oi.seller_id = s.id) as total_sales
            FROM `sellers` s 
            JOIN `users` u ON s.user_id = u.id 
            WHERE s.deleted_at IS NULL 
            ORDER BY s.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "Sellers list");
        } else {
            Response::success($items, "Sellers list");
        }
    }

    public function updateSellerStatus(array $params): void {
        $admin = $this->authenticateAdmin();
        $sellerId = (int)$params['id'];
        $data = $this->getRequestData();

        $status = strtoupper(trim($data['status'] ?? 'APPROVED'));
        $allowed = ['PENDING', 'APPROVED', 'REJECTED', 'SUSPENDED'];

        if (!in_array($status, $allowed)) {
            Response::error("Invalid seller status", [], 422);
        }

        $this->db->prepare("UPDATE `sellers` SET `status` = ? WHERE `id` = ?")->execute([$status, $sellerId]);
        Logger::logAdmin($admin['id'], "UPDATE_SELLER_STATUS", "SELLERS", $sellerId, null, ['status' => $status]);

        Response::success(null, "Seller status updated to {$status}");
    }

    /**
     * Admin Category Management
     */
    public function getCategories(): void {
        $this->authenticateAdmin();
        $stmt = $this->db->query("SELECT c.*, p.name as parent_name,
            (SELECT COUNT(*) FROM `products` prod WHERE prod.category_id = c.id) as product_count
            FROM `categories` c 
            LEFT JOIN `categories` p ON c.parent_id = p.id 
            ORDER BY c.sort_order ASC, c.id DESC");
        Response::success($stmt->fetchAll(), "Admin categories list");
    }

    public function createCategory(): void {
        $admin = $this->authenticateAdmin();
        $data = $this->getRequestData();

        $validator = Validator::make($data)->required('name');
        if ($validator->fails()) {
            Response::error("Category name is required", $validator->errors(), 422);
        }

        $name = trim($data['name']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . '-' . rand(10, 99);
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $imageUrl = $data['image_url'] ?? null;
        $description = $data['description'] ?? null;
        $sortOrder = (int)($data['sort_order'] ?? 0);

        $stmt = $this->db->prepare("INSERT INTO `categories` (`parent_id`, `name`, `slug`, `image_url`, `description`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$parentId, $name, $slug, $imageUrl, $description, $sortOrder]);
        $id = (int)$this->db->lastInsertId();

        Logger::logAdmin($admin['id'], "CREATE_CATEGORY", "CATEGORIES", $id, null, ['name' => $name]);
        Response::success(['id' => $id, 'slug' => $slug], "Category created successfully", 201);
    }

    /**
     * Admin Order Management
     */
    public function getOrders(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();

        $stmt = $this->db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email,
            oa.city, oa.state, oa.pincode,
            (SELECT COUNT(*) FROM `order_items` oi WHERE oi.order_id = o.id) as item_count
            FROM `orders` o 
            JOIN `users` u ON o.user_id = u.id 
            LEFT JOIN `order_addresses` oa ON o.id = oa.order_id 
            ORDER BY o.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "All orders list");
        } else {
            Response::success($items, "All orders list");
        }
    }

    public function updateOrderStatus(array $params): void {
        $admin = $this->authenticateAdmin();
        $orderId = (int)$params['id'];
        $data = $this->getRequestData();

        $status = strtoupper(trim($data['status'] ?? ''));
        $notes = trim($data['notes'] ?? 'Updated by store admin');

        $this->db->prepare("UPDATE `orders` SET `status` = ? WHERE `id` = ?")->execute([$status, $orderId]);
        $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `updated_by_user_id`) VALUES (?, ?, ?, ?)")
            ->execute([$orderId, $status, $notes, $admin['id']]);

        Logger::logAdmin($admin['id'], "UPDATE_ORDER_STATUS", "ORDERS", $orderId, null, ['status' => $status]);
        Response::success(null, "Order status updated to {$status}");
    }

    /**
     * Admin Payments Log
     */
    public function getPayments(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `payments`")->fetchColumn();

        $stmt = $this->db->prepare("SELECT p.*, o.order_number, u.name as customer_name, u.email as customer_email 
            FROM `payments` p 
            JOIN `orders` o ON p.order_id = o.id 
            JOIN `users` u ON p.user_id = u.id 
            ORDER BY p.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "Payments list");
        } else {
            Response::success($items, "Payments list");
        }
    }

    /**
     * Admin Coupons
     */
    public function getCoupons(): void {
        $this->authenticateAdmin();
        $stmt = $this->db->query("SELECT c.*, 
            (SELECT COUNT(*) FROM `coupon_usage` cu WHERE cu.coupon_id = c.id) as total_used 
            FROM `coupons` c 
            ORDER BY c.id DESC");
        Response::success($stmt->fetchAll(), "Coupons list");
    }

    public function createCoupon(): void {
        $admin = $this->authenticateAdmin();
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('code')
            ->required('discount_type')
            ->required('discount_value')
            ->required('start_date')
            ->required('expiry_date');

        if ($validator->fails()) {
            Response::error("Coupon validation failed", $validator->errors(), 422);
        }

        $code = strtoupper(trim($data['code']));
        $stmt = $this->db->prepare("INSERT INTO `coupons` 
            (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `start_date`, `expiry_date`, `usage_limit`, `per_user_limit`, `is_active`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

        $stmt->execute([
            $code,
            $data['description'] ?? "Promotional discount code {$code}",
            $data['discount_type'],
            (float)$data['discount_value'],
            (float)($data['min_order_amount'] ?? 0.00),
            !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null,
            $data['start_date'],
            $data['expiry_date'],
            (int)($data['usage_limit'] ?? 1000),
            (int)($data['per_user_limit'] ?? 1)
        ]);

        $id = (int)$this->db->lastInsertId();
        Logger::logAdmin($admin['id'], "CREATE_COUPON", "COUPONS", $id, null, ['code' => $code]);
        Response::success(['id' => $id, 'code' => $code], "Coupon created successfully", 201);
    }

    /**
     * Admin Reports & Analytics
     */
    public function getReports(): void {
        $this->authenticateAdmin();

        // Monthly sales
        $salesStmt = $this->db->query("SELECT DATE_FORMAT(created_at, '%b %Y') as period, 
            COUNT(*) as orders_count, 
            SUM(total_payable) as revenue 
            FROM `orders` 
            WHERE `payment_status` = 'PAID' 
            GROUP BY DATE_FORMAT(created_at, '%b %Y') 
            ORDER BY MIN(created_at) DESC LIMIT 12");
        $monthlySales = $salesStmt->fetchAll();

        // Top 5 Products by Revenue
        $topProducts = $this->db->query("SELECT product_title, SUM(quantity) as units_sold, SUM(total_price) as gross_revenue 
            FROM `order_items` 
            GROUP BY product_id, product_title 
            ORDER BY gross_revenue DESC LIMIT 5")->fetchAll();

        Response::success([
            'monthly_sales' => $monthlySales,
            'top_products'  => $topProducts
        ], "Reports generated");
    }

    /**
     * Admin Activity Logs
     */
    public function getLogs(): void {
        $this->authenticateAdmin();
        $stmt = $this->db->query("SELECT l.*, u.name as admin_name, u.email as admin_email 
            FROM `admin_logs` l 
            JOIN `users` u ON l.user_id = u.id 
            ORDER BY l.id DESC LIMIT 50");
        Response::success($stmt->fetchAll(), "Admin audit trail");
    }

    /**
     * Admin Returns Management
     */
    public function getReturns(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `returns`")->fetchColumn();

        $stmt = $this->db->prepare("SELECT r.*, o.order_number, u.name as customer_name, u.email as customer_email,
            oi.product_title, oi.variant_title, s.store_name as seller_name
            FROM `returns` r
            JOIN `orders` o ON r.order_id = o.id
            JOIN `users` u ON r.user_id = u.id
            JOIN `order_items` oi ON r.order_item_id = oi.id
            JOIN `sellers` s ON r.seller_id = s.id
            ORDER BY r.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "Returns list");
        } else {
            Response::success($items, "Returns list");
        }
    }

    public function updateReturnStatus(array $params): void {
        $admin = $this->authenticateAdmin();
        $returnId = (int)$params['id'];
        $data = $this->getRequestData();

        $status = strtoupper(trim($data['status'] ?? ''));
        $adminNotes = trim($data['admin_notes'] ?? '');
        $allowed = ['REQUESTED', 'APPROVED', 'REJECTED', 'PICKUP_SCHEDULED', 'PICKED_UP', 'REFUND_INITIATED', 'REFUNDED'];

        if (!in_array($status, $allowed)) {
            Response::error("Invalid return status: {$status}", [], 422);
        }

        $stmt = $this->db->prepare("SELECT * FROM `returns` WHERE `id` = ?");
        $stmt->execute([$returnId]);
        $return = $stmt->fetch();

        if (!$return) {
            Response::error("Return request not found", [], 404);
        }

        $this->db->prepare("UPDATE `returns` SET `status` = ?, `admin_notes` = ? WHERE `id` = ?")
            ->execute([$status, $adminNotes, $returnId]);

        if ($status === 'APPROVED' || $status === 'PICKED_UP') {
            $this->db->prepare("UPDATE `order_items` SET `status` = ? WHERE `id` = ?")
                ->execute(["RETURN_{$status}", $return['order_item_id']]);
        }

        Logger::logAdmin($admin['id'], "UPDATE_RETURN_STATUS", "RETURNS", $returnId, $return['status'], ['status' => $status]);
        Response::success(['id' => $returnId, 'status' => $status], "Return status updated to {$status}");
    }

    /**
     * Admin Refunds Management
     */
    public function getRefunds(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $total = (int)$this->db->query("SELECT COUNT(*) FROM `refunds`")->fetchColumn();

        $stmt = $this->db->prepare("SELECT ref.*, o.order_number, u.name as customer_name, p.payment_method
            FROM `refunds` ref
            JOIN `orders` o ON ref.order_id = o.id
            JOIN `users` u ON ref.user_id = u.id
            JOIN `payments` p ON ref.payment_id = p.id
            ORDER BY ref.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        if (isset($params['page'])) {
            Response::paginated($items, $total, $page, $limit, "Refunds list");
        } else {
            Response::success($items, "Refunds list");
        }
    }

    public function createRefund(): void {
        $admin = $this->authenticateAdmin();
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('order_id')
            ->required('amount')
            ->required('reason');

        if ($validator->fails()) {
            Response::error("Validation failed", $validator->errors(), 422);
        }

        $orderId = (int)$data['order_id'];
        $amount = (float)$data['amount'];
        $reason = trim($data['reason']);

        $pStmt = $this->db->prepare("SELECT * FROM `payments` WHERE `order_id` = ? AND `status` = 'CAPTURED' ORDER BY id DESC LIMIT 1");
        $pStmt->execute([$orderId]);
        $payment = $pStmt->fetch();

        if (!$payment) {
            Response::error("No captured payment found for this order to refund", [], 400);
        }

        $orderStmt = $this->db->prepare("SELECT * FROM `orders` WHERE `id` = ?");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        $rzpRefundId = 'rfnd_' . bin2hex(random_bytes(8));

        $this->db->beginTransaction();
        try {
            $refStmt = $this->db->prepare("INSERT INTO `refunds` (`order_id`, `payment_id`, `user_id`, `razorpay_refund_id`, `amount`, `status`, `reason`) VALUES (?, ?, ?, ?, ?, 'PROCESSED', ?)");
            $refStmt->execute([$orderId, $payment['id'], $order['user_id'], $rzpRefundId, $amount, $reason]);
            $refundId = (int)$this->db->lastInsertId();

            $this->db->prepare("UPDATE `payments` SET `status` = 'REFUNDED' WHERE `id` = ?")->execute([$payment['id']]);
            $this->db->prepare("UPDATE `orders` SET `payment_status` = 'REFUNDED' WHERE `id` = ?")->execute([$orderId]);

            $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `updated_by_user_id`) VALUES (?, 'REFUNDED', ?, ?)")
                ->execute([$orderId, "Refund of ₹{$amount} processed. Reason: {$reason}", $admin['id']]);

            $this->db->commit();

            Logger::logAdmin($admin['id'], "CREATE_REFUND", "REFUNDS", $refundId, null, ['amount' => $amount, 'order_id' => $orderId]);
            Response::success(['refund_id' => $refundId, 'amount' => $amount, 'status' => 'PROCESSED'], "Refund processed successfully", 201);
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error("Failed to process refund: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * =========================================================================
     * SUPER ADMIN SETTINGS & .ENV CONFIGURATION
     * =========================================================================
     */

    /**
     * Retrieve complete system settings, active gateways, and .env configuration
     */
    public function getSystemSettings(): void {
        $this->authenticateSuperAdmin();

        $settings = [
            'active_payment_gateway' => PaymentService::getActiveGateway(),
            'payment_gateways'       => PaymentService::getAllGateways(),
            'active_sms_provider'    => SmsService::getActiveProvider(),
            'sms_providers'          => SmsService::getAllProviders(),
            'fcm'                    => NotificationService::getFcmConfig(),
            'app'                    => [
                'app_env'      => Env::get('APP_ENV', 'development'),
                'app_debug'    => (bool)Env::get('APP_DEBUG', true),
                'app_name'     => Env::get('APP_NAME', 'Flipkart Shopping Platform'),
                'app_url'      => Env::get('APP_URL', 'http://localhost/ecommerce_api'),
                'app_currency' => Env::get('APP_CURRENCY', 'INR')
            ],
            'database'               => [
                'host'     => Env::get('DB_HOST', '127.0.0.1'),
                'port'     => Env::get('DB_PORT', '3306'),
                'database' => Env::get('DB_NAME', 'flipkartdb'),
                'username' => Env::get('DB_USER', 'root')
            ],
            'raw_env'                => Env::getRaw()
        ];

        Response::success($settings, "System and environment settings retrieved");
    }

    /**
     * Atomically update system and .env settings
     */
    public function updateSystemSettings(): void {
        $superAdmin = $this->authenticateSuperAdmin();
        $data = $this->getRequestData();

        if (empty($data)) {
            Response::error("No configuration data provided", [], 422);
        }

        $allowedKeys = [
            'ACTIVE_PAYMENT_GATEWAY', 'RAZORPAY_KEY_ID', 'RAZORPAY_KEY_SECRET', 'RAZORPAY_WEBHOOK_SECRET',
            'CASHFREE_APP_ID', 'CASHFREE_SECRET_KEY', 'CASHFREE_ENV',
            'PHONEPE_MERCHANT_ID', 'PHONEPE_SALT_KEY', 'PHONEPE_SALT_INDEX', 'PHONEPE_ENV',
            'PAYTM_MID', 'PAYTM_MERCHANT_KEY', 'PAYTM_WEBSITE', 'PAYTM_ENV',
            'PAYU_MERCHANT_KEY', 'PAYU_SALT', 'PAYU_ENV',
            'ACTIVE_SMS_PROVIDER', 'FAST2SMS_API_KEY', 'FAST2SMS_SENDER_ID',
            'MSG91_AUTH_KEY', 'MSG91_TEMPLATE_ID', 'MSG91_SENDER_ID',
            'TWILIO_ACCOUNT_SID', 'TWILIO_AUTH_TOKEN', 'TWILIO_PHONE_NUMBER',
            'TEXTLOCAL_API_KEY', 'TEXTLOCAL_SENDER',
            'FCM_SERVER_KEY', 'FCM_PROJECT_ID', 'FCM_SENDER_ID',
            'APP_ENV', 'APP_DEBUG', 'APP_NAME', 'APP_URL', 'APP_CURRENCY',
            'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'
        ];

        $updates = [];
        foreach ($data as $k => $v) {
            $upperKey = strtoupper(trim($k));
            if (in_array($upperKey, $allowedKeys, true)) {
                $updates[$upperKey] = $v;
            }
        }

        if (empty($updates)) {
            Response::error("No recognized configuration keys provided", [], 422);
        }

        $success = Env::update($updates);
        if (!$success) {
            Response::error("Failed to write updates to .env file", [], 500);
        }

        Logger::logAdmin($superAdmin['id'], "UPDATE_ENV_SETTINGS", "SYSTEM", 1, null, array_keys($updates));
        Response::success([
            'updated_keys'           => array_keys($updates),
            'active_payment_gateway' => PaymentService::getActiveGateway(),
            'active_sms_provider'    => SmsService::getActiveProvider()
        ], "Environment (.env) and Gateway settings saved successfully! Live configuration active.");
    }

    /**
     * Get raw .env text for direct code editor
     */
    public function getRawEnv(): void {
        $this->authenticateSuperAdmin();
        Response::success(['raw_env' => Env::getRaw()], "Raw .env content");
    }

    /**
     * Save raw .env text directly from editor
     */
    public function saveRawEnv(): void {
        $superAdmin = $this->authenticateSuperAdmin();
        $data = $this->getRequestData();

        if (!isset($data['content'])) {
            Response::error("Missing content parameter", [], 422);
        }

        $content = (string)$data['content'];
        $success = Env::writeRaw($content);

        if (!$success) {
            Response::error("Failed to save .env file", [], 500);
        }

        Logger::logAdmin($superAdmin['id'], "WRITE_RAW_ENV", "SYSTEM", 1, null, ['length' => strlen($content)]);
        Response::success(null, ".env file updated successfully");
    }

    /**
     * Test Payment Gateway Connectivity
     */
    public function testPaymentGateway(): void {
        $this->authenticateSuperAdmin();
        $data = $this->getRequestData();
        $gateway = trim($data['gateway'] ?? PaymentService::getActiveGateway());

        $result = PaymentService::testGateway($gateway);
        if (!$result['success']) {
            Response::error($result['message'], $result, 400);
        }

        Response::success($result, $result['message']);
    }

    /**
     * Test SMS Provider Dispatch
     */
    public function testSmsGateway(): void {
        $this->authenticateSuperAdmin();
        $data = $this->getRequestData();
        $provider = trim($data['provider'] ?? SmsService::getActiveProvider());
        $phone = trim($data['phone'] ?? '9876543210');

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            Response::error("Please provide a valid recipient phone number", [], 422);
        }

        $result = SmsService::testProvider($provider, $phone);
        if (!$result['success']) {
            Response::error($result['message'], $result, 400);
        }

        Response::success($result, $result['message']);
    }

    /**
     * Test Firebase Cloud Messaging Push Notification
     */
    public function testFcmNotification(): void {
        $this->authenticateSuperAdmin();
        $data = $this->getRequestData();
        $title = trim($data['title'] ?? 'Flipkart Super Admin Alert');
        $body = trim($data['body'] ?? 'Live test push notification from Flipkart Control Center');
        $token = trim($data['token'] ?? '');

        $result = NotificationService::testFcm($title, $body, $token ?: null);
        if (!$result['success']) {
            Response::error($result['message'], $result, 400);
        }

        Response::success($result, $result['message']);
    }

    /**
     * =========================================================================
     * WHITE-LABEL BRANDING & PUBLIC SITE CONFIGURATION
     * =========================================================================
     */

    /**
     * Public Branding Endpoint (Web & Mobile App Bootstrapping)
     */
    public function getPublicSettings(): void {
        $stmt = $this->db->query("SELECT `key`, `value` FROM `settings` WHERE `group_name` IN ('BRANDING', 'CONTACT', 'LOCALIZATION', 'GENERAL')");
        $rows = $stmt->fetchAll();
        $settingsMap = [];
        foreach ($rows as $r) {
            $settingsMap[$r['key']] = $r['value'];
        }

        $branding = [
            'site_name'        => $settingsMap['site_name'] ?? Env::get('APP_NAME', 'Flipkart'),
            'site_logo_url'    => $settingsMap['site_logo_url'] ?? '',
            'site_favicon_url' => $settingsMap['site_favicon_url'] ?? 'https://static-assets-web.flixcart.com/batman-returns/batman-returns/p/images/logo_lite-cbb357.png',
            'site_tagline'     => $settingsMap['site_tagline'] ?? 'Explore Plus ✦',
            'primary_color'    => $settingsMap['primary_color'] ?? '#2874F0',
            'secondary_color'  => $settingsMap['secondary_color'] ?? '#FB641B',
            'support_email'    => $settingsMap['support_email'] ?? 'support@flipkart.local',
            'support_phone'    => $settingsMap['support_phone'] ?? '1800 202 9898',
            'currency_symbol'  => $settingsMap['currency_symbol'] ?? '₹',
            'currency_code'    => Env::get('APP_CURRENCY', 'INR'),
            'footer_copyright' => $settingsMap['footer_copyright'] ?? '© 2026 E-Commerce Marketplace. All Rights Reserved.',
            'active_gateway'   => PaymentService::getActiveGateway(),
            'active_sms'       => SmsService::getActiveProvider(),
            'app_url'          => Env::get('APP_URL', 'http://localhost/ecommerce_api')
        ];

        Response::success($branding, "Public site branding settings");
    }

    /**
     * Super Admin Update White-Label Branding
     */
    public function updateBrandingSettings(): void {
        $superAdmin = $this->authenticateSuperAdmin();
        $data = $this->getRequestData();

        $allowedKeys = [
            'site_name', 'site_logo_url', 'site_favicon_url', 'site_tagline',
            'primary_color', 'secondary_color', 'support_email', 'support_phone',
            'currency_symbol', 'footer_copyright'
        ];

        $stmt = $this->db->prepare("INSERT INTO `settings` (`key`, `value`, `group_name`, `description`) 
            VALUES (?, ?, 'BRANDING', 'White-label setting') 
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        $envUpdates = [];
        foreach ($data as $k => $v) {
            $key = strtolower(trim($k));
            if (in_array($key, $allowedKeys, true)) {
                $stmt->execute([$key, trim((string)$v)]);
                if ($key === 'site_name') {
                    $envUpdates['APP_NAME'] = trim((string)$v);
                }
            }
        }

        if (!empty($envUpdates)) {
            Env::update($envUpdates);
        }

        Logger::logAdmin($superAdmin['id'], "UPDATE_BRANDING_SETTINGS", "SYSTEM", 1, null, array_keys($data));
        Response::success(null, "White-label branding updated successfully across Web, Admin, and Mobile apps!");
    }

    /**
     * =========================================================================
     * ADMIN & SUPER ADMIN PRODUCT MANAGEMENT (FULL CRUD)
     * =========================================================================
     */

    /**
     * Get All Products for Admin Moderation
     */
    public function getProducts(): void {
        $this->authenticateAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(100, max(1, (int)($params['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;
        $search = trim($params['search'] ?? '');
        $catId = isset($params['category_id']) ? (int)$params['category_id'] : null;

        $where = ["p.deleted_at IS NULL"];
        $bindings = [];

        if (!empty($search)) {
            $where[] = "(p.title LIKE ? OR b.name LIKE ? OR c.name LIKE ?)";
            $term = "%{$search}%";
            $bindings[] = $term;
            $bindings[] = $term;
            $bindings[] = $term;
        }

        if ($catId) {
            $where[] = "p.category_id = ?";
            $bindings[] = $catId;
        }

        $whereClause = implode(" AND ", $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM `products` p JOIN `brands` b ON p.brand_id = b.id JOIN `categories` c ON p.category_id = c.id WHERE {$whereClause}");
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        $query = "SELECT p.*, b.name as brand_name, c.name as category_name, s.store_name as seller_name,
            (SELECT COALESCE(SUM(quantity), 0) FROM `inventory` inv WHERE inv.product_id = p.id) as total_stock,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            LEFT JOIN `sellers` s ON p.seller_id = s.id
            WHERE {$whereClause}
            ORDER BY p.id DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        $idx = 1;
        foreach ($bindings as $b) {
            $stmt->bindValue($idx++, $b);
        }
        $stmt->bindValue($idx++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        Response::paginated($items, $total, $page, $limit, "Admin products catalog");
    }

    /**
     * Get Single Product By ID
     */
    public function getProductById(array $params): void {
        $this->authenticateAdmin();
        $id = (int)$params['id'];

        $stmt = $this->db->prepare("SELECT p.*, b.name as brand_name, c.name as category_name, s.store_name as seller_name,
            (SELECT COALESCE(SUM(quantity), 0) FROM `inventory` inv WHERE inv.product_id = p.id) as total_stock
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            LEFT JOIN `sellers` s ON p.seller_id = s.id
            WHERE p.id = ? AND p.deleted_at IS NULL");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error("Product not found", [], 404);
        }

        $imgStmt = $this->db->prepare("SELECT * FROM `product_images` WHERE `product_id` = ? ORDER BY sort_order ASC");
        $imgStmt->execute([$id]);
        $product['images'] = $imgStmt->fetchAll();

        $varStmt = $this->db->prepare("SELECT pv.*, inv.quantity as stock FROM `product_variants` pv LEFT JOIN `inventory` inv ON inv.variant_id = pv.id WHERE pv.product_id = ?");
        $varStmt->execute([$id]);
        $product['variants'] = $varStmt->fetchAll();

        Response::success($product, "Product details");
    }

    /**
     * Create New Product by Super Admin / Admin
     */
    public function createProduct(): void {
        $admin = $this->authenticateAdmin();
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('title')
            ->required('category_id');

        if ($validator->fails()) {
            Response::error("Product validation failed", $validator->errors(), 422);
        }

        $title = trim($data['title']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . rand(100, 999);
        
        // Price & MRP resolution with aliases
        $price = isset($data['base_price']) ? (float)$data['base_price'] : (isset($data['price']) ? (float)$data['price'] : 0.0);
        $mrp = isset($data['base_mrp']) ? (float)$data['base_mrp'] : (isset($data['mrp']) ? (float)$data['mrp'] : ($price * 1.3));

        if ($price <= 0) {
            Response::error("Product price must be greater than 0", [], 422);
        }
        if ($mrp < $price) {
            $mrp = $price * 1.2;
        }

        // Brand resolution: by brand_id or brand_name or auto-creation
        $brandId = !empty($data['brand_id']) ? (int)$data['brand_id'] : null;
        if (!$brandId && !empty($data['brand_name'])) {
            $brandName = trim($data['brand_name']);
            $bStmt = $this->db->prepare("SELECT id FROM `brands` WHERE `name` = ? LIMIT 1");
            $bStmt->execute([$brandName]);
            $foundBrand = $bStmt->fetch();
            if ($foundBrand) {
                $brandId = (int)$foundBrand['id'];
            } else {
                $bSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $brandName))) . '-' . rand(10, 99);
                $this->db->prepare("INSERT INTO `brands` (`name`, `slug`, `is_active`) VALUES (?, ?, 1)")->execute([$brandName, $bSlug]);
                $brandId = (int)$this->db->lastInsertId();
            }
        }
        if (!$brandId) {
            $brandId = 1; // Default brand fallback
        }

        $catId = (int)$data['category_id'];
        $sellerId = !empty($data['seller_id']) ? (int)$data['seller_id'] : 1; // Default Super Admin seller
        $description = !empty($data['description']) ? $data['description'] : "Premium authentic {$title}. 100% genuine quality assured.";
        $highlights = !empty($data['highlights']) ? (is_array($data['highlights']) ? $data['highlights'] : explode("\n", $data['highlights'])) : ['100% Genuine Quality Assured', '7-Day Replacement Guarantee', 'Fast Delivery Across India'];
        $specs = !empty($data['specifications']) ? (is_array($data['specifications']) ? $data['specifications'] : ['Quality' => 'Grade A', 'Origin' => 'India']) : ['Standard' => 'Verified Authentic'];
        $stock = isset($data['stock']) ? max(0, (int)$data['stock']) : (isset($data['initial_stock']) ? max(0, (int)$data['initial_stock']) : 50);

        // Images resolution
        $images = [];
        if (!empty($data['image_url'])) {
            $images[] = trim($data['image_url']);
        }
        if (!empty($data['images'])) {
            if (is_array($data['images'])) {
                foreach ($data['images'] as $img) {
                    if (!empty(trim($img))) $images[] = trim($img);
                }
            } else {
                $images[] = trim($data['images']);
            }
        }
        if (empty($images)) {
            $images[] = 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800';
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO `products` 
                (`title`, `slug`, `brand_id`, `category_id`, `seller_id`, `description`, `highlights`, `specifications`, `base_mrp`, `base_price`, `status`, `is_active`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED', 1)");

            $stmt->execute([
                $title,
                $slug,
                $brandId,
                $catId,
                $sellerId,
                $description,
                json_encode($highlights),
                json_encode($specs),
                $mrp,
                $price
            ]);
            $productId = (int)$this->db->lastInsertId();

            // Insert Primary Variant
            $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $title), 0, 4)) . '-' . $productId;
            $vStmt = $this->db->prepare("INSERT INTO `product_variants` (`product_id`, `sku`, `title`, `price`, `mrp`, `stock`, `is_active`) VALUES (?, ?, 'Standard', ?, ?, ?, 1)");
            $vStmt->execute([$productId, $sku, $price, $mrp, $stock]);
            $variantId = (int)$this->db->lastInsertId();

            // Insert Inventory
            $invStmt = $this->db->prepare("INSERT INTO `inventory` (`product_id`, `variant_id`, `seller_id`, `quantity`, `reserved_quantity`, `low_stock_threshold`) VALUES (?, ?, ?, ?, 0, 5)");
            $invStmt->execute([$productId, $variantId, $sellerId, $stock]);
            $inventoryId = (int)$this->db->lastInsertId();

            // Log Inventory Transaction
            $this->db->prepare("INSERT INTO `inventory_transactions` (`inventory_id`, `type`, `quantity_change`, `previous_quantity`, `new_quantity`, `notes`) VALUES (?, 'INITIAL_STOCK', ?, 0, ?, 'Product created by Admin')")
                ->execute([$inventoryId, $stock, $stock]);

            // Insert Product Images
            $imgStmt = $this->db->prepare("INSERT INTO `product_images` (`product_id`, `image_url`, `is_primary`, `sort_order`) VALUES (?, ?, ?, ?)");
            $sort = 0;
            foreach ($images as $img) {
                $imgUrl = trim($img);
                if (!empty($imgUrl)) {
                    $imgStmt->execute([$productId, $imgUrl, ($sort === 0 ? 1 : 0), $sort]);
                    $sort++;
                }
            }

            $this->db->commit();
            Logger::logAdmin($admin['id'], "CREATE_PRODUCT", "PRODUCTS", $productId, null, ['title' => $title, 'price' => $price]);

            Response::success([
                'id'       => $productId,
                'title'    => $title,
                'slug'     => $slug,
                'price'    => $price,
                'mrp'      => $mrp,
                'stock'    => $stock
            ], "Product '{$title}' created successfully and published to live store!", 201);

        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error("Failed to create product: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update Product Details & Inventory
     */
    public function updateProduct(array $params): void {
        $admin = $this->authenticateAdmin();
        $id = (int)$params['id'];
        $data = $this->getRequestData();

        $stmt = $this->db->prepare("SELECT * FROM `products` WHERE `id` = ? AND `deleted_at` IS NULL");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error("Product not found", [], 404);
        }

        $title = isset($data['title']) ? trim($data['title']) : $product['title'];
        $price = isset($data['base_price']) ? (float)$data['base_price'] : (float)$product['base_price'];
        $mrp = isset($data['base_mrp']) ? (float)$data['base_mrp'] : (float)$product['base_mrp'];
        $catId = isset($data['category_id']) ? (int)$data['category_id'] : (int)$product['category_id'];
        $brandId = isset($data['brand_id']) ? (int)$data['brand_id'] : (int)$product['brand_id'];
        $status = isset($data['status']) ? strtoupper(trim($data['status'])) : $product['status'];
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : (int)$product['is_active'];

        $this->db->beginTransaction();
        try {
            $uStmt = $this->db->prepare("UPDATE `products` SET `title` = ?, `base_price` = ?, `base_mrp` = ?, `category_id` = ?, `brand_id` = ?, `status` = ?, `is_active` = ? WHERE `id` = ?");
            $uStmt->execute([$title, $price, $mrp, $catId, $brandId, $status, $isActive, $id]);

            // Update Primary Variant Price
            $this->db->prepare("UPDATE `product_variants` SET `price` = ?, `mrp` = ? WHERE `product_id` = ?")->execute([$price, $mrp, $id]);

            // Update Stock if supplied
            if (isset($data['stock'])) {
                $newStock = max(0, (int)$data['stock']);
                $this->db->prepare("UPDATE `inventory` SET `quantity` = ? WHERE `product_id` = ?")->execute([$newStock, $id]);
                $this->db->prepare("UPDATE `product_variants` SET `stock` = ? WHERE `product_id` = ?")->execute([$newStock, $id]);
            }

            // Update Primary Image if supplied
            if (!empty($data['image_url'])) {
                $this->db->prepare("UPDATE `product_images` SET `image_url` = ? WHERE `product_id` = ? AND `is_primary` = 1")
                    ->execute([trim($data['image_url']), $id]);
            }

            $this->db->commit();
            Logger::logAdmin($admin['id'], "UPDATE_PRODUCT", "PRODUCTS", $id, null, ['title' => $title, 'price' => $price]);
            Response::success(null, "Product #{$id} updated successfully!");

        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error("Failed to update product: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Delete Product (Soft Delete)
     */
    public function deleteProduct(array $params): void {
        $admin = $this->authenticateAdmin();
        $id = (int)$params['id'];

        $this->db->prepare("UPDATE `products` SET `deleted_at` = NOW(), `is_active` = 0 WHERE `id` = ?")->execute([$id]);
        Logger::logAdmin($admin['id'], "DELETE_PRODUCT", "PRODUCTS", $id, null, []);
        Response::success(null, "Product #{$id} removed from catalog");
    }

    /**
     * Update Product Moderation Status
     */
    public function updateProductStatus(array $params): void {
        $admin = $this->authenticateAdmin();
        $id = (int)$params['id'];
        $data = $this->getRequestData();

        $status = strtoupper(trim($data['status'] ?? 'APPROVED'));
        $isActive = ($status === 'APPROVED') ? 1 : 0;

        $this->db->prepare("UPDATE `products` SET `status` = ?, `is_active` = ? WHERE `id` = ?")->execute([$status, $isActive, $id]);
        Logger::logAdmin($admin['id'], "UPDATE_PRODUCT_STATUS", "PRODUCTS", $id, null, ['status' => $status]);
        Response::success(null, "Product #{$id} status updated to {$status}");
    }
}


