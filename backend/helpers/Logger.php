<?php
/**
 * Admin Log and System Logger Helper
 */

require_once __DIR__ . '/../config/database.php';

class Logger {
    public static function logAdmin(int $userId, string $action, string $module, ?int $recordId = null, $oldValues = null, $newValues = null): void {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("INSERT INTO `admin_logs` 
                (`user_id`, `action`, `module`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $stmt->execute([
                $userId,
                $action,
                $module,
                $recordId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ip,
                $ua
            ]);
        } catch (Exception $e) {
            error_log("Failed to write admin log: " . $e->getMessage());
        }
    }
}
