<?php
/**
 * Seed & Initialize White-Label Branding Settings
 */
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/Env.php';

$pdo = getDbConnection();

$defaults = [
    'site_name'         => ['value' => 'Flipkart', 'group' => 'BRANDING', 'desc' => 'Platform / Storefront Display Name'],
    'site_logo_url'     => ['value' => '', 'group' => 'BRANDING', 'desc' => 'Header Brand Logo Image URL'],
    'site_favicon_url'  => ['value' => 'https://static-assets-web.flixcart.com/batman-returns/batman-returns/p/images/logo_lite-cbb357.png', 'group' => 'BRANDING', 'desc' => 'Browser Favicon URL'],
    'site_tagline'      => ['value' => 'Explore Plus ✦', 'group' => 'BRANDING', 'desc' => 'Header Subtitle / Tagline'],
    'primary_color'     => ['value' => '#2874F0', 'group' => 'BRANDING', 'desc' => 'Primary Brand Theme Color (Hex)'],
    'secondary_color'   => ['value' => '#FB641B', 'group' => 'BRANDING', 'desc' => 'Secondary Accent Action Color (Hex)'],
    'support_email'     => ['value' => 'support@flipkart.local', 'group' => 'CONTACT', 'desc' => 'Customer Support Email Address'],
    'support_phone'     => ['value' => '1800 202 9898', 'group' => 'CONTACT', 'desc' => 'Customer Support Toll-Free Helpline'],
    'currency_symbol'   => ['value' => '₹', 'group' => 'LOCALIZATION', 'desc' => 'Storefront Currency Symbol'],
    'footer_copyright'  => ['value' => '© 2026 Flipkart E-Commerce Platform. All Rights Reserved.', 'group' => 'BRANDING', 'desc' => 'Footer Copyright Notice']
];

$stmt = $pdo->prepare("INSERT INTO `settings` (`key`, `value`, `group_name`, `description`) 
    VALUES (?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)");

foreach ($defaults as $key => $info) {
    $stmt->execute([$key, $info['value'], $info['group'], $info['desc']]);
}

echo "✅ White-label branding settings initialized successfully in MySQL flipkartdb settings table.\n";
