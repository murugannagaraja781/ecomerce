<?php
/**
 * Base Controller for REST API
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../config/database.php';

abstract class BaseController {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    protected function getRequestData(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (stripos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            return is_array($data) ? $data : [];
        }

        return array_merge($_GET, $_POST);
    }

    protected function getJsonBody(): array {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function getQueryParams(): array {
        return $_GET;
    }

    protected function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
