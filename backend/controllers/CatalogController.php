<?php
/**
 * Catalog Controller
 * Handles Homepage feeds, Categories, Brands, Products, Variants and Details
 */

require_once __DIR__ . '/BaseController.php';

class CatalogController extends BaseController {

    /**
     * Homepage Feed: Banners, Categories, Flash Deals, Best Sellers, Trending
     */
    public function getHomeFeed(): void {
        // 1. Hero Banners
        $bannerStmt = $this->db->query("SELECT * FROM `banners` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
        $banners = $bannerStmt->fetchAll();

        // 2. Top Navigation Categories (Parents)
        $catStmt = $this->db->query("SELECT id, name, slug, image_url, description FROM `categories` WHERE `parent_id` IS NULL AND `is_active` = 1 ORDER BY `sort_order` ASC LIMIT 12");
        $categories = $catStmt->fetchAll();

        // 3. Featured / Flash Deals (Discount >= 40%)
        $dealsStmt = $this->db->query("SELECT p.id, p.title, p.slug, p.base_mrp, p.base_price, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name, c.name as category_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.status = 'APPROVED'
            ORDER BY discount_percentage DESC LIMIT 8");
        $flashDeals = $dealsStmt->fetchAll();

        // 4. Best Sellers (Top ratings and review count)
        $bestSellersStmt = $this->db->query("SELECT p.id, p.title, p.slug, p.base_mrp, p.base_price, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            WHERE p.is_active = 1 AND p.status = 'APPROVED'
            ORDER BY p.review_count DESC LIMIT 8");
        $bestSellers = $bestSellersStmt->fetchAll();

        // 5. Trending Mobiles & Accessories (Includes Celvas iPhone 15 Case)
        $trendingStmt = $this->db->query("SELECT p.id, p.title, p.slug, p.base_mrp, p.base_price, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            WHERE p.is_active = 1 AND p.status = 'APPROVED' AND (p.category_id = 15 OR p.category_id = 14 OR p.category_id = 13)
            ORDER BY p.id ASC LIMIT 8");
        $trendingElectronics = $trendingStmt->fetchAll();

        Response::success([
            'banners'              => $banners,
            'categories'           => $categories,
            'flash_deals'          => $flashDeals,
            'best_sellers'         => $bestSellers,
            'trending_electronics' => $trendingElectronics
        ], "Home feed fetched successfully");
    }

    /**
     * Categories Tree (Parents with their Subcategories)
     */
    public function getCategories(): void {
        $parents = $this->db->query("SELECT * FROM `categories` WHERE `parent_id` IS NULL AND `is_active` = 1 ORDER BY `sort_order` ASC")->fetchAll();
        $subcats = $this->db->query("SELECT * FROM `categories` WHERE `parent_id` IS NOT NULL AND `is_active` = 1 ORDER BY `sort_order` ASC")->fetchAll();

        $subcatMap = [];
        foreach ($subcats as $sub) {
            $subcatMap[$sub['parent_id']][] = $sub;
        }

        foreach ($parents as &$p) {
            $p['subcategories'] = $subcatMap[$p['id']] ?? [];
        }

        Response::success($parents, "Categories retrieved");
    }

    /**
     * Single Category Detail with Child Subcategories
     */
    public function getCategoryById(array $params): void {
        $idOrSlug = $params['id'];
        $stmt = $this->db->prepare("SELECT * FROM `categories` WHERE (`id` = ? OR `slug` = ?) AND `is_active` = 1");
        $stmt->execute([$idOrSlug, $idOrSlug]);
        $category = $stmt->fetch();

        if (!$category) {
            Response::error("Category not found", [], 404);
        }

        // Subcategories
        $subStmt = $this->db->prepare("SELECT * FROM `categories` WHERE `parent_id` = ? AND `is_active` = 1 ORDER BY `sort_order` ASC");
        $subStmt->execute([$category['id']]);
        $category['subcategories'] = $subStmt->fetchAll();

        Response::success($category, "Category details");
    }

    /**
     * Brands List
     */
    public function getBrands(): void {
        $brands = $this->db->query("SELECT * FROM `brands` WHERE `is_active` = 1 ORDER BY `name` ASC")->fetchAll();
        Response::success($brands, "Brands retrieved");
    }

    /**
     * Products Catalog Listing with Multi-Facet Filtering & Sorting
     */
    public function getProducts(): void {
        $queryParams = $this->getQueryParams();

        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = min(50, max(1, (int)($queryParams['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = ["p.is_active = 1", "p.status = 'APPROVED'", "p.deleted_at IS NULL"];
        $bindings = [];

        // Filter by Category (and all its child categories if parent)
        if (!empty($queryParams['category_id'])) {
            $catId = (int)$queryParams['category_id'];
            $childCatIds = $this->db->query("SELECT id FROM `categories` WHERE `parent_id` = {$catId}")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($childCatIds)) {
                $childCatIds[] = $catId;
                $inClause = implode(',', $childCatIds);
                $where[] = "p.category_id IN ({$inClause})";
            } else {
                $where[] = "p.category_id = ?";
                $bindings[] = $catId;
            }
        }

        // Filter by Brand
        if (!empty($queryParams['brand_id'])) {
            $where[] = "p.brand_id = ?";
            $bindings[] = (int)$queryParams['brand_id'];
        }

        // Price Range
        if (!empty($queryParams['min_price'])) {
            $where[] = "p.base_price >= ?";
            $bindings[] = (float)$queryParams['min_price'];
        }
        if (!empty($queryParams['max_price'])) {
            $where[] = "p.base_price <= ?";
            $bindings[] = (float)$queryParams['max_price'];
        }

        // Rating
        if (!empty($queryParams['min_rating'])) {
            $where[] = "p.rating >= ?";
            $bindings[] = (float)$queryParams['min_rating'];
        }

        // Min Discount %
        if (!empty($queryParams['min_discount'])) {
            $where[] = "ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) >= ?";
            $bindings[] = (int)$queryParams['min_discount'];
        }

        // Sorting
        $sort = $queryParams['sort'] ?? 'popularity';
        $orderClause = match ($sort) {
            'price_low'  => 'p.base_price ASC',
            'price_high' => 'p.base_price DESC',
            'newest'     => 'p.id DESC',
            'rating'     => 'p.rating DESC, p.review_count DESC',
            'discount'   => '((p.base_mrp - p.base_price) / p.base_mrp) DESC',
            default      => 'p.review_count DESC, p.rating DESC'
        };

        $whereSql = implode(' AND ', $where);

        // Count query
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM `products` p WHERE {$whereSql}");
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        // Main query
        $sql = "SELECT p.id, p.title, p.slug, p.brand_id, p.category_id, p.seller_id,
            p.base_mrp, p.base_price, p.rating, p.review_count, p.is_featured,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name, c.name as category_name, s.store_name as seller_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            JOIN `sellers` s ON p.seller_id = s.id
            WHERE {$whereSql}
            ORDER BY {$orderClause}
            LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $products = $stmt->fetchAll();

        Response::paginated($products, $total, $page, $limit, "Products retrieved");
    }

    /**
     * Product Detail Page
     */
    public function getProductById(array $params): void {
        $idOrSlug = $params['id'];

        $stmt = $this->db->prepare("SELECT p.*,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name, b.slug as brand_slug,
            c.name as category_name, c.slug as category_slug,
            s.id as seller_id, s.store_name, s.rating as seller_rating, s.rating_count as seller_rating_count,
            s.city as seller_city, s.state as seller_state
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            JOIN `sellers` s ON p.seller_id = s.id
            WHERE (p.id = ? OR p.slug = ?) AND p.is_active = 1 AND p.deleted_at IS NULL");
        $stmt->execute([$idOrSlug, $idOrSlug]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error("Product not found", [], 404);
        }

        $productId = $product['id'];

        // Decode JSON fields
        $product['highlights'] = json_decode($product['highlights'] ?? '[]', true);
        $product['specifications'] = json_decode($product['specifications'] ?? '{}', true);

        // Gallery Images
        $imgStmt = $this->db->prepare("SELECT id, image_url, is_primary, sort_order FROM `product_images` WHERE `product_id` = ? ORDER BY `is_primary` DESC, `sort_order` ASC");
        $imgStmt->execute([$productId]);
        $product['images'] = $imgStmt->fetchAll();

        // Product Variants
        $varStmt = $this->db->prepare("SELECT id, sku, title, color, size, storage, ram, model, weight, mrp, price, stock, image_url,
            ROUND(((mrp - price) / mrp) * 100) as discount_percentage
            FROM `product_variants` WHERE `product_id` = ? AND `is_active` = 1");
        $varStmt->execute([$productId]);
        $product['variants'] = $varStmt->fetchAll();

        // Reviews Summary & Recent Reviews
        $revStmt = $this->db->prepare("SELECT r.*, u.name as user_name, u.avatar_url as user_avatar 
            FROM `reviews` r
            JOIN `users` u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'APPROVED'
            ORDER BY r.id DESC LIMIT 5");
        $revStmt->execute([$productId]);
        $product['recent_reviews'] = $revStmt->fetchAll();

        // Available Bank & Promo Offers
        $product['offers'] = [
            ['title' => 'Bank Offer', 'description' => '5% Unlimited Cashback on Axis Bank Credit Card', 'code' => 'AXIS5'],
            ['title' => 'Special Price', 'description' => 'Get extra ₹100 off on first purchase (use WELCOME100)', 'code' => 'WELCOME100'],
            ['title' => 'Partner Offer', 'description' => 'Sign-up for Flipkart Pay Later & get free ₹250 Gift Card', 'code' => 'PAYLATER']
        ];

        // Similar Products
        $simStmt = $this->db->prepare("SELECT p.id, p.title, p.slug, p.base_mrp, p.base_price, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
            LIMIT 6");
        $simStmt->execute([$product['category_id'], $productId]);
        $product['similar_products'] = $simStmt->fetchAll();

        Response::success($product, "Product details retrieved");
    }

    /**
     * Banners Endpoint
     */
    public function getBanners(): void {
        $position = $_GET['position'] ?? null;
        $sql = "SELECT * FROM `banners` WHERE `is_active` = 1";
        $bindings = [];
        if ($position) {
            $sql .= " AND `position` = ?";
            $bindings[] = $position;
        }
        $sql .= " ORDER BY `sort_order` ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        Response::success($stmt->fetchAll(), "Banners retrieved");
    }
}
