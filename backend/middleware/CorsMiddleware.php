<?php
/**
 * Cross-Origin Resource Sharing (CORS) Middleware
 */

class CorsMiddleware {
    private static array $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://localhost:8080',
        'http://localhost:5000',
        'http://localhost:5173',
        'http://localhost:8000'
    ];

    public static function handle(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $isAllowed = false;

        if (!empty($origin)) {
            $parsed = parse_url($origin);
            $host = $parsed['host'] ?? '';
            $scheme = $parsed['scheme'] ?? 'http';
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            $originNormalized = "{$scheme}://{$host}{$port}";

            if (in_array($originNormalized, self::$allowedOrigins, true) ||
                $host === 'localhost' ||
                $host === '127.0.0.1' ||
                preg_match('/^192\.168\.\d+\.\d+$/', $host) ||
                preg_match('/^10\.\d+\.\d+\.\d+$/', $host)) {
                $isAllowed = true;
            }
        }

        if ($isAllowed) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Credentials: true");
        } else {
            // Safe fallback: omit origin header or set to self
            header("Access-Control-Allow-Origin: null");
        }

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-Id, Accept, Origin, X-Razorpay-Signature");
        header("Access-Control-Max-Age: 86400");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
