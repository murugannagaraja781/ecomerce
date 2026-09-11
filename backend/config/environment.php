<?php
/**
 * Environment Configuration
 */

define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_NAME', 'Flipkart Shopping Platform API');
define('APP_URL', 'http://localhost/ecommerce_api');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'flipkartdb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security & JWT
define('JWT_SECRET', 'flipkart_ultra_secure_jwt_secret_key_2026_9831749817293847');
define('JWT_ACCESS_EXPIRY', 86400 * 7); // 7 days access token
define('JWT_REFRESH_EXPIRY', 86400 * 30); // 30 days refresh token

// Razorpay Test Credentials (configurable from settings table or fallback)
define('RAZORPAY_KEY_ID', 'rzp_test_1DP5mmOlF5G5ag');
define('RAZORPAY_KEY_SECRET', 's9P7Wj9Q9Z8X7V6U5T4S3R2Q');

// Upload Paths
define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL', APP_URL . '/uploads');
