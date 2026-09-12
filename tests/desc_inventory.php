<?php
require_once __DIR__ . '/../backend/config/database.php';
$db = Database::getConnection();
$stmt = $db->query("DESCRIBE inventory");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
