<?php
/**
 * Cart, Wishlist and Saved for Later Controller
 * Server-synchronized cart with stock validation and live price calculation
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';

class CartController extends BaseController {

    private function getCartId(array $user): int {
        $userId = $user['id'];
        $stmt = $this->db->prepare("SELECT id FROM `carts` WHERE `user_id` = ?");
        $stmt->execute([$userId]);
        $cartId = $stmt->fetchColumn();

        if (!$cartId) {
            $this->db->prepare("INSERT INTO `carts` (`user_id`) VALUES (?)")->execute([$userId]);
            $cartId = (int)$this->db->lastInsertId();
        }

        return (int)$cartId;
    }

    /**
     * Get Server-Synchronized Cart
     */
    public function getCart(): void {
        $user = AuthMiddleware::authenticate(true);
        $cartId = $this->getCartId($user);

        $sql = "SELECT ci.id as item_id, ci.quantity, ci.product_id, ci.variant_id, ci.seller_id,
            p.title as product_title, p.slug as product_slug,
            pv.title as variant_title, pv.color, pv.sku,
            COALESCE(pv.price, p.base_price) as unit_price,
            COALESCE(pv.mrp, p.base_mrp) as unit_mrp,
            COALESCE(pv.stock, 50) as available_stock,
            s.store_name as seller_name,
            COALESCE(pv.image_url, (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1)) as image_url
            FROM `cart_items` ci
            JOIN `products` p ON ci.product_id = p.id
            LEFT JOIN `product_variants` pv ON ci.variant_id = pv.id
            JOIN `sellers` s ON ci.seller_id = s.id
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cartId]);
        $items = $stmt->fetchAll();

        // Calculate Totals
        $totalMrp = 0.00;
        $totalSelling = 0.00;
        $itemCount = 0;
        $outOfStock = false;

        foreach ($items as &$item) {
            $item['quantity'] = (int)$item['quantity'];
            $item['unit_price'] = (float)$item['unit_price'];
            $item['unit_mrp'] = (float)$item['unit_mrp'];
            $item['available_stock'] = (int)$item['available_stock'];
            $item['total_price'] = round($item['unit_price'] * $item['quantity'], 2);
            $item['total_mrp'] = round($item['unit_mrp'] * $item['quantity'], 2);
            $item['is_in_stock'] = $item['available_stock'] >= $item['quantity'];

            if (!$item['is_in_stock']) {
                $outOfStock = true;
            }

            $totalMrp += $item['total_mrp'];
            $totalSelling += $item['total_price'];
            $itemCount += $item['quantity'];
        }

        $totalDiscount = max(0.00, $totalMrp - $totalSelling);
        // Free delivery above ₹499, else ₹40
        $deliveryCharge = ($totalSelling >= 499.00 || $itemCount === 0) ? 0.00 : 40.00;
        $tax = round($totalSelling * 0.00, 2); // Included in price in India MRP
        $totalPayable = round($totalSelling + $deliveryCharge, 2);

        Response::success([
            'items'        => $items,
            'summary'      => [
                'total_items'     => $itemCount,
                'total_mrp'       => round($totalMrp, 2),
                'total_discount'  => round($totalDiscount, 2),
                'delivery_charge' => round($deliveryCharge, 2),
                'subtotal'        => round($totalSelling, 2),
                'total_payable'   => round($totalPayable, 2),
                'savings'         => round($totalDiscount + ($deliveryCharge === 0.00 && $totalSelling > 0 ? 40.00 : 0.00), 2),
                'has_out_of_stock'=> $outOfStock
            ]
        ], "Cart fetched successfully");
    }

    /**
     * Add Item to Cart
     */
    public function addItem(): void {
        $user = AuthMiddleware::authenticate(true);
        $cartId = $this->getCartId($user);
        $data = $this->getRequestData();

        $validator = Validator::make($data)->required('product_id');
        if ($validator->fails()) {
            Response::error("Product ID is required", $validator->errors(), 422);
        }

        $productId = (int)$data['product_id'];
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $qty = max(1, (int)($data['quantity'] ?? 1));

        // Validate product exists
        $pStmt = $this->db->prepare("SELECT id, seller_id, is_active FROM `products` WHERE `id` = ? AND `deleted_at` IS NULL");
        $pStmt->execute([$productId]);
        $prod = $pStmt->fetch();

        if (!$prod || !$prod['is_active']) {
            Response::error("Product is not available for purchase", [], 404);
        }

        $sellerId = (int)$prod['seller_id'];

        // Validate stock
        if ($variantId) {
            $vStmt = $this->db->prepare("SELECT stock FROM `product_variants` WHERE `id` = ? AND `product_id` = ?");
            $vStmt->execute([$variantId, $productId]);
            $stock = (int)$vStmt->fetchColumn();
        } else {
            // First variant stock or 100
            $stock = 100;
        }

        if ($stock < $qty) {
            Response::error("Requested quantity exceeds available stock (Current Stock: {$stock})", [], 400);
        }

        // Check if item already exists in cart
        if ($variantId) {
            $check = $this->db->prepare("SELECT id, quantity FROM `cart_items` WHERE `cart_id` = ? AND `product_id` = ? AND `variant_id` = ?");
            $check->execute([$cartId, $productId, $variantId]);
        } else {
            $check = $this->db->prepare("SELECT id, quantity FROM `cart_items` WHERE `cart_id` = ? AND `product_id` = ? AND `variant_id` IS NULL");
            $check->execute([$cartId, $productId]);
        }
        $existing = $check->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            if ($newQty > $stock) {
                $newQty = $stock;
            }
            $upd = $this->db->prepare("UPDATE `cart_items` SET `quantity` = ? WHERE `id` = ?");
            $upd->execute([$newQty, $existing['id']]);
        } else {
            $ins = $this->db->prepare("INSERT INTO `cart_items` (`cart_id`, `product_id`, `variant_id`, `seller_id`, `quantity`) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$cartId, $productId, $variantId, $sellerId, $qty]);
        }

        $this->getCart();
    }

    /**
     * Update Item Quantity in Cart
     */
    public function updateItem(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $cartId = $this->getCartId($user);
        $itemId = (int)$params['id'];
        $data = $this->getRequestData();

        $qty = (int)($data['quantity'] ?? 1);

        if ($qty <= 0) {
            $this->deleteItem($params);
            return;
        }

        // Check stock
        $stmt = $this->db->prepare("SELECT ci.*, COALESCE(pv.stock, 50) as available_stock 
            FROM `cart_items` ci 
            LEFT JOIN `product_variants` pv ON ci.variant_id = pv.id 
            WHERE ci.id = ? AND ci.cart_id = ?");
        $stmt->execute([$itemId, $cartId]);
        $item = $stmt->fetch();

        if (!$item) {
            Response::error("Cart item not found", [], 404);
        }

        if ($qty > $item['available_stock']) {
            Response::error("Only {$item['available_stock']} units available in stock", [], 400);
        }

        $this->db->prepare("UPDATE `cart_items` SET `quantity` = ? WHERE `id` = ?")->execute([$qty, $itemId]);
        $this->getCart();
    }

    /**
     * Delete Item from Cart
     */
    public function deleteItem(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $cartId = $this->getCartId($user);
        $itemId = (int)$params['id'];

        $this->db->prepare("DELETE FROM `cart_items` WHERE `id` = ? AND `cart_id` = ?")->execute([$itemId, $cartId]);
        $this->getCart();
    }

    /**
     * Move Cart Item to Saved For Later
     */
    public function saveForLater(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $cartId = $this->getCartId($user);
        $itemId = (int)$params['id'];

        $stmt = $this->db->prepare("SELECT * FROM `cart_items` WHERE `id` = ? AND `cart_id` = ?");
        $stmt->execute([$itemId, $cartId]);
        $item = $stmt->fetch();

        if (!$item) {
            Response::error("Cart item not found", [], 404);
        }

        // Insert into saved_for_later
        $this->db->prepare("INSERT INTO `saved_for_later` (`user_id`, `product_id`, `variant_id`, `seller_id`, `quantity`) 
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`)")
            ->execute([$user['id'], $item['product_id'], $item['variant_id'], $item['seller_id'], $item['quantity']]);

        // Remove from cart
        $this->db->prepare("DELETE FROM `cart_items` WHERE `id` = ?")->execute([$itemId]);

        $this->getCart();
    }

    /**
     * Get Wishlist
     */
    public function getWishlist(): void {
        $user = AuthMiddleware::authenticate(true);
        $userId = $user['id'];

        $stmt = $this->db->prepare("SELECT wi.id as wishlist_item_id, wi.product_id, wi.variant_id,
            p.title, p.slug, p.base_price, p.base_mrp, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `wishlist_items` wi
            JOIN `wishlists` w ON wi.wishlist_id = w.id
            JOIN `products` p ON wi.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY wi.id DESC");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();

        Response::success($items, "Wishlist items fetched");
    }

    /**
     * Add to Wishlist
     */
    public function addToWishlist(): void {
        $user = AuthMiddleware::authenticate(true);
        $userId = $user['id'];
        $data = $this->getRequestData();

        $validator = Validator::make($data)->required('product_id');
        if ($validator->fails()) {
            Response::error("Product ID is required", $validator->errors(), 422);
        }

        $productId = (int)$data['product_id'];
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;

        // Get wishlist ID
        $wStmt = $this->db->prepare("SELECT id FROM `wishlists` WHERE `user_id` = ?");
        $wStmt->execute([$userId]);
        $wishlistId = $wStmt->fetchColumn();

        if (!$wishlistId) {
            $this->db->prepare("INSERT INTO `wishlists` (`user_id`) VALUES (?)")->execute([$userId]);
            $wishlistId = (int)$this->db->lastInsertId();
        }

        $this->db->prepare("INSERT INTO `wishlist_items` (`wishlist_id`, `product_id`, `variant_id`) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE `product_id` = VALUES(`product_id`)")
            ->execute([$wishlistId, $productId, $variantId]);

        Response::success(['in_wishlist' => true], "Product added to wishlist");
    }

    /**
     * Remove from Wishlist
     */
    public function removeFromWishlist(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $userId = $user['id'];
        $id = (int)$params['id']; // Can be wishlist_item_id or product_id

        $this->db->prepare("DELETE wi FROM `wishlist_items` wi
            JOIN `wishlists` w ON wi.wishlist_id = w.id
            WHERE w.user_id = ? AND (wi.id = ? OR wi.product_id = ?)")
            ->execute([$userId, $id, $id]);

        Response::success(['in_wishlist' => false], "Removed from wishlist");
    }
}
