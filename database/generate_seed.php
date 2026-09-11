<?php
/**
 * Database Seed Generator for flipkartdb
 * Generates database/seed.sql with 100+ products, variants, categories, brands, sellers, etc.
 */

$adminHash = '$2y$10$ybRkEb2NJzWJvUZS.X3Pe.wHWryxs9zxzO.bwJ7bB00r3oNa93zSm'; // Admin@12345
$sellerHash = '$2y$10$tRqMG.16EEoH0RzaZ/1iDOjDQG4D3Csa.IYN28fNpArmRXCqrJ7G2'; // Seller@12345
$customerHash = '$2y$10$qePIEaQRHNVHZskunRuM..AhsQpaVD6oDqZ6ZpgLb7YZAl.jBJxkW'; // Customer@12345

$sql = [];
$sql[] = "USE `flipkartdb`;";
$sql[] = "SET FOREIGN_KEY_CHECKS = 0;";

// 1. Roles
$sql[] = "INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES
(1, 'SUPER_ADMIN', 'Super Administrator', 'Full platform access and management'),
(2, 'ADMIN', 'Store Administrator', 'Product, order, customer and system management'),
(3, 'SELLER', 'Merchant / Seller', 'Seller portal access for inventory and orders'),
(4, 'CUSTOMER', 'Customer', 'Shopper account on mobile and web'),
(5, 'SUPPORT_AGENT', 'Customer Support', 'Customer support, order tracking and tickets')
ON DUPLICATE KEY UPDATE `display_name`=VALUES(`display_name`);";

// 2. Permissions
$sql[] = "INSERT INTO `permissions` (`id`, `name`, `module`, `description`) VALUES
(1, 'products.view', 'Catalog', 'View products'),
(2, 'products.create', 'Catalog', 'Create new products'),
(3, 'products.update', 'Catalog', 'Update existing products'),
(4, 'products.delete', 'Catalog', 'Delete products'),
(5, 'categories.manage', 'Catalog', 'Manage categories'),
(6, 'orders.view', 'Orders', 'View customer orders'),
(7, 'orders.update', 'Orders', 'Update order statuses'),
(8, 'sellers.manage', 'Sellers', 'Approve and manage sellers'),
(9, 'coupons.manage', 'Promotions', 'Manage discount coupons'),
(10, 'reports.view', 'Analytics', 'View sales and financial reports')
ON DUPLICATE KEY UPDATE `description`=VALUES(`description`);";

// 3. Role Permissions
$sql[] = "INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
(2, 1), (2, 2), (2, 3), (2, 5), (2, 6), (2, 7), (2, 9), (2, 10),
(3, 1), (3, 2), (3, 3), (3, 6), (3, 7),
(5, 1), (5, 6), (5, 7)
ON DUPLICATE KEY UPDATE `role_id`=VALUES(`role_id`);";

// 4. Users
$sql[] = "INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `phone`, `password_hash`, `avatar_url`, `is_active`, `is_verified`) VALUES
(1, 1, 'System Administrator', 'admin@flipkart.local', '9900000001', '{$adminHash}', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150', 1, 1),
(2, 3, 'Celvas India Pvt Ltd', 'seller@celvas.in', '9811122233', '{$sellerHash}', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150', 1, 1),
(3, 3, 'GadgetZone Retails', 'seller@gadgetzone.in', '9822233344', '{$sellerHash}', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150', 1, 1),
(4, 4, 'Rahul Sharma', 'customer@gmail.com', '9876543210', '{$customerHash}', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150', 1, 1),
(5, 4, 'Priya Patel', 'priya.patel@gmail.com', '9876543211', '{$customerHash}', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150', 1, 1)
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);";

// 5. Sellers
$sql[] = "INSERT INTO `sellers` (`id`, `user_id`, `store_name`, `business_email`, `business_phone`, `gstin`, `pan`, `address`, `city`, `state`, `pincode`, `rating`, `rating_count`, `status`, `commission_rate`) VALUES
(1, 2, 'Celvas Official Store', 'seller@celvas.in', '9811122233', '29AABCC1234F1Z5', 'AABCC1234F', '124 Industrial Tech Park, Whitefield', 'Bengaluru', 'Karnataka', '560066', 4.85, 1420, 'APPROVED', 5.00),
(2, 3, 'GadgetZone India', 'seller@gadgetzone.in', '9822233344', '27AABCD5678G2Z1', 'AABCD5678G', '45 Electronics Complex, Andheri East', 'Mumbai', 'Maharashtra', '400069', 4.70, 890, 'APPROVED', 6.50)
ON DUPLICATE KEY UPDATE `store_name`=VALUES(`store_name`);";

// 6. Categories & Subcategories (12 Parents + 36 Subcategories)
$parents = [
    1 => ['Mobiles & Tablets', 'mobiles-tablets', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300', 'Explore smartphones, iPhones, tablets and accessories'],
    2 => ['Electronics & Audio', 'electronics-audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300', 'Headphones, laptops, smartwatches and gaming gear'],
    3 => ["Men's Fashion", 'mens-fashion', 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=300', 'Trendy shirts, jeans, ethnic wear and footwear for men'],
    4 => ["Women's Fashion", 'womens-fashion', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=300', 'Kurtas, sarees, western dresses and jewelry for women'],
    5 => ['Home & Furniture', 'home-furniture', 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=300', 'Furniture, bedding, home decor and kitchenware'],
    6 => ['TV & Appliances', 'tv-appliances', 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=300', 'Smart televisions, refrigerators, washing machines and ACs'],
    7 => ['Beauty & Grooming', 'beauty-grooming', 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=300', 'Skincare, perfumes, makeup and personal care essentials'],
    8 => ['Grocery & Gourmet', 'grocery-gourmet', 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=300', 'Daily staples, snacks, beverages and household supplies'],
    9 => ['Sports & Fitness', 'sports-fitness', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=300', 'Gym equipment, yoga mats, running shoes and sports gear'],
    10 => ['Toys & Baby Care', 'toys-baby-care', 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=300', 'Toys, board games, strollers, diapers and baby clothing'],
    11 => ['Books & Stationery', 'books-stationery', 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=300', 'Bestseller books, office stationery and art supplies'],
    12 => ['Automotive & Accessories', 'automotive-accessories', 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=300', 'Car care, helmets, bike accessories and GPS gadgets']
];

$subcats = [
    // Mobiles (1)
    [13, 1, 'Smartphones', 'smartphones'],
    [14, 1, 'iPhones & iOS', 'iphones-ios'],
    [15, 1, 'Mobile Cases & Covers', 'mobile-cases-covers'],
    [16, 1, 'Screen Protectors', 'screen-protectors'],
    // Electronics (2)
    [17, 2, 'Wireless Earbuds', 'wireless-earbuds'],
    [18, 2, 'Laptops & MacBooks', 'laptops-macbooks'],
    [19, 2, 'Smart Watches', 'smart-watches'],
    [20, 2, 'Bluetooth Speakers', 'bluetooth-speakers'],
    // Men's Fashion (3)
    [21, 3, 'Casual T-Shirts', 'mens-tshirts'],
    [22, 3, 'Formal Shirts', 'mens-formal-shirts'],
    [23, 3, 'Jeans & Trousers', 'mens-jeans'],
    [24, 3, 'Sneakers & Sports Shoes', 'mens-footwear'],
    // Women's Fashion (4)
    [25, 4, 'Ethnic Kurtas & Sets', 'womens-kurtas'],
    [26, 4, 'Western Dresses', 'womens-dresses'],
    [27, 4, 'Handbags & Clutches', 'womens-handbags'],
    [28, 4, 'Jewelry & Watches', 'womens-jewelry'],
    // Home (5)
    [29, 5, 'Sofas & Recliners', 'sofas-recliners'],
    [30, 5, 'Beds & Mattresses', 'beds-mattresses'],
    [31, 5, 'Curtains & Bedding', 'curtains-bedding'],
    [32, 5, 'Cookware & Kitchen Tools', 'cookware-kitchen'],
    // TV & Appliances (6)
    [33, 6, 'Smart 4K TVs', 'smart-4k-tvs'],
    [34, 6, 'Refrigerators', 'refrigerators'],
    [35, 6, 'Washing Machines', 'washing-machines'],
    [36, 6, 'Air Conditioners', 'air-conditioners'],
    // Beauty (7)
    [37, 7, 'Face Serums & Moisturizers', 'face-serums'],
    [38, 7, 'Perfumes & Deodorants', 'perfumes-deodorants'],
    [39, 7, 'Hair Care & Shampoos', 'hair-care'],
    // Grocery (8)
    [40, 8, 'Dry Fruits & Nuts', 'dry-fruits-nuts'],
    [41, 8, 'Organic Tea & Coffee', 'tea-coffee'],
    [42, 8, 'Snacks & Namkeen', 'snacks-namkeen'],
    // Sports (9)
    [43, 9, 'Yoga Mats & Resistance Bands', 'yoga-fitness-gear'],
    [44, 9, 'Cricket & Badminton Gear', 'cricket-badminton'],
    // Toys (10)
    [45, 10, 'Educational & STEM Toys', 'stem-educational-toys'],
    [46, 10, 'Baby Diapers & Wipes', 'baby-diapers'],
    // Books (11)
    [47, 11, 'Self-Help & Business Books', 'business-self-help-books'],
    // Auto (12)
    [48, 12, 'Helmets & Riding Gear', 'helmets-riding-gear']
];

$catVals = [];
foreach ($parents as $id => $p) {
    $pName = addslashes($p[0]);
    $pSlug = addslashes($p[1]);
    $pImg = addslashes($p[2]);
    $pDesc = addslashes($p[3]);
    $catVals[] = "({$id}, NULL, '{$pName}', '{$pSlug}', '{$pImg}', '{$pImg}', '{$pDesc}', {$id}, 1)";
}
foreach ($subcats as $s) {
    $sName = addslashes($s[2]);
    $sSlug = addslashes($s[3]);
    $catVals[] = "({$s[0]}, {$s[1]}, '{$sName}', '{$sSlug}', NULL, NULL, 'Best products in {$sName}', {$s[0]}, 1)";
}
$sql[] = "INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `image_url`, `banner_url`, `description`, `sort_order`, `is_active`) VALUES\n" . implode(",\n", $catVals) . "\nON DUPLICATE KEY UPDATE `name`=VALUES(`name`);";

// 7. Brands (25 Brands)
$brands = [
    [1, 'Celvas', 'celvas', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=100', 'Premium mobile cases and tempered glass accessories'],
    [2, 'Apple', 'apple', 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=100', 'Innovators in personal tech, iPhone, iPad and Mac'],
    [3, 'Samsung', 'samsung', 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=100', 'Galaxy smartphones, Neo QLED TVs and smart home appliances'],
    [4, 'OnePlus', 'oneplus', 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=100', 'Never Settle smartphones, audio and ecosystem devices'],
    [5, 'Realme', 'realme', 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=100', 'Dare to Leap smartphones and lifestyle gadgets'],
    [6, 'Xiaomi', 'xiaomi', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=100', 'Smartphones, smart bands, smart TVs and IoT ecosystem'],
    [7, 'boAt', 'boat', 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=100', 'India leading audio, smartwatches and mobile accessories'],
    [8, 'Noise', 'noise', 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=100', 'Smart wearables and wireless audio tech for youth'],
    [9, 'Sony', 'sony', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=100', 'World-class Bravia TVs, Alpha cameras and noise cancelling audio'],
    [10, 'Puma', 'puma', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=100', 'Forever Faster athletic shoes, sneakers and apparel'],
    [11, 'Nike', 'nike', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=100', 'Just Do It running shoes, training gear and street fashion'],
    [12, "Levi\'s", 'levis', 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=100', 'Original iconic denim jeans, jackets and casual apparel'],
    [13, 'Allen Solly', 'allen-solly', 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=100', 'Friday Dressing premium smart formal and casual shirts'],
    [14, 'Biba', 'biba', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=100', 'Celebrated Indian ethnic wear, anarkalis and festive kurtas'],
    [15, 'Philips', 'philips', 'https://images.unsplash.com/photo-1621607512214-68297480165e?w=100', 'Grooming trimmers, air fryers and smart lighting'],
    [16, 'Mamaearth', 'mamaearth', 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=100', 'Toxin-free natural skincare, haircare and baby care'],
    [17, 'Prestige', 'prestige', 'https://images.unsplash.com/photo-1584990347449-383bc8155e94?w=100', 'India trusted pressure cookers, cookware and appliances'],
    [18, 'Wakefit', 'wakefit', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=100', 'Orthopedic memory foam mattresses and modern furniture'],
    [19, 'LG', 'lg', 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=100', 'Life Good OLED televisions, inverter ACs and refrigerators'],
    [20, 'Asus', 'asus', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=100', 'ROG gaming laptops and ZenBook ultrabooks'],
    [21, 'Dell', 'dell', 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=100', 'XPS and Inspiron high performance computing laptops'],
    [22, 'Happilo', 'happilo', 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=100', 'Premium California almonds, cashews, raisins and berries'],
    [23, 'Vega', 'vega', 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=100', 'ISI certified motorcycle helmets and safety gear'],
    [24, 'Nivia', 'nivia', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=100', 'Football, basketball and athletic training sports goods'],
    [25, 'Penguin Random House', 'penguin', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100', 'Leading publisher of global bestsellers and literature']
];

$brandVals = [];
foreach ($brands as $b) {
    $bName = addslashes($b[1]);
    $bSlug = addslashes($b[2]);
    $bLogo = addslashes($b[3]);
    $bDesc = addslashes($b[4]);
    $brandVals[] = "({$b[0]}, '{$bName}', '{$bSlug}', '{$bLogo}', NULL, '{$bDesc}', 1)";
}
$sql[] = "INSERT INTO `brands` (`id`, `name`, `slug`, `logo_url`, `banner_url`, `description`, `is_active`) VALUES\n" . implode(",\n", $brandVals) . "\nON DUPLICATE KEY UPDATE `name`=VALUES(`name`);";

// 8. Banners
$sql[] = "INSERT INTO `banners` (`id`, `title`, `subtitle`, `image_url`, `mobile_image_url`, `link_type`, `link_id`, `cta_text`, `position`, `sort_order`, `is_active`) VALUES
(1, 'The Big Shopping Carnival', 'Mega Discounts up to 80% Off on Electronics & Smartphones', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600', 'CATEGORY', '1', 'Shop Now', 'HOME_HERO', 1, 1),
(2, 'iPhone 15 Premium Protection', 'Celvas Military-Grade Shockproof Armor Cases Starting at ₹499', 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=1200', 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600', 'PRODUCT', '1', 'Explore Deal', 'HOME_HERO', 2, 1),
(3, 'Festive Fashion Trends', 'Min 50% Off on Top Brands - Puma, Levi\'\'s, Allen Solly, Biba', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600', 'CATEGORY', '3', 'Upgrade Wardrobe', 'HOME_HERO', 3, 1),
(4, 'Next-Gen True Wireless Audio', 'Sony, boAt & Noise Earbuds with Active Noise Cancellation', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=1200', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600', 'CATEGORY', '17', 'Grab Deal', 'MIDDLE_STRIP', 4, 1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);";

// 9. Coupons
$sql[] = "INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `start_date`, `expiry_date`, `usage_limit`, `per_user_limit`, `is_active`) VALUES
(1, 'WELCOME100', 'Flat ₹100 instant off on your first shopping order above ₹499', 'FIXED', 100.00, 499.00, 100.00, '2026-01-01 00:00:00', '2028-12-31 23:59:59', 50000, 1, 1),
(2, 'FLIPDEAL20', '20% off up to ₹500 on all electronics and smartphone accessories', 'PERCENTAGE', 20.00, 999.00, 500.00, '2026-01-01 00:00:00', '2028-12-31 23:59:59', 20000, 2, 1),
(3, 'BIGFEST10', '10% instant discount up to ₹1,500 on large orders above ₹4,999', 'PERCENTAGE', 10.00, 4999.00, 1500.00, '2026-01-01 00:00:00', '2028-12-31 23:59:59', 10000, 1, 1),
(4, 'FREESHIP', 'Zero delivery charge on orders above ₹299', 'FIXED', 40.00, 299.00, 40.00, '2026-01-01 00:00:00', '2028-12-31 23:59:59', 100000, 5, 1)
ON DUPLICATE KEY UPDATE `description`=VALUES(`description`);";

// 10. Demo Addresses
$sql[] = "INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `alternate_phone`, `pincode`, `address_line1`, `address_line2`, `landmark`, `city`, `state`, `address_type`, `is_default`) VALUES
(1, 4, 'Rahul Sharma', '9876543210', '9876543219', '560034', '#402, Green Glen Layout, Bellandur', 'Outer Ring Road', 'Near Central Mall', 'Bengaluru', 'Karnataka', 'HOME', 1),
(2, 4, 'Rahul Sharma (Office)', '9876543210', NULL, '560103', 'Ecospace Tech Park, Block 2B, 4th Floor', 'Marathahalli - Sarjapur Outer Ring Rd', 'Opposite Cisco', 'Bengaluru', 'Karnataka', 'WORK', 0),
(3, 5, 'Priya Patel', '9876543211', NULL, '400053', 'Flat 501, Silver Sands Apartments', 'Lokhandwala Complex, Andheri West', 'Near High Street', 'Mumbai', 'Maharashtra', 'HOME', 1)
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);";

// 11. 105 Realistic Products (Starting with Sample Product #1: Celvas Back Cover for Apple iPhone 15)
$productsData = [];
// #1: Celvas Back Cover for Apple iPhone 15
$productsData[] = [
    'id' => 1,
    'title' => 'Celvas Back Cover for Apple iPhone 15',
    'slug' => 'celvas-back-cover-apple-iphone-15',
    'brand_id' => 1, // Celvas
    'category_id' => 15, // Mobile Cases & Covers
    'seller_id' => 1, // Celvas Official
    'description' => 'Engineered with aerospace-grade polycarbonate and impact-absorbing TPU shock bumpers, the Celvas Armor Case for Apple iPhone 15 offers military-grade drop protection up to 10 feet. It features raised 1.5mm bezels to shield your Super Retina XDR display and advanced camera lens cluster from scratches. With a matte oleophobic finish that resists fingerprints and yellowing, and built-in MagSafe magnetic ring array for ultra-fast wireless charging, this is the ultimate case for your iPhone 15.',
    'highlights' => json_encode([
        'Compatible with Apple iPhone 15 (6.1 inch)',
        'Built-in MagSafe Strong Magnetic Ring Array for Qi2 Wireless Charging',
        'Certified Military Grade Drop Protection (MIL-STD-810G)',
        '1.5mm Raised Camera Lip & 1.2mm Raised Screen Edges',
        'Anti-Fingerprint Matte Frosted Translucent Finish with tactile clicky alloy buttons'
    ]),
    'specifications' => json_encode([
        'Brand' => 'Celvas',
        'Model Name' => 'MagArmor Stealth Shield',
        'Color' => 'Matte Midnight Black',
        'Material' => 'Polycarbonate Backplate + Shockproof TPU Bumper',
        'Type' => 'Back Cover with MagSafe',
        'Drop Protection' => '10 Feet Certified Drop Tested',
        'Wireless Charging' => 'Full MagSafe & Qi Compatibility',
        'Package Contents' => '1x Celvas Armor Case, 1x Screen Cleaning Wipe Kit'
    ]),
    'base_mrp' => 1499.00,
    'base_price' => 499.00,
    'is_featured' => 1,
    'rating' => 4.65,
    'review_count' => 384,
    'variants' => [
        ['sku' => 'CEL-IP15-BLK', 'title' => 'Celvas iPhone 15 Cover - Midnight Black', 'color' => 'Midnight Black', 'mrp' => 1499.00, 'price' => 499.00, 'stock' => 150, 'img' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600'],
        ['sku' => 'CEL-IP15-BLU', 'title' => 'Celvas iPhone 15 Cover - Deep Ocean Blue', 'color' => 'Ocean Blue', 'mrp' => 1499.00, 'price' => 499.00, 'stock' => 85, 'img' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600'],
        ['sku' => 'CEL-IP15-CLR', 'title' => 'Celvas iPhone 15 Cover - Crystal Frost Clear', 'color' => 'Frost Clear', 'mrp' => 1599.00, 'price' => 549.00, 'stock' => 120, 'img' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600']
    ],
    'images' => [
        ['url' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800', 'is_primary' => 1],
        ['url' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800', 'is_primary' => 0],
        ['url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800', 'is_primary' => 0],
        ['url' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=800', 'is_primary' => 0]
    ]
];

// Reusable catalog definitions
$productTemplates = [
    // [Title, BrandID, SubcatID, SellerID, MRP, Price, Rating, Reviews, Colors, Img]
    ['Apple iPhone 15 (128 GB) - Black', 2, 14, 2, 79900.00, 69999.00, 4.80, 2450, ['Black', 'Blue', 'Pink', 'Green'], 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600'],
    ['Apple iPhone 15 Pro (256 GB) - Natural Titanium', 2, 14, 2, 134900.00, 124999.00, 4.90, 1890, ['Natural Titanium', 'Blue Titanium', 'Black Titanium'], 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600'],
    ['Samsung Galaxy S24 5G (8GB RAM, 128GB)', 3, 13, 2, 74999.00, 64999.00, 4.70, 1120, ['Onyx Black', 'Cobalt Violet', 'Amber Yellow'], 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600'],
    ['OnePlus 12R 5G (16GB RAM, 256GB Storage)', 4, 13, 2, 45999.00, 39999.00, 4.60, 950, ['Cool Blue', 'Iron Gray'], 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600'],
    ['Realme 12 Pro+ 5G (Submarine Blue, 256GB)', 5, 13, 2, 34999.00, 29999.00, 4.50, 780, ['Submarine Blue', 'Navigator Beige'], 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600'],
    ['Xiaomi Redmi Note 13 Pro+ 5G', 6, 13, 2, 33999.00, 28999.00, 4.50, 890, ['Fusion Purple', 'Midnight Black'], 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600'],
    ['Celvas 9H Edge-to-Edge Tempered Glass for iPhone 15', 1, 16, 1, 999.00, 299.00, 4.70, 640, ['Clear HD'], 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=600'],
    ['Celvas Heavy Duty Armor Case for Samsung Galaxy S24', 1, 15, 1, 1299.00, 449.00, 4.60, 310, ['Matte Black', 'Army Green'], 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=600'],
    ['Sony WH-1000XM5 Wireless Active Noise Cancelling Headphones', 9, 17, 2, 34990.00, 26990.00, 4.85, 3400, ['Black', 'Silver', 'Midnight Blue'], 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600'],
    ['boAt Airdopes 141 True Wireless Earbuds with 42H Playtime', 7, 17, 2, 4490.00, 1199.00, 4.30, 8900, ['Bold Black', 'Pure White', 'Cyan Cider'], 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600'],
    ['Noise ColorFit Pulse 3 Smartwatch with 1.96 Display', 8, 19, 2, 4999.00, 1499.00, 4.25, 4500, ['Jet Black', 'Rose Pink', 'Deep Wine'], 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600'],
    ['Apple Watch Series 9 GPS 45mm Midnight Aluminum', 2, 19, 2, 44900.00, 41999.00, 4.80, 1200, ['Midnight', 'Starlight', 'Silver'], 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'],
    ['Asus ROG Strix G16 Gaming Laptop (Intel i7 13th Gen, RTX 4060)', 20, 18, 2, 149990.00, 124990.00, 4.70, 430, ['Eclipse Gray'], 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600'],
    ['Dell XPS 13 Plus Laptop (Intel Evo Core i7, 16GB, 1TB SSD)', 21, 18, 2, 179900.00, 154990.00, 4.65, 210, ['Platinum', 'Graphite'], 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600'],
    ['Sony Bravia 55 inch 4K Ultra HD Smart Google TV', 9, 33, 2, 89900.00, 57990.00, 4.75, 1890, ['Black'], 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=600'],
    ['LG 343 L 3 Star Frost Free Smart Inverter Refrigerator', 19, 34, 2, 48999.00, 36990.00, 4.60, 920, ['Shiny Steel'], 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=600'],
    ['Philips Series 5000 Skin-Protect Trimmer', 15, 38, 1, 2995.00, 1799.00, 4.50, 3100, ['Metallic Navy'], 'https://images.unsplash.com/photo-1621607512214-68297480165e?w=600'],
    ['Puma Men Velocity Nitro 2 Running Shoes', 10, 24, 2, 10999.00, 4999.00, 4.60, 870, ['Black-Lime', 'Royal Blue', 'All White'], 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600'],
    ['Nike Air Max SC Mens Walking & Gym Sneakers', 11, 24, 2, 7995.00, 5495.00, 4.70, 1450, ['White-University Red', 'Triple Black'], 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600'],
    ["Levi's 511 Slim Fit Men Washed Denim Jeans", 12, 23, 2, 4199.00, 2299.00, 4.50, 2300, ['Dark Indigo', 'Stone Wash Blue', 'Black'], 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600'],
    ['Allen Solly Men Regular Fit Solid Formal Cotton Shirt', 13, 22, 2, 2199.00, 1099.00, 4.40, 1800, ['Sky Blue', 'Crisp White', 'Soft Pink'], 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600'],
    ['Biba Women Pure Cotton Embroidered Festive Anarkali Kurta Set', 14, 25, 2, 6995.00, 3495.00, 4.65, 1100, ['Maroon', 'Emerald Green', 'Mustard Yellow'], 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600'],
    ['Mamaearth Vitamin C Daily Glow Face Serum (30ml)', 16, 37, 1, 699.00, 499.00, 4.45, 6200, ['Standard 30ml'], 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600'],
    ['Prestige Deluxe Alpha Stainless Steel Pressure Cooker 3L', 17, 32, 2, 3200.00, 2199.00, 4.60, 4100, ['Silver Steel'], 'https://images.unsplash.com/photo-1584990347449-383bc8155e94?w=600'],
    ['Wakefit Orthopedic Memory Foam Mattress (78x60x6 inches)', 18, 30, 2, 16999.00, 11499.00, 4.70, 5200, ['Space Grey'], 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600'],
    ['Happilo Premium California Roasted Almonds (500g)', 22, 40, 1, 649.00, 429.00, 4.70, 7800, ['500g Pouch'], 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=600'],
    ['Vega Bolt Full Face ISI Certified Motorbike Helmet', 23, 48, 1, 2450.00, 1850.00, 4.55, 3100, ['Matte Black', 'Glossy White', 'Neon Graphic'], 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600'],
    ['Nivia Storm Football Rubber Molded Official Size 5', 24, 44, 1, 749.00, 449.00, 4.50, 4100, ['White-Orange'], 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600'],
    ['Atomic Habits by James Clear (Paperback Bestseller)', 25, 47, 1, 799.00, 499.00, 4.90, 19200, ['Paperback'], 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600']
];

// Generate products up to 105
$currentId = 2;
while ($currentId <= 105) {
    $tplIndex = ($currentId - 2) % count($productTemplates);
    $tpl = $productTemplates[$tplIndex];
    $iteration = floor(($currentId - 2) / count($productTemplates)) + 1;
    $suffix = $iteration > 1 ? " (Series {$iteration})" : "";
    
    $title = $tpl[0] . $suffix;
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . "-{$currentId}";
    $brandId = $tpl[1];
    $catId = $tpl[2];
    $sellerId = $tpl[3];
    $mrp = $tpl[4];
    $price = $tpl[5];
    $rating = $tpl[6];
    $reviews = $tpl[7] + ($currentId * 3);
    $colors = $tpl[8];
    $img = $tpl[9];
    
    $vars = [];
    foreach ($colors as $idx => $c) {
        $sku = strtoupper(substr($slug, 0, 8)) . "-{$currentId}-" . ($idx + 1);
        $vars[] = [
            'sku' => $sku,
            'title' => "{$title} - {$c}",
            'color' => $c,
            'mrp' => $mrp,
            'price' => $price,
            'stock' => 50 + ($currentId % 40),
            'img' => $img
        ];
    }
    
    $productsData[] = [
        'id' => $currentId,
        'title' => $title,
        'slug' => $slug,
        'brand_id' => $brandId,
        'category_id' => $catId,
        'seller_id' => $sellerId,
        'description' => "Experience premium quality with {$title}. Designed with superior craftsmanship, durable materials and high performance for long-lasting everyday satisfaction.",
        'highlights' => json_encode([
            'Top rated genuine product with verified brand warranty',
            'Fast doorstep delivery across all India pincodes',
            '7 Days easy return and replacement guarantee',
            'Special bank offers and cashback eligible'
        ]),
        'specifications' => json_encode([
            'Brand' => 'Verified Official Brand',
            'Model' => $title,
            'Warranty' => '1 Year Manufacturer Warranty',
            'Country of Origin' => 'India'
        ]),
        'base_mrp' => $mrp,
        'base_price' => $price,
        'is_featured' => ($currentId <= 15 ? 1 : 0),
        'rating' => $rating,
        'review_count' => $reviews,
        'variants' => $vars,
        'images' => [
            ['url' => $img, 'is_primary' => 1]
        ]
    ];
    $currentId++;
}

// Build Products, Variants, Images, Inventory SQL
$prodRows = [];
$variantRows = [];
$imageRows = [];
$invRows = [];
$attrRows = [];

$vGlobalId = 1;
$imgGlobalId = 1;
$invGlobalId = 1;

foreach ($productsData as $p) {
    $escapedTitle = addslashes($p['title']);
    $escapedDesc = addslashes($p['description']);
    $escapedHighlights = addslashes($p['highlights']);
    $escapedSpecs = addslashes($p['specifications']);
    
    $prodRows[] = "({$p['id']}, '{$escapedTitle}', '{$p['slug']}', {$p['brand_id']}, {$p['category_id']}, {$p['seller_id']}, '{$escapedDesc}', '{$escapedHighlights}', '{$escapedSpecs}', '1 Year Warranty', '7 Days Replacement', {$p['base_mrp']}, {$p['base_price']}, {$p['is_featured']}, 1, 'APPROVED', {$p['rating']}, {$p['review_count']})";
    
    foreach ($p['variants'] as $v) {
        $vTitle = addslashes($v['title']);
        $vColor = addslashes($v['color']);
        $variantRows[] = "({$vGlobalId}, {$p['id']}, '{$v['sku']}', '{$vTitle}', '{$vColor}', NULL, NULL, NULL, NULL, NULL, {$v['mrp']}, {$v['price']}, {$v['stock']}, '{$v['img']}', 1)";
        $invRows[] = "({$invGlobalId}, {$p['id']}, {$vGlobalId}, {$p['seller_id']}, {$v['stock']}, 0, 5)";
        $vGlobalId++;
        $invGlobalId++;
    }
    
    foreach ($p['images'] as $idx => $im) {
        $imageRows[] = "({$imgGlobalId}, {$p['id']}, NULL, '{$im['url']}', {$im['is_primary']}, {$idx})";
        $imgGlobalId++;
    }
    
    $attrRows[] = "({$p['id']}, 'Brand', 'Original Authentic', 1)";
    $attrRows[] = "({$p['id']}, 'Warranty', '1 Year', 2)";
}

$sql[] = "INSERT INTO `products` (`id`, `title`, `slug`, `brand_id`, `category_id`, `seller_id`, `description`, `highlights`, `specifications`, `warranty`, `return_policy`, `base_mrp`, `base_price`, `is_featured`, `is_active`, `status`, `rating`, `review_count`) VALUES\n" . implode(",\n", $prodRows) . "\nON DUPLICATE KEY UPDATE `title`=VALUES(`title`);";

$sql[] = "INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `title`, `color`, `size`, `storage`, `ram`, `model`, `weight`, `mrp`, `price`, `stock`, `image_url`, `is_active`) VALUES\n" . implode(",\n", $variantRows) . "\nON DUPLICATE KEY UPDATE `title`=VALUES(`title`);";

$sql[] = "INSERT INTO `product_images` (`id`, `product_id`, `variant_id`, `image_url`, `is_primary`, `sort_order`) VALUES\n" . implode(",\n", $imageRows) . "\nON DUPLICATE KEY UPDATE `image_url`=VALUES(`image_url`);";

$sql[] = "INSERT INTO `inventory` (`id`, `product_id`, `variant_id`, `seller_id`, `quantity`, `reserved_quantity`, `low_stock_threshold`) VALUES\n" . implode(",\n", $invRows) . "\nON DUPLICATE KEY UPDATE `quantity`=VALUES(`quantity`);";

// 12. Reviews for Sample Product #1
$sql[] = "INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `is_verified_purchase`, `status`, `helpful_count`) VALUES
(1, 1, 4, NULL, 5, 'Exceptional fit and solid military protection!', 'The case fits my iPhone 15 like a glove! Tactile buttons feel super responsive and the matte black finish does not attract fingerprint smudges. MagSafe magnets are very strong.', 1, 'APPROVED', 42),
(2, 1, 5, NULL, 5, 'Best MagSafe cover for iPhone 15 at this price point', 'Super shockproof edges and raised camera ring keeps my lenses completely safe when placed on table. Wireless charging works flawlessly.', 1, 'APPROVED', 28),
(3, 1, 1, NULL, 4, 'Very durable and comfortable grip', 'Good build quality and matte texture provides steady non-slippery grip in hand.', 1, 'APPROVED', 15)
ON DUPLICATE KEY UPDATE `comment`=VALUES(`comment`);";

// 13. Demo Order for Customer #4 with tracking history
$sql[] = "INSERT INTO `orders` (`id`, `order_number`, `user_id`, `seller_id`, `status`, `total_mrp`, `total_discount`, `coupon_discount`, `delivery_charge`, `tax_amount`, `total_payable`, `payment_status`, `payment_method`, `expected_delivery_date`) VALUES
(1, 'OD20260910001', 4, 1, 'CONFIRMED', 1499.00, 1000.00, 100.00, 0.00, 0.00, 399.00, 'PAID', 'RAZORPAY', '2026-09-14')
ON DUPLICATE KEY UPDATE `order_number`=VALUES(`order_number`);";

$sql[] = "INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `seller_id`, `product_title`, `variant_title`, `sku`, `price`, `mrp`, `quantity`, `total_price`, `status`) VALUES
(1, 1, 1, 1, 1, 'Celvas Back Cover for Apple iPhone 15', 'Celvas iPhone 15 Cover - Midnight Black', 'CEL-IP15-BLK', 499.00, 1499.00, 1, 499.00, 'CONFIRMED')
ON DUPLICATE KEY UPDATE `product_title`=VALUES(`product_title`);";

$sql[] = "INSERT INTO `order_addresses` (`id`, `order_id`, `full_name`, `phone`, `pincode`, `address_line1`, `address_line2`, `landmark`, `city`, `state`, `address_type`) VALUES
(1, 1, 'Rahul Sharma', '9876543210', '560034', '#402, Green Glen Layout, Bellandur', 'Outer Ring Road', 'Near Central Mall', 'Bengaluru', 'Karnataka', 'HOME')
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);";

$sql[] = "INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `updated_by_user_id`) VALUES
(1, 1, 'PLACED', 'Order placed successfully by customer via Razorpay', 4),
(2, 1, 'CONFIRMED', 'Payment confirmed (Razorpay pay_demo_1001). Inventory reserved.', 1)
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);";

$sql[] = "INSERT INTO `payments` (`id`, `order_id`, `user_id`, `payment_method`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `amount`, `currency`, `status`) VALUES
(1, 1, 4, 'RAZORPAY', 'order_demo_1001', 'pay_demo_1001', 'sig_verified_demo_mock_12345', 399.00, 'INR', 'CAPTURED')
ON DUPLICATE KEY UPDATE `amount`=VALUES(`amount`);";

// 14. Settings
$sql[] = "INSERT INTO `settings` (`key`, `value`, `group_name`, `description`) VALUES
('site_name', 'Flipkart Shopping Platform', 'GENERAL', 'Brand name of platform'),
('support_email', 'support@flipkart-platform.local', 'GENERAL', 'Customer support email'),
('support_phone', '1800 202 9898', 'GENERAL', 'Toll-free customer care number'),
('razorpay_key_id', 'rzp_test_1DP5mmOlF5G5ag', 'PAYMENT', 'Razorpay Test Key ID'),
('razorpay_key_secret', 's9P7Wj9Q9Z8X7V6U5T4S3R2Q', 'PAYMENT', 'Razorpay Test Secret Key'),
('currency_symbol', '₹', 'CURRENCY', 'Indian Rupee symbol'),
('free_delivery_threshold', '499.00', 'SHIPPING', 'Minimum cart value for free delivery'),
('standard_delivery_fee', '40.00', 'SHIPPING', 'Standard delivery fee below threshold')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);";

$sql[] = "SET FOREIGN_KEY_CHECKS = 1;";

$finalSql = implode("\n\n", $sql);
file_put_contents(__DIR__ . '/seed.sql', $finalSql);
echo "Successfully generated database/seed.sql (" . strlen($finalSql) . " bytes)\n";
