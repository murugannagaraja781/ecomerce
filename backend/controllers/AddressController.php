<?php
/**
 * Customer Address Management Controller
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Validator.php';

class AddressController extends BaseController {

    public function getAddresses(): void {
        $user = AuthMiddleware::authenticate(true);
        $stmt = $this->db->prepare("SELECT * FROM `addresses` WHERE `user_id` = ? AND `deleted_at` IS NULL ORDER BY `is_default` DESC, `id` DESC");
        $stmt->execute([$user['id']]);
        Response::success($stmt->fetchAll(), "Addresses retrieved");
    }

    public function createAddress(): void {
        $user = AuthMiddleware::authenticate(true);
        $data = $this->getRequestData();

        $validator = Validator::make($data)
            ->required('full_name')
            ->required('phone')
            ->required('pincode')
            ->required('address_line1')
            ->required('city')
            ->required('state');

        if ($validator->fails()) {
            Response::error("Address validation failed", $validator->errors(), 422);
        }

        $userId = $user['id'];
        $isDefault = !empty($data['is_default']) ? 1 : 0;

        // If default or first address, unset previous defaults
        $existingCount = $this->db->query("SELECT COUNT(*) FROM `addresses` WHERE `user_id` = {$userId} AND `deleted_at` IS NULL")->fetchColumn();
        if ($existingCount == 0) {
            $isDefault = 1;
        } elseif ($isDefault) {
            $this->db->prepare("UPDATE `addresses` SET `is_default` = 0 WHERE `user_id` = ?")->execute([$userId]);
        }

        $stmt = $this->db->prepare("INSERT INTO `addresses` 
            (`user_id`, `full_name`, `phone`, `alternate_phone`, `pincode`, `address_line1`, `address_line2`, `landmark`, `city`, `state`, `address_type`, `is_default`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $userId,
            trim($data['full_name']),
            trim($data['phone']),
            $data['alternate_phone'] ?? null,
            trim($data['pincode']),
            trim($data['address_line1']),
            $data['address_line2'] ?? null,
            $data['landmark'] ?? null,
            trim($data['city']),
            trim($data['state']),
            $data['address_type'] ?? 'HOME',
            $isDefault
        ]);

        $id = (int)$this->db->lastInsertId();
        Response::success(['id' => $id], "Address added successfully", 201);
    }

    public function updateAddress(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $addressId = (int)$params['id'];
        $data = $this->getRequestData();

        $check = $this->db->prepare("SELECT id FROM `addresses` WHERE `id` = ? AND `user_id` = ? AND `deleted_at` IS NULL");
        $check->execute([$addressId, $user['id']]);
        if (!$check->fetch()) {
            Response::error("Address not found", [], 404);
        }

        $isDefault = !empty($data['is_default']) ? 1 : 0;
        if ($isDefault) {
            $this->db->prepare("UPDATE `addresses` SET `is_default` = 0 WHERE `user_id` = ?")->execute([$user['id']]);
        }

        $stmt = $this->db->prepare("UPDATE `addresses` SET
            `full_name` = COALESCE(?, `full_name`),
            `phone` = COALESCE(?, `phone`),
            `alternate_phone` = ?,
            `pincode` = COALESCE(?, `pincode`),
            `address_line1` = COALESCE(?, `address_line1`),
            `address_line2` = ?,
            `landmark` = ?,
            `city` = COALESCE(?, `city`),
            `state` = COALESCE(?, `state`),
            `address_type` = COALESCE(?, `address_type`),
            `is_default` = ?
            WHERE `id` = ? AND `user_id` = ?");

        $stmt->execute([
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $data['alternate_phone'] ?? null,
            $data['pincode'] ?? null,
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            $data['landmark'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['address_type'] ?? null,
            $isDefault,
            $addressId,
            $user['id']
        ]);

        Response::success(null, "Address updated successfully");
    }

    public function deleteAddress(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $addressId = (int)$params['id'];

        $stmt = $this->db->prepare("UPDATE `addresses` SET `deleted_at` = NOW() WHERE `id` = ? AND `user_id` = ?");
        $stmt->execute([$addressId, $user['id']]);

        Response::success(null, "Address deleted successfully");
    }

    public function setDefault(array $params): void {
        $user = AuthMiddleware::authenticate(true);
        $addressId = (int)$params['id'];

        $this->db->prepare("UPDATE `addresses` SET `is_default` = 0 WHERE `user_id` = ?")->execute([$user['id']]);
        $this->db->prepare("UPDATE `addresses` SET `is_default` = 1 WHERE `id` = ? AND `user_id` = ?")->execute([$addressId, $user['id']]);

        Response::success(null, "Default address set successfully");
    }
}
