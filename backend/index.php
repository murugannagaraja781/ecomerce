<?php
/**
 * Main Front Controller - Flipkart Shopping Platform API
 */

require_once __DIR__ . '/config/environment.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/routes/Router.php';

// Handle CORS globally before any route processing
CorsMiddleware::handle();

$router = new Router();

// System Status & Healthcheck
$router->get('/api/health', function() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'flipkartdb'");
        $tables = $stmt->fetch()['table_count'];

        Response::success([
            'platform'    => APP_NAME,
            'version'     => '1.0.0',
            'environment' => APP_ENV,
            'status'      => 'healthy',
            'database'    => [
                'status'       => 'connected',
                'database'     => DB_NAME,
                'tables_count' => (int)$tables
            ],
            'timestamp'   => date('Y-m-d H:i:s')
        ], "API is running smoothly");
    } catch (Exception $e) {
        Response::error("System health check failed: " . $e->getMessage(), [], 500);
    }
});

// Load feature routes (Auth, Catalog, Cart, Orders, Seller, Admin, etc.)
if (file_exists(__DIR__ . '/routes/api.php')) {
    require_once __DIR__ . '/routes/api.php';
}

// Dispatch incoming request
$router->dispatch();
