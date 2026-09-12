<?php
require_once __DIR__ . '/../backend/config/database.php';
$db = Database::getConnection();
$stmt = $db->query("SELECT u.id, u.name, u.email, u.phone, r.name as role FROM users u JOIN roles r ON u.role_id = r.id");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "ID: {$u['id']} | Name: {$u['name']} | Email: {$u['email']} | Phone: {$u['phone']} | Role: {$u['role']}\n";
}
