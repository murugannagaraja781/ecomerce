<?php
/**
 * Authentication Middleware
 */

require_once __DIR__ . '/../helpers/JwtHelper.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../config/database.php';

class AuthMiddleware {
    public static function authenticate(bool $required = true): ?array {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            $payload = JwtHelper::verifyToken($token);

            if ($payload && isset($payload['user_id'])) {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare("SELECT u.id, u.role_id, u.name, u.email, u.phone, u.avatar_url, u.is_active, r.name as role_name 
                    FROM `users` u 
                    JOIN `roles` r ON u.role_id = r.id 
                    WHERE u.id = ? AND u.deleted_at IS NULL");
                $stmt->execute([$payload['user_id']]);
                $user = $stmt->fetch();

                if ($user) {
                    if (!$user['is_active']) {
                        Response::error("Your account has been deactivated. Please contact support.", [], 403);
                    }
                    return $user;
                }
            }
        }

        if ($required) {
            Response::error("Unauthorized access. Token is missing, invalid or expired.", [], 401);
        }

        return null;
    }
}
