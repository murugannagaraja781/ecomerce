<?php
/**
 * Admin Control Center API Controller
 * Full administrative oversight: Users, Sellers, Catalog, Orders, Payments, Coupons, Reports & Logs
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class AdminController extends BaseController {

    private function authenticateAdmin(): array {
        return RoleMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'SUPPORT_AGENT']);
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
        $stmt = $this->db->query("SELECT u.id, u.name, u.email, u.phone, u.role_id, r.name as role_name, u.is_active, u.is_verified, u.created_at 
            FROM `users` u 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE u.deleted_at IS NULL 
            ORDER BY u.id DESC");
        Response::success($stmt->fetchAll(), "Users list");
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
        $stmt = $this->db->query("SELECT s.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone,
            (SELECT COUNT(*) FROM `products` p WHERE p.seller_id = s.id AND p.deleted_at IS NULL) as total_products,
            (SELECT COALESCE(SUM(total_price), 0) FROM `order_items` oi WHERE oi.seller_id = s.id) as total_sales
            FROM `sellers` s 
            JOIN `users` u ON s.user_id = u.id 
            WHERE s.deleted_at IS NULL 
            ORDER BY s.id DESC");
        Response::success($stmt->fetchAll(), "Sellers list");
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
        $stmt = $this->db->query("SELECT o.*, u.name as customer_name, u.email as customer_email,
            oa.city, oa.state, oa.pincode,
            (SELECT COUNT(*) FROM `order_items` oi WHERE oi.order_id = o.id) as item_count
            FROM `orders` o 
            JOIN `users` u ON o.user_id = u.id 
            LEFT JOIN `order_addresses` oa ON o.id = oa.order_id 
            ORDER BY o.id DESC");
        Response::success($stmt->fetchAll(), "All orders list");
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
        $stmt = $this->db->query("SELECT p.*, o.order_number, u.name as customer_name, u.email as customer_email 
            FROM `payments` p 
            JOIN `orders` o ON p.order_id = o.id 
            JOIN `users` u ON p.user_id = u.id 
            ORDER BY p.id DESC");
        Response::success($stmt->fetchAll(), "Payments list");
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
}
