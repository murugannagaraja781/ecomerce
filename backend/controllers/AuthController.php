<?php
/**
 * Authentication Controller
 * Handles Customer, Seller and Admin login, registration, OTP and token refresh
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/JwtHelper.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/SmsService.php';

class AuthController extends BaseController {

    /**
     * Customer Registration
     */
    public function register(): void {
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('name')
            ->required('email')
            ->email('email')
            ->required('password')
            ->minLength('password', 6);

        if ($validator->fails()) {
            Response::error("Validation failed", $validator->errors(), 422);
        }

        $email = strtolower(trim($data['email']));
        $phone = isset($data['phone']) ? trim($data['phone']) : null;

        // Check duplicate email
        $stmt = $this->db->prepare("SELECT id FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::error("An account with this email address already exists", ['email' => ['Email already registered']], 409);
        }

        // Check duplicate phone if provided
        if ($phone) {
            $stmt = $this->db->prepare("SELECT id FROM `users` WHERE `phone` = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                Response::error("An account with this mobile number already exists", ['phone' => ['Phone number already registered']], 409);
            }
        }

        // Customer role ID is 4
        $roleId = 4;
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $name = trim($data['name']);

        $stmt = $this->db->prepare("INSERT INTO `users` (`role_id`, `name`, `email`, `phone`, `password_hash`, `is_active`, `is_verified`) VALUES (?, ?, ?, ?, ?, 1, 1)");
        $stmt->execute([$roleId, $name, $email, $phone, $passwordHash]);
        $userId = (int)$this->db->lastInsertId();

        // Create empty Cart and Wishlist for user
        $this->db->prepare("INSERT INTO `carts` (`user_id`) VALUES (?)")->execute([$userId]);
        $this->db->prepare("INSERT INTO `wishlists` (`user_id`) VALUES (?)")->execute([$userId]);

        $tokenPayload = [
            'user_id'   => $userId,
            'email'     => $email,
            'name'      => $name,
            'role_name' => 'CUSTOMER'
        ];

        $accessToken = JwtHelper::generateToken($tokenPayload);
        $refreshToken = JwtHelper::generateRefreshToken();

        // Store refresh token
        $exp = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRY);
        $this->db->prepare("INSERT INTO `user_tokens` (`user_id`, `refresh_token`, `device_name`, `expires_at`) VALUES (?, ?, ?, ?)")
            ->execute([$userId, $refreshToken, $_SERVER['HTTP_USER_AGENT'] ?? 'App', $exp]);

        Response::success([
            'user' => [
                'id'        => $userId,
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'role'      => 'CUSTOMER',
                'avatar_url'=> null
            ],
            'tokens' => [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => JWT_ACCESS_EXPIRY
            ]
        ], "Registration successful! Welcome to Flipkart.", 201);
    }

    /**
     * Customer Login
     */
    public function login(): void {
        $this->authenticateUserByRole(['CUSTOMER']);
    }

    /**
     * Seller Login
     */
    public function sellerLogin(): void {
        $this->authenticateUserByRole(['SELLER']);
    }

    /**
     * Admin Login
     */
    public function adminLogin(): void {
        $this->authenticateUserByRole(['ADMIN', 'SUPER_ADMIN', 'SUPPORT_AGENT']);
    }

    /**
     * Unified Role-Based Login Logic
     */
    private function authenticateUserByRole(array $allowedRoles): void {
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('email')
            ->required('password');

        if ($validator->fails()) {
            Response::error("Invalid login credentials provided", $validator->errors(), 422);
        }

        $emailOrPhone = trim($data['email']);
        $password = (string)$data['password'];

        $stmt = $this->db->prepare("SELECT u.*, r.name as role_name 
            FROM `users` u 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE (u.email = ? OR u.phone = ?) AND u.deleted_at IS NULL");
        $stmt->execute([$emailOrPhone, $emailOrPhone]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error("Incorrect email/phone or password. Please try again.", [], 401);
        }

        if (!$user['is_active']) {
            Response::error("Your account has been deactivated. Please contact support.", [], 403);
        }

        if (!in_array($user['role_name'], $allowedRoles)) {
            Response::error("You do not have authorization to access this portal with this account.", [], 403);
        }

        $extraData = [];
        if ($user['role_name'] === 'SELLER') {
            $sStmt = $this->db->prepare("SELECT * FROM `sellers` WHERE `user_id` = ? AND `deleted_at` IS NULL");
            $sStmt->execute([$user['id']]);
            $seller = $sStmt->fetch();
            if ($seller) {
                $extraData['seller'] = $seller;
            }
        }

        $tokenPayload = [
            'user_id'   => (int)$user['id'],
            'email'     => $user['email'],
            'name'      => $user['name'],
            'role_name' => $user['role_name']
        ];

        $accessToken = JwtHelper::generateToken($tokenPayload);
        $refreshToken = JwtHelper::generateRefreshToken();

        // Save refresh token
        $exp = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRY);
        $this->db->prepare("INSERT INTO `user_tokens` (`user_id`, `refresh_token`, `device_name`, `expires_at`) VALUES (?, ?, ?, ?)")
            ->execute([$user['id'], $refreshToken, $_SERVER['HTTP_USER_AGENT'] ?? 'Device', $exp]);

        Response::success([
            'user' => [
                'id'        => (int)$user['id'],
                'name'      => $user['name'],
                'email'     => $user['email'],
                'phone'     => $user['phone'],
                'role'      => $user['role_name'],
                'avatar_url'=> $user['avatar_url']
            ],
            'extra' => $extraData,
            'tokens' => [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => JWT_ACCESS_EXPIRY
            ]
        ], "Login successful. Welcome back, " . htmlspecialchars($user['name']));
    }

    /**
     * Send OTP to phone or email
     */
    public function sendOtp(): void {
        $data = $this->getRequestData();
        $validator = Validator::make($data)->required('phone');

        if ($validator->fails()) {
            Response::error("Please provide a valid phone number", $validator->errors(), 422);
        }

        $phone = trim($data['phone']);
        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            Response::error("Phone number must be between 10 and 15 digits", ['phone' => ['Invalid phone format']], 422);
        }

        // Predefined demo OTP mapping as requested by client
        if ($phone === '8000000001' || $phone === '9000000001') {
            $otp = '123456';
        } elseif ($phone === '9876543210') {
            $otp = '1369';
        } else {
            // Cryptographically secure 6-digit OTP
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        }
        $expiry = date('Y-m-d H:i:s', time() + 86400); // 24 hours

        $stmt = $this->db->prepare("UPDATE `users` SET `otp_code` = ?, `otp_expires_at` = ? WHERE `phone` = ?");
        $stmt->execute([$otp, $expiry, $phone]);

        // Dispatch via Active SMS Provider (Fast2SMS / MSG91 / Twilio / Textlocal / Local)
        $smsResult = SmsService::sendOtp($phone, $otp);

        $responseData = [
            'phone'        => $phone,
            'expires_in'   => 86400,
            'demo_otp'     => $otp,
            'sms_provider' => $smsResult['provider'] ?? 'local',
            'sms_status'   => $smsResult['success'] ?? true,
            'message'      => $smsResult['message'] ?? "OTP sent successfully. Demo OTP: {$otp}"
        ];

        Response::success($responseData, "OTP generated and dispatched");
    }

    /**
     * Verify OTP and log in
     */
    public function verifyOtp(): void {
        $data = $this->getRequestData();
        $validator = Validator::make($data)
            ->required('phone')
            ->required('otp');

        if ($validator->fails()) {
            Response::error("Validation failed", $validator->errors(), 422);
        }

        $phone = trim($data['phone']);
        $otp = trim($data['otp']);

        $stmt = $this->db->prepare("SELECT u.*, r.name as role_name 
            FROM `users` u 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE u.phone = ? AND u.deleted_at IS NULL");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::error("No account found with this phone number", [], 404);
        }

        // Check demo configured OTPs first
        $isDemoMatch = false;
        if (($phone === '8000000001' || $phone === '9000000001') && $otp === '123456') {
            $isDemoMatch = true;
        } elseif ($phone === '9876543210' && ($otp === '1369' || $otp === '136900')) {
            $isDemoMatch = true;
        }

        if (!$isDemoMatch) {
            // Verify OTP is present and valid
            if (empty($user['otp_code']) || empty($user['otp_expires_at'])) {
                Response::error("No active OTP requested for this account. Please request a new OTP.", [], 400);
            }

            // Check expiration
            if (strtotime($user['otp_expires_at']) < time()) {
                Response::error("The OTP has expired. Please request a new OTP.", [], 400);
            }

            // Constant-time OTP comparison
            if (!hash_equals((string)$user['otp_code'], (string)$otp)) {
                Response::error("Invalid OTP entered. Please try again.", [], 400);
            }
        }

        // Refresh user verification status
        $this->db->prepare("UPDATE `users` SET `is_verified` = 1 WHERE `id` = ?")->execute([$user['id']]);

        $accessToken = JwtHelper::generateToken([
            'user_id'   => (int)$user['id'],
            'email'     => $user['email'],
            'name'      => $user['name'],
            'role_name' => $user['role_name'],
            'role'      => $user['role_name']
        ]);
        $refreshToken = JwtHelper::generateRefreshToken();

        // Save refresh token
        $exp = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRY);
        $this->db->prepare("INSERT INTO `user_tokens` (`user_id`, `refresh_token`, `device_name`, `expires_at`) VALUES (?, ?, ?, ?)")
            ->execute([$user['id'], $refreshToken, $_SERVER['HTTP_USER_AGENT'] ?? 'Device', $exp]);

        Response::success([
            'user' => [
                'id'        => (int)$user['id'],
                'name'      => $user['name'],
                'email'     => $user['email'],
                'phone'     => $user['phone'],
                'role'      => $user['role_name'],
                'avatar_url'=> $user['avatar_url']
            ],
            'tokens' => [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => JWT_ACCESS_EXPIRY
            ]
        ], "OTP verified successfully. Welcome!");
    }

    /**
     * Refresh Access Token
     */
    public function refreshToken(): void {
        $data = $this->getRequestData();
        $validator = Validator::make($data)->required('refresh_token');

        if ($validator->fails()) {
            Response::error("Refresh token is required", $validator->errors(), 422);
        }

        $token = trim($data['refresh_token']);
        $stmt = $this->db->prepare("SELECT t.*, u.name, u.email, u.is_active, r.name as role_name 
            FROM `user_tokens` t 
            JOIN `users` u ON t.user_id = u.id 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE t.refresh_token = ? AND t.expires_at > NOW() AND u.deleted_at IS NULL");
        $stmt->execute([$token]);
        $record = $stmt->fetch();

        if (!$record || !$record['is_active']) {
            Response::error("Invalid or expired refresh token. Please login again.", [], 401);
        }

        $newAccessToken = JwtHelper::generateToken([
            'user_id'   => (int)$record['user_id'],
            'email'     => $record['email'],
            'name'      => $record['name'],
            'role_name' => $record['role_name']
        ]);

        Response::success([
            'access_token' => $newAccessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => JWT_ACCESS_EXPIRY
        ], "Token refreshed successfully");
    }

    /**
     * Logout
     */
    public function logout(): void {
        $user = AuthMiddleware::authenticate(false);
        $data = $this->getRequestData();
        $refreshToken = $data['refresh_token'] ?? null;

        if ($refreshToken) {
            $this->db->prepare("DELETE FROM `user_tokens` WHERE `refresh_token` = ?")->execute([$refreshToken]);
        } elseif ($user) {
            $this->db->prepare("DELETE FROM `user_tokens` WHERE `user_id` = ?")->execute([$user['id']]);
        }

        Response::success(null, "Logged out successfully");
    }

    /**
     * Get Current Authenticated Profile
     */
    public function getProfile(): void {
        $user = AuthMiddleware::authenticate(true);

        // Fetch addresses count, orders count, wishlist count, cart count
        $userId = (int)$user['id'];
        $cartStmt = $this->db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM `cart_items` ci JOIN `carts` c ON ci.cart_id = c.id WHERE c.user_id = ?");
        $cartStmt->execute([$userId]);
        $cartCount = $cartStmt->fetchColumn();

        $wishStmt = $this->db->prepare("SELECT COUNT(*) FROM `wishlist_items` wi JOIN `wishlists` w ON wi.wishlist_id = w.id WHERE w.user_id = ?");
        $wishStmt->execute([$userId]);
        $wishCount = $wishStmt->fetchColumn();

        $orderStmt = $this->db->prepare("SELECT COUNT(*) FROM `orders` WHERE `user_id` = ?");
        $orderStmt->execute([$userId]);
        $orderCount = $orderStmt->fetchColumn();

        Response::success([
            'user' => $user,
            'stats' => [
                'cart_items_count'     => (int)$cartCount,
                'wishlist_items_count' => (int)$wishCount,
                'orders_count'         => (int)$orderCount
            ]
        ], "Profile fetched");
    }

    /**
     * Update Current Profile
     */
    public function updateProfile(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $name = isset($data['name']) ? trim($data['name']) : $user['name'];
        $phone = isset($data['phone']) ? trim($data['phone']) : $user['phone'];
        $avatarUrl = isset($data['avatar_url']) ? trim($data['avatar_url']) : $user['avatar_url'];

        $stmt = $this->db->prepare("UPDATE `users` SET `name` = ?, `phone` = ?, `avatar_url` = ? WHERE `id` = ?");
        $stmt->execute([$name, $phone, $avatarUrl, $user['id']]);

        Response::success([
            'id'        => (int)$user['id'],
            'name'      => $name,
            'email'     => $user['email'],
            'phone'     => $phone,
            'avatar_url'=> $avatarUrl,
            'role'      => $user['role_name']
        ], "Profile updated successfully");
    }
}
