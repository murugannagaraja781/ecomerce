<?php
/**
 * Environment File (.env) Manager & Parser Helper
 * Provides atomic read/write of .env files and runtime environment synchronization.
 */

class Env {
    private static array $cache = [];
    private static ?string $envFilePath = null;

    public static function getEnvPath(): string {
        if (self::$envFilePath === null) {
            self::$envFilePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        }
        return self::$envFilePath;
    }

    /**
     * Load environment variables from .env file into $_ENV and getenv()
     */
    public static function load(?string $path = null): void {
        $file = $path ?: self::getEnvPath();
        if (!file_exists($file)) {
            $example = dirname($file) . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($example)) {
                copy($example, $file);
            } else {
                return;
            }
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Strip surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                // Handle booleans / nulls
                $lower = strtolower($value);
                if ($lower === 'true') $value = true;
                elseif ($lower === 'false') $value = false;
                elseif ($lower === 'null') $value = null;

                self::$cache[$key] = $value;
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Force reload environment variables from .env file, clearing cache
     */
    public static function reload(?string $path = null): void {
        self::$cache = [];
        self::load($path);
    }

    /**
     * Get an environment variable with default fallback
     */
    public static function get(string $key, mixed $default = null): mixed {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        return $default;
    }

    /**
     * Read all key-value pairs from .env
     */
    public static function all(): array {
        self::load();
        return self::$cache;
    }

    /**
     * Read raw .env file text
     */
    public static function getRaw(): string {
        $file = self::getEnvPath();
        if (!file_exists($file)) {
            $example = dirname($file) . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($example)) {
                return file_get_contents($example) ?: '';
            }
            return '';
        }
        return file_get_contents($file) ?: '';
    }

    /**
     * Atomically write raw string to .env
     */
    public static function writeRaw(string $content): bool {
        $file = self::getEnvPath();
        $tmp = $file . '.tmp.' . bin2hex(random_bytes(4));
        if (file_put_contents($tmp, $content) === false) {
            return false;
        }
        $success = rename($tmp, $file);
        if ($success) {
            self::$cache = [];
            self::load($file);
        }
        return $success;
    }

    /**
     * Atomically update specific keys in .env preserving formatting & comments
     */
    public static function update(array $newValues): bool {
        $file = self::getEnvPath();
        $content = self::getRaw();
        $lines = explode("\n", $content);
        $updatedKeys = [];
        $newLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                $newLines[] = $line;
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $origVal] = explode('=', $line, 2);
                $key = trim($key);
                if (array_key_exists($key, $newValues)) {
                    $val = $newValues[$key];
                    if (is_bool($val)) {
                        $valStr = $val ? 'true' : 'false';
                    } elseif ($val === null) {
                        $valStr = '';
                    } else {
                        $valStr = (string)$val;
                        // Quote if contains spaces or special characters
                        if (preg_match('/\s/', $valStr) && !str_starts_with($valStr, '"')) {
                            $valStr = "\"{$valStr}\"";
                        }
                    }
                    $newLines[] = "{$key}={$valStr}";
                    $updatedKeys[$key] = true;
                } else {
                    $newLines[] = $line;
                }
            } else {
                $newLines[] = $line;
            }
        }

        // Append any brand new keys not present in existing file
        foreach ($newValues as $k => $v) {
            if (!isset($updatedKeys[$k])) {
                $valStr = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
                if (preg_match('/\s/', $valStr) && !str_starts_with($valStr, '"')) {
                    $valStr = "\"{$valStr}\"";
                }
                $newLines[] = "{$k}={$valStr}";
            }
        }

        return self::writeRaw(implode("\n", $newLines));
    }
}
