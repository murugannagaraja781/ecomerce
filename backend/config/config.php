<?php
/**
 * Application Constants and App Settings
 */

require_once __DIR__ . '/environment.php';
require_once __DIR__ . '/database.php';

class AppConfig {
    private static array $settingsCache = [];

    public static function get(string $key, $default = null) {
        if (empty(self::$settingsCache)) {
            self::loadSettings();
        }

        return self::$settingsCache[$key] ?? $default;
    }

    public static function loadSettings(): void {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->query("SELECT `key`, `value` FROM `settings`");
            while ($row = $stmt->fetch()) {
                self::$settingsCache[$row['key']] = $row['value'];
            }
        } catch (Exception $e) {
            // Fallback if settings table query fails
            self::$settingsCache = [];
        }
    }
}
