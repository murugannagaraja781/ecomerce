<?php
/**
 * Standard API Response Helper
 */

class Response {
    public static function json($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success($data = null, string $message = "Success", int $statusCode = 200): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    public static function error(string $message = "An error occurred", array $errors = [], int $statusCode = 400): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], $statusCode);
    }

    public static function paginated(array $items, int $total, int $page, int $limit, string $message = "Success"): void {
        $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;
        self::json([
            'success'    => true,
            'message'    => $message,
            'data'       => $items,
            'pagination' => [
                'total_items'  => $total,
                'current_page' => $page,
                'per_page'     => $limit,
                'total_pages'  => $totalPages,
                'has_next'     => $page < $totalPages,
                'has_prev'     => $page > 1
            ]
        ]);
    }
}
