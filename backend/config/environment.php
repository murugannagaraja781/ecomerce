<?php
/**
 * Environment Configuration Loader
 * Dynamically loads .env with fallback defaults
 */

require_once dirname(__DIR__) . '/helpers/Env.php';
Env::load();

// Application Configuration
define('APP_ENV', Env::get('APP_ENV', 'development'));
define('APP_DEBUG', (bool)Env::get('APP_DEBUG', true));
define('APP_NAME', Env::get('APP_NAME', 'Flipkart Shopping Platform API'));
define('APP_URL', Env::get('APP_URL', 'http://localhost/ecommerce_api'));
define('APP_CURRENCY', Env::get('APP_CURRENCY', 'INR'));

// Database Configuration
define('DB_HOST', Env::get('DB_HOST', '127.0.0.1'));
define('DB_PORT', Env::get('DB_PORT', '3306'));
define('DB_NAME', Env::get('DB_NAME', 'flipkartdb'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASS', ''));
define('DB_CHARSET', Env::get('DB_CHARSET', 'utf8mb4'));

// Security & JWT
define('JWT_SECRET', Env::get('JWT_SECRET', 'flipkart_ultra_secure_jwt_secret_key_2026_9831749817293847'));
define('JWT_ACCESS_EXPIRY', (int)Env::get('JWT_ACCESS_EXPIRY', 86400 * 7));
define('JWT_REFRESH_EXPIRY', (int)Env::get('JWT_REFRESH_EXPIRY', 86400 * 30));

// Active Payment Gateway
define('ACTIVE_PAYMENT_GATEWAY', Env::get('ACTIVE_PAYMENT_GATEWAY', 'razorpay'));

// Razorpay
define('RAZORPAY_KEY_ID', Env::get('RAZORPAY_KEY_ID', 'rzp_test_1DP5mmOlF5G5ag'));
define('RAZORPAY_KEY_SECRET', Env::get('RAZORPAY_KEY_SECRET', 's9P7Wj9Q9Z8X7V6U5T4S3R2Q'));
define('RAZORPAY_WEBHOOK_SECRET', Env::get('RAZORPAY_WEBHOOK_SECRET', 'whsec_flipkart_rzp_live_2026'));

// Cashfree
define('CASHFREE_APP_ID', Env::get('CASHFREE_APP_ID', ''));
define('CASHFREE_SECRET_KEY', Env::get('CASHFREE_SECRET_KEY', ''));
define('CASHFREE_ENV', Env::get('CASHFREE_ENV', 'SANDBOX'));

// PhonePe
define('PHONEPE_MERCHANT_ID', Env::get('PHONEPE_MERCHANT_ID', ''));
define('PHONEPE_SALT_KEY', Env::get('PHONEPE_SALT_KEY', ''));
define('PHONEPE_SALT_INDEX', Env::get('PHONEPE_SALT_INDEX', '1'));
define('PHONEPE_ENV', Env::get('PHONEPE_ENV', 'UAT'));

// Paytm
define('PAYTM_MID', Env::get('PAYTM_MID', ''));
define('PAYTM_MERCHANT_KEY', Env::get('PAYTM_MERCHANT_KEY', ''));
define('PAYTM_WEBSITE', Env::get('PAYTM_WEBSITE', 'WEBSTAGING'));
define('PAYTM_ENV', Env::get('PAYTM_ENV', 'TEST'));

// PayU
define('PAYU_MERCHANT_KEY', Env::get('PAYU_MERCHANT_KEY', ''));
define('PAYU_SALT', Env::get('PAYU_SALT', ''));
define('PAYU_ENV', Env::get('PAYU_ENV', 'TEST'));

// Active SMS Provider
define('ACTIVE_SMS_PROVIDER', Env::get('ACTIVE_SMS_PROVIDER', 'local'));

// Fast2SMS
define('FAST2SMS_API_KEY', Env::get('FAST2SMS_API_KEY', ''));
define('FAST2SMS_SENDER_ID', Env::get('FAST2SMS_SENDER_ID', 'FSTSMS'));

// MSG91
define('MSG91_AUTH_KEY', Env::get('MSG91_AUTH_KEY', ''));
define('MSG91_TEMPLATE_ID', Env::get('MSG91_TEMPLATE_ID', ''));
define('MSG91_SENDER_ID', Env::get('MSG91_SENDER_ID', 'FLPKRT'));

// Twilio
define('TWILIO_ACCOUNT_SID', Env::get('TWILIO_ACCOUNT_SID', ''));
define('TWILIO_AUTH_TOKEN', Env::get('TWILIO_AUTH_TOKEN', ''));
define('TWILIO_PHONE_NUMBER', Env::get('TWILIO_PHONE_NUMBER', ''));

// Textlocal
define('TEXTLOCAL_API_KEY', Env::get('TEXTLOCAL_API_KEY', ''));
define('TEXTLOCAL_SENDER', Env::get('TEXTLOCAL_SENDER', 'TXTLCL'));

// FCM Push Notifications
define('FCM_SERVER_KEY', Env::get('FCM_SERVER_KEY', ''));
define('FCM_PROJECT_ID', Env::get('FCM_PROJECT_ID', ''));
define('FCM_SENDER_ID', Env::get('FCM_SENDER_ID', ''));

// Upload Paths
define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL', APP_URL . '/uploads');
