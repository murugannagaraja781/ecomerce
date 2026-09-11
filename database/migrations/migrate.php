<?php
/**
 * Simple Migration Runner for flipkartdb
 */

require_once __DIR__ . '/../../backend/config/database.php';

try {
    $pdo = getDbConnection();
    echo "Connected to MySQL successfully.\n";

    // Create migrations table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL UNIQUE,
        `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $migrationFiles = glob(__DIR__ . '/*.sql');
    sort($migrationFiles);

    foreach ($migrationFiles as $file) {
        $basename = basename($file);
        $stmt = $pdo->prepare("SELECT id FROM `migrations` WHERE `migration` = ?");
        $stmt->execute([$basename]);
        if ($stmt->fetch()) {
            echo "Skipping already applied migration: {$basename}\n";
            continue;
        }

        echo "Applying migration: {$basename} ...\n";
        $sql = file_get_contents($file);
        $pdo->exec($sql);

        $ins = $pdo->prepare("INSERT INTO `migrations` (`migration`) VALUES (?)");
        $ins->execute([$basename]);
        echo "Successfully applied: {$basename}\n";
    }

    echo "All migrations completed.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
