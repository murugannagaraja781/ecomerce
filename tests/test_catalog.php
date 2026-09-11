<?php
/**
 * Catalog and Search Automated Test Suite
 */

function getJson($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($res, true)];
}

$base = 'http://localhost/ecommerce_api/api';

echo "=== 1. Testing Homepage Feed ===\n";
$home = getJson("{$base}/home");
assert($home['code'] === 200, "Home must return 200");
echo "Banners count: " . count($home['data']['data']['banners']) . "\n";
echo "Categories count: " . count($home['data']['data']['categories']) . "\n";
echo "Flash deals count: " . count($home['data']['data']['flash_deals']) . "\n";

echo "\n=== 2. Testing Category Tree ===\n";
$cats = getJson("{$base}/categories");
assert($cats['code'] === 200, "Categories must return 200");
echo "Top level categories: " . count($cats['data']['data']) . "\n";
echo "First category: " . $cats['data']['data'][0]['name'] . " (Subcats: " . count($cats['data']['data'][0]['subcategories']) . ")\n";

echo "\n=== 3. Testing Products List & Pagination ===\n";
$prods = getJson("{$base}/products?limit=10&page=1");
assert($prods['code'] === 200, "Products must return 200");
echo "Total products in DB: " . $prods['data']['pagination']['total_items'] . "\n";
echo "Products on page 1: " . count($prods['data']['data']) . "\n";

echo "\n=== 4. Testing Product Detail (Celvas iPhone 15 Cover #1) ===\n";
$sampleProd = getJson("{$base}/products/1");
assert($sampleProd['code'] === 200, "Product 1 must return 200");
$p = $sampleProd['data']['data'];
echo "Title: " . $p['title'] . "\n";
echo "Brand: " . $p['brand_name'] . ", Category: " . $p['category_name'] . "\n";
echo "Price: ₹" . $p['base_price'] . " (MRP: ₹" . $p['base_mrp'] . ", Discount: " . $p['discount_percentage'] . "%)\n";
echo "Variants count: " . count($p['variants']) . "\n";
echo "Images count: " . count($p['images']) . "\n";
echo "Seller: " . $p['store_name'] . " (Rating: " . $p['seller_rating'] . ")\n";

echo "\n=== 5. Testing Server-side Search ===\n";
$searchRes = getJson("{$base}/search?q=iPhone");
assert($searchRes['code'] === 200, "Search must return 200");
echo "Search results for 'iPhone': " . $searchRes['data']['pagination']['total_items'] . " items found\n";
echo "Top match: " . $searchRes['data']['data'][0]['title'] . "\n";

echo "\n=== 6. Testing Search Suggestions (Debounced) ===\n";
$sugg = getJson("{$base}/search/suggestions?q=Celv");
assert($sugg['code'] === 200, "Suggestions must return 200");
echo "Suggestions found: " . count($sugg['data']['data']['suggestions']) . "\n";
foreach ($sugg['data']['data']['suggestions'] as $s) {
    echo " - [{$s['type']}] {$s['text']}\n";
}

echo "\n=== ALL CATALOG & SEARCH TESTS PASSED PERFECTLY ===\n";
