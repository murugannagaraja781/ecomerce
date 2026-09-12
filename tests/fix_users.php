<?php
require_once __DIR__ . '/../backend/config/database.php';
$db = Database::getConnection();

// 1. Get role IDs
$rolesStmt = $db->query("SELECT id, name FROM roles");
$roles = [];
while ($r = $rolesStmt->fetch()) {
    $roles[$r['name']] = (int)$r['id'];
}

$pwdHash = password_hash('Customer@12345', PASSWORD_BCRYPT);
$adminPwdHash = password_hash('Admin@12345', PASSWORD_BCRYPT);

// Remove extra user 6
$db->exec("DELETE FROM users WHERE email = 'user8000000001@flipkart.local'");

// User ID 4: customer@gmail.com -> CUSTOMER, phone 8000000001
$db->prepare("UPDATE users SET role_id = ?, name = 'Rahul Sharma (Customer)', phone = '8000000001', password_hash = ? WHERE email = 'customer@gmail.com'")
   ->execute([$roles['CUSTOMER'], $pwdHash]);

// User ID 1: admin@flipkart.local -> SUPER_ADMIN, phone 9876543210
$db->prepare("UPDATE users SET role_id = ?, name = 'Super Administrator', phone = '9876543210', password_hash = ? WHERE email = 'admin@flipkart.local'")
   ->execute([$roles['SUPER_ADMIN'], $adminPwdHash]);

// Dedicated Admin user with 9000000001
$db->prepare("UPDATE users SET role_id = ?, name = 'Flipkart Administrator', phone = '9000000001', password_hash = ? WHERE phone = '9000000001'")
   ->execute([$roles['ADMIN'] ?? $roles['SUPER_ADMIN'], $adminPwdHash]);

echo "Users updated successfully.\n";
$stmt = $db->query("SELECT u.id, u.name, u.email, u.phone, r.name as role FROM users u JOIN roles r ON u.role_id = r.id");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "ID: {$u['id']} | Name: {$u['name']} | Email: {$u['email']} | Phone: {$u['phone']} | Role: {$u['role']}\n";
}
