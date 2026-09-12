<?php
require_once __DIR__ . '/../backend/config/database.php';
$db = Database::getConnection();

// 1. Get role IDs
$rolesStmt = $db->query("SELECT id, name FROM roles");
$roles = [];
while ($r = $rolesStmt->fetch()) {
    $roles[$r['name']] = (int)$r['id'];
}
echo "Available roles: " . json_encode($roles) . "\n";

// Password hash for all accounts: Customer@12345
$pwdHash = password_hash('Customer@12345', PASSWORD_BCRYPT);

// 2. Ensure User 1: 8000000001 (CUSTOMER)
$stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute(['8000000001']);
$u1 = $stmt->fetch();
if (!$u1) {
    $db->prepare("INSERT INTO users (role_id, name, email, phone, password_hash, is_active, is_verified) 
                  VALUES (?, ?, ?, ?, ?, 1, 1)")
       ->execute([$roles['CUSTOMER'], 'Murugan Customer', 'user8000000001@flipkart.local', '8000000001', $pwdHash]);
    echo "Created CUSTOMER user with phone 8000000001\n";
} else {
    $db->prepare("UPDATE users SET role_id = ?, is_active = 1, is_verified = 1 WHERE phone = ?")
       ->execute([$roles['CUSTOMER'], '8000000001']);
    echo "Updated CUSTOMER user with phone 8000000001\n";
}

// 3. Ensure User 2: 9000000001 (ADMIN)
$stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute(['9000000001']);
$u2 = $stmt->fetch();
if (!$u2) {
    $db->prepare("INSERT INTO users (role_id, name, email, phone, password_hash, is_active, is_verified) 
                  VALUES (?, ?, ?, ?, ?, 1, 1)")
       ->execute([$roles['ADMIN'] ?? $roles['SUPER_ADMIN'], 'Flipkart Administrator', 'admin9000000001@flipkart.local', '9000000001', $pwdHash]);
    echo "Created ADMIN user with phone 9000000001\n";
} else {
    $db->prepare("UPDATE users SET role_id = ?, is_active = 1, is_verified = 1 WHERE phone = ?")
       ->execute([$roles['ADMIN'] ?? $roles['SUPER_ADMIN'], '9000000001']);
    echo "Updated ADMIN user with phone 9000000001\n";
}

// 4. Ensure User 3: 9876543210 (SUPER_ADMIN)
$stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute(['9876543210']);
$u3 = $stmt->fetch();
if (!$u3) {
    $db->prepare("INSERT INTO users (role_id, name, email, phone, password_hash, is_active, is_verified) 
                  VALUES (?, ?, ?, ?, ?, 1, 1)")
       ->execute([$roles['SUPER_ADMIN'], 'Super Administrator', 'superadmin@flipkart.local', '9876543210', $pwdHash]);
    echo "Created SUPER_ADMIN user with phone 9876543210\n";
} else {
    $db->prepare("UPDATE users SET role_id = ?, name = 'Super Administrator', is_active = 1, is_verified = 1 WHERE phone = ?")
       ->execute([$roles['SUPER_ADMIN'], '9876543210']);
    echo "Updated SUPER_ADMIN user with phone 9876543210\n";
}

// Verify state
echo "\n--- VERIFIED USERS IN DATABASE ---\n";
$stmt = $db->query("SELECT u.id, u.name, u.email, u.phone, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.phone IN ('8000000001', '9000000001', '9876543210')");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "Phone: {$row['phone']} | Name: {$row['name']} | Role: {$row['role']} | Email: {$row['email']}\n";
}
