<?php
/**
 * Server-side Search Controller with Debounce Suggestions & Faceted Filtering
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SearchController extends BaseController {

    /**
     * Server-side Product Search
     */
    public function search(): void {
        $queryParams = $this->getQueryParams();
        $q = trim($queryParams['q'] ?? '');

        if ($q === '') {
            Response::paginated([], 0, 1, 20, "Please enter a search term");
        }

        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = min(50, max(1, (int)($queryParams['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        // Record search history asynchronously/silently
        $this->recordSearchQuery($q);

        $searchTerm = "%{$q}%";
        $where = [
            "p.is_active = 1",
            "p.status = 'APPROVED'",
            "p.deleted_at IS NULL",
            "(p.title LIKE ? OR p.description LIKE ? OR b.name LIKE ? OR c.name LIKE ? OR pv.sku LIKE ?)"
        ];
        $bindings = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];

        // Optional brand filter
        if (!empty($queryParams['brand_id'])) {
            $where[] = "p.brand_id = ?";
            $bindings[] = (int)$queryParams['brand_id'];
        }

        // Optional category filter
        if (!empty($queryParams['category_id'])) {
            $where[] = "p.category_id = ?";
            $bindings[] = (int)$queryParams['category_id'];
        }

        // Price filters
        if (!empty($queryParams['min_price'])) {
            $where[] = "p.base_price >= ?";
            $bindings[] = (float)$queryParams['min_price'];
        }
        if (!empty($queryParams['max_price'])) {
            $where[] = "p.base_price <= ?";
            $bindings[] = (float)$queryParams['max_price'];
        }

        // Min rating
        if (!empty($queryParams['min_rating'])) {
            $where[] = "p.rating >= ?";
            $bindings[] = (float)$queryParams['min_rating'];
        }

        // Min discount
        if (!empty($queryParams['min_discount'])) {
            $where[] = "ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) >= ?";
            $bindings[] = (int)$queryParams['min_discount'];
        }

        $whereSql = implode(' AND ', $where);

        // Count distinct products
        $countSql = "SELECT COUNT(DISTINCT p.id) FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            LEFT JOIN `product_variants` pv ON pv.product_id = p.id
            WHERE {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        // Sort
        $sort = $queryParams['sort'] ?? 'relevance';
        $orderClause = match ($sort) {
            'price_low'  => 'p.base_price ASC',
            'price_high' => 'p.base_price DESC',
            'newest'     => 'p.id DESC',
            'rating'     => 'p.rating DESC',
            'discount'   => '((p.base_mrp - p.base_price) / p.base_mrp) DESC',
            default      => 'CASE WHEN p.title LIKE ? THEN 1 WHEN b.name LIKE ? THEN 2 ELSE 3 END, p.review_count DESC'
        };

        if ($sort === 'relevance' || $sort === '') {
            $bindings[] = "%{$q}%";
            $bindings[] = "%{$q}%";
        }

        $sql = "SELECT DISTINCT p.id, p.title, p.slug, p.brand_id, p.category_id, p.seller_id,
            p.base_mrp, p.base_price, p.rating, p.review_count,
            ROUND(((p.base_mrp - p.base_price) / p.base_mrp) * 100) as discount_percentage,
            b.name as brand_name, c.name as category_name, s.store_name as seller_name,
            (SELECT image_url FROM `product_images` pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM `products` p
            JOIN `brands` b ON p.brand_id = b.id
            JOIN `categories` c ON p.category_id = c.id
            JOIN `sellers` s ON p.seller_id = s.id
            LEFT JOIN `product_variants` pv ON pv.product_id = p.id
            WHERE {$whereSql}
            ORDER BY {$orderClause}
            LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $results = $stmt->fetchAll();

        Response::paginated($results, $total, $page, $limit, "Search results for '{$q}'");
    }

    /**
     * Debounced Auto-Complete Search Suggestions
     */
    public function suggestions(): void {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            Response::success([
                'suggestions' => [],
                'popular' => $this->getPopularSearches()
            ]);
        }

        $term = "%{$q}%";

        // Match product titles
        $pStmt = $this->db->prepare("SELECT title as text, 'product' as type, slug as ref_id FROM `products` WHERE `title` LIKE ? AND `is_active` = 1 LIMIT 5");
        $pStmt->execute([$term]);
        $prodMatches = $pStmt->fetchAll();

        // Match brand names
        $bStmt = $this->db->prepare("SELECT name as text, 'brand' as type, slug as ref_id FROM `brands` WHERE `name` LIKE ? AND `is_active` = 1 LIMIT 3");
        $bStmt->execute([$term]);
        $brandMatches = $bStmt->fetchAll();

        // Match category names
        $cStmt = $this->db->prepare("SELECT name as text, 'category' as type, slug as ref_id FROM `categories` WHERE `name` LIKE ? AND `is_active` = 1 LIMIT 3");
        $cStmt->execute([$term]);
        $catMatches = $cStmt->fetchAll();

        $suggestions = array_merge($prodMatches, $brandMatches, $catMatches);

        Response::success([
            'query'       => $q,
            'suggestions' => $suggestions,
            'popular'     => $this->getPopularSearches()
        ], "Search suggestions");
    }

    /**
     * Popular and Trending Searches
     */
    public function popular(): void {
        Response::success($this->getPopularSearches(), "Popular searches");
    }

    private function getPopularSearches(): array {
        return [
            'iPhone 15 Case',
            'Apple iPhone 15',
            'boAt Airdopes',
            'Smart Watches',
            'Running Shoes Puma',
            '4K Smart TV',
            'Cotton Shirts'
        ];
    }

    private function recordSearchQuery(string $query): void {
        try {
            $user = AuthMiddleware::authenticate(false);
            $userId = $user ? $user['id'] : null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $this->db->prepare("INSERT INTO `search_history` (`user_id`, `query`, `ip_address`) VALUES (?, ?, ?)");
            $stmt->execute([$userId, substr($query, 0, 150), $ip]);
        } catch (Exception $e) {
            // Ignore search log failures
        }
    }
}
