<?php
require_once __DIR__ . '/../backend/config/database.php';
$pdo = Database::getConnection();
$stmt = $pdo->query("SELECT id, code, min_order_amount, discount_type, discount_value, is_active FROM coupons");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "Coupon: {$c['code']} | Min: {$c['min_order_amount']} | Disc: {$c['discount_value']} ({$c['discount_type']}) | Active: {$c['is_active']}\n";
}
