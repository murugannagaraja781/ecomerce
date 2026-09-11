<?php
/**
 * Role-Based Access Control Middleware
 */

require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../config/database.php';

class RoleMiddleware {
    public static function authorize(array $allowedRoles): array {
        $user = AuthMiddleware::authenticate(true);

        if (!in_array($user['role_name'], $allowedRoles)) {
            Response::error("Forbidden: You do not have permission to access this resource.", [], 403);
        }

        // If seller role, attach seller profile
        if ($user['role_name'] === 'SELLER') {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT * FROM `sellers` WHERE `user_id` = ? AND `deleted_at` IS NULL");
            $stmt->execute([$user['id']]);
            $seller = $stmt->fetch();

            if (!$seller) {
                Response::error("Seller profile not found for this user account.", [], 404);
            }
            if ($seller['status'] === 'SUSPENDED' || $seller['status'] === 'REJECTED') {
                Response::error("Your seller account status is currently: {$seller['status']}.", [], 403);
            }

            $user['seller'] = $seller;
        }

        return $user;
    }
}
