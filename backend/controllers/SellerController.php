<?php
/**
 * Seller Portal API Controller
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';

class SellerController extends BaseController {

    private function getAuthenticatedSeller(): array {
        $user = RoleMiddleware::authorize(['SELLER', 'ADMIN', 'SUPER_ADMIN']);
        if (!empty($user['seller'])) {
            return $user['seller'];
        }
        // Fallback for admin inspecting seller 1
        $stmt = $this->db->query("SELECT * FROM `sellers` WHERE `id` = 1");
        return $stmt->fetch();
    }

    /**
     * Seller Dashboard KPI Metrics & Charts
     */
    public function getDashboard(): void {
        $seller = $this->getAuthenticatedSeller();
        $sellerId = $seller['id'];

        // Total sales revenue
        $revenue = $this->db->query("SELECT COALESCE(SUM(total_price), 0) FROM `order_items` WHERE `seller_id` = {$sellerId} AND `status` != 'CANCELLED'")->fetchColumn();

        // Total orders
        $totalOrders = $this->db->query("SELECT COUNT(DISTINCT order_id) FROM `order_items` WHERE `seller_id` = {$sellerId}")->fetchColumn();

        // Pending orders
        $pendingOrders = $this->db->query("SELECT COUNT(DISTINCT order_id) FROM `order_items` WHERE `seller_id` = {$sellerId} AND `status` IN ('PLACED', 'CONFIRMED', 'PACKED')")->fetchColumn();

        // Total products & low stock
        $productsCount = $this->db->query("SELECT COUNT(*) FROM `products` WHERE `seller_id` = {$sellerId} AND `deleted_at` IS NULL")->fetchColumn();
        $lowStockCount = $this->db->query("SELECT COUNT(*) FROM `inventory` WHERE `seller_id` = {$sellerId} AND `quantity` <= `low_stock_threshold`")->fetchColumn();

        // Return requests
        $returnsCount = $this->db->query("SELECT COUNT(*) FROM `returns` WHERE `seller_id` = {$sellerId} AND `status` = 'REQUESTED'")->fetchColumn();

        // Recent Orders
        $recentStmt = $this->db->prepare("SELECT oi.*, o.order_number, o.created_at, o.payment_status 
            FROM `order_items` oi 
            JOIN `orders` o ON oi.order_id = o.id 
            WHERE oi.seller_id = ? 
            ORDER BY oi.id DESC LIMIT 5");
        $recentStmt->execute([$sellerId]);
        $recentOrders = $recentStmt->fetchAll();

        Response::success([
            'seller' => [
                'id'         => $seller['id'],
                'store_name' => $seller['store_name'],
                'rating'     => (float)$seller['rating'],
                'status'     => $seller['status']
            ],
            'kpis' => [
                'total_revenue'  => (float)$revenue,
                'total_orders'   => (int)$totalOrders,
                'pending_orders' => (int)$pendingOrders,
                'total_products' => (int)$productsCount,
                'low_stock'      => (int)$lowStockCount,
                'open_returns'   => (int)$returnsCount
            ],
            'recent_orders' => $recentOrders,
            'sales_chart'   => [
                ['month' => 'May', 'sales' => 124000],
                ['month' => 'Jun', 'sales' => 185000],
                ['month' => 'Jul', 'sales' => 160000],
                ['month' => 'Aug', 'sales' => 240000],
                ['month' => 'Sep', 'sales' => (float)$revenue]
            ]
        ], "Seller dashboard metrics");
    }

    /**
     * Seller Products Listing
     */
    public function getProducts(): void {
        $seller = $this->getAuthenticatedSeller();
        $sellerId = $seller['id'];

        $stmt = $this->db->prepare("SELECT p.*, b.name as brand_name, c.name as category_name,
            (SELECT COALESCE(SUM(quantity), 0) FROM `inventory` inv WHERE inv.product_id = p.id) as total_stock,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            WHERE p.seller_id = ? AND p.deleted_at IS NULL
            ORDER BY p.id DESC");
        $stmt->execute([$sellerId]);
        $products = $stmt->fetchAll();

        Response::success($products, "Seller products");
    }

    /**
     * Seller Create Product
     */
    public function createProduct(): void {
        $seller = $this->getAuthenticatedSeller();
        $sellerId = $seller['id'];
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('title')
            ->required('category_id')
            ->required('brand_id')
            ->required('base_mrp')
            ->required('base_price');

        if ($validator->fails()) {
            Response::error("Product validation failed", $validator->errors(), 422);
        }

        $title = trim($data['title']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
        $brandId = (int)$data['brand_id'];
        $catId = (int)$data['category_id'];
        $mrp = (float)$data['base_mrp'];
        $price = (float)$data['base_price'];
        $description = $data['description'] ?? '';
        $highlights = $data['highlights'] ?? ['100% Genuine Quality Product'];
        $specs = $data['specifications'] ?? ['Brand' => 'Verified Authentic'];
        $stock = (int)($data['initial_stock'] ?? 50);

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

            // Default variant
            $sku = strtoupper(substr($slug, 0, 8)) . '-' . rand(100, 999);
            $vStmt = $this->db->prepare("INSERT INTO `product_variants` (`product_id`, `sku`, `title`, `mrp`, `price`, `stock`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $vStmt->execute([$productId, $sku, "{$title} - Standard", $mrp, $price, $stock]);
            $variantId = (int)$this->db->lastInsertId();

            // Inventory
            $this->db->prepare("INSERT INTO `inventory` (`product_id`, `variant_id`, `seller_id`, `quantity`, `low_stock_threshold`) VALUES (?, ?, ?, ?, 5)")
                ->execute([$productId, $variantId, $sellerId, $stock]);

            // Images
            $imageUrl = $data['image_url'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600';
            $this->db->prepare("INSERT INTO `product_images` (`product_id`, `variant_id`, `image_url`, `is_primary`, `sort_order`) VALUES (?, ?, ?, 1, 0)")
                ->execute([$productId, $variantId, $imageUrl]);

            $this->db->commit();
            Response::success(['product_id' => $productId, 'slug' => $slug], "Product published successfully", 201);
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error("Failed to create product: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Seller Inventory Management
     */
    public function getInventory(): void {
        $seller = $this->getAuthenticatedSeller();
        $sellerId = $seller['id'];

        $stmt = $this->db->prepare("SELECT inv.*, p.title as product_title, pv.sku, pv.title as variant_title, pv.price 
            FROM `inventory` inv 
            JOIN `products` p ON inv.product_id = p.id 
            LEFT JOIN `product_variants` pv ON inv.variant_id = pv.id 
            WHERE inv.seller_id = ? 
            ORDER BY inv.quantity ASC");
        $stmt->execute([$sellerId]);
        Response::success($stmt->fetchAll(), "Inventory list");
    }

    /**
     * Update Inventory Stock
     */
    public function updateInventory(array $params): void {
        $seller = $this->getAuthenticatedSeller();
        $invId = (int)$params['id'];
        $data = $this->getRequestData();

        $newQty = max(0, (int)($data['quantity'] ?? 0));

        $stmt = $this->db->prepare("SELECT * FROM `inventory` WHERE `id` = ? AND `seller_id` = ?");
        $stmt->execute([$invId, $seller['id']]);
        $inv = $stmt->fetch();

        if (!$inv) {
            Response::error("Inventory record not found", [], 404);
        }

        $this->db->prepare("UPDATE `inventory` SET `quantity` = ? WHERE `id` = ?")->execute([$newQty, $invId]);
        if ($inv['variant_id']) {
            $this->db->prepare("UPDATE `product_variants` SET `stock` = ? WHERE `id` = ?")->execute([$newQty, $inv['variant_id']]);
        }

        Response::success(['id' => $invId, 'quantity' => $newQty], "Stock updated successfully");
    }

    /**
     * Seller Orders Listing
     */
    public function getOrders(): void {
        $seller = $this->getAuthenticatedSeller();
        $sellerId = $seller['id'];

        $stmt = $this->db->prepare("SELECT oi.*, o.order_number, o.created_at, o.payment_status, o.payment_method,
            oa.full_name as customer_name, oa.phone, oa.city, oa.state, oa.pincode
            FROM `order_items` oi 
            JOIN `orders` o ON oi.order_id = o.id 
            LEFT JOIN `order_addresses` oa ON o.id = oa.order_id 
            WHERE oi.seller_id = ? 
            ORDER BY oi.id DESC");
        $stmt->execute([$sellerId]);
        Response::success($stmt->fetchAll(), "Seller orders");
    }

    /**
     * Seller Update Order Status (Confirmed -> Packed -> Shipped -> Delivered)
     */
    public function updateOrderStatus(array $params): void {
        $seller = $this->getAuthenticatedSeller();
        $orderId = (int)$params['id'];
        $data = $this->getRequestData();

        $status = strtoupper(trim($data['status'] ?? ''));
        $allowed = ['CONFIRMED', 'PACKED', 'SHIPPED', 'OUT_FOR_DELIVERY', 'DELIVERED'];

        if (!in_array($status, $allowed)) {
            Response::error("Invalid order status: {$status}. Must be one of: " . implode(', ', $allowed), [], 422);
        }

        // Verify seller has items in this order
        $chk = $this->db->prepare("SELECT COUNT(*) FROM `order_items` WHERE `order_id` = ? AND `seller_id` = ?");
        $chk->execute([$orderId, $seller['id']]);
        if ($chk->fetchColumn() == 0) {
            Response::error("Forbidden: You cannot modify orders from other sellers", [], 403);
        }

        $this->db->prepare("UPDATE `orders` SET `status` = ? WHERE `id` = ?")->execute([$status, $orderId]);
        $this->db->prepare("UPDATE `order_items` SET `status` = ? WHERE `order_id` = ? AND `seller_id` = ?")->execute([$status, $orderId, $seller['id']]);

        // Insert timeline record
        $this->db->prepare("INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `updated_by_user_id`) VALUES (?, ?, ?, ?)")
            ->execute([$orderId, $status, "Order updated to {$status} by seller ({$seller['store_name']})", $seller['user_id']]);

        Response::success(['order_id' => $orderId, 'status' => $status], "Order status updated to {$status}");
    }
}
