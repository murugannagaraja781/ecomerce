<?php
/**
 * Secure File Upload Controller
 * Restricts uploads to validated images (JPEG, PNG, WebP) with MIME inspection,
 * getimagesize() validation, size enforcement (max 5MB), and randomized filenames.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Logger.php';

class UploadController extends BaseController {

    private const MAX_FILE_SIZE = 5242880; // 5 MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    /**
     * Handle Authenticated Image Upload
     */
    public function uploadImage(): void {
        $user = AuthMiddleware::authenticate(true);

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMsg = match ($errorCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File size exceeds server upload limit",
                UPLOAD_ERR_PARTIAL   => "The file was only partially uploaded",
                UPLOAD_ERR_NO_FILE   => "No file was uploaded",
                default              => "File upload failed"
            };
            Response::error($errorMsg, ['image' => [$errorMsg]], 422);
        }

        $file = $_FILES['image'];

        // 1. Validate File Size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            Response::error("File size cannot exceed 5MB", ['image' => ["Max allowed file size is 5MB"]], 413);
        }

        if ($file['size'] === 0) {
            Response::error("Uploaded file is empty", ['image' => ["Empty file received"]], 422);
        }

        // 2. Validate MIME Type with finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            Response::error("Invalid file format: {$mimeType}. Only JPG, PNG, and WebP images are permitted.", ['image' => ["Unsupported file type"]], 422);
        }

        // 3. Verify actual image payload with getimagesize
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            Response::error("File content is not a valid image", ['image' => ["Corrupted or invalid image"]], 422);
        }

        // 4. Safe destination directory
        $uploadDir = UPLOAD_DIR . DIRECTORY_SEPARATOR . 'images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 5. Ensure .htaccess prevents execution in uploads directory
        $htaccessPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "# Prevent script execution in upload directory\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8\nphp_flag engine off\n<FilesMatch \"\.(?i:php|phtml|phar|sh|pl|py|cgi)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n");
        }

        // 6. Generate cryptographically random filename
        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $safeName = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Logger::error("Failed to move uploaded file to: {$destination}");
            Response::error("Failed to save uploaded file", [], 500);
        }

        $url = UPLOAD_URL . '/images/' . $safeName;

        Response::success([
            'filename'  => $safeName,
            'url'       => $url,
            'mime_type' => $mimeType,
            'size'      => $file['size'],
            'width'     => $imageInfo[0],
            'height'    => $imageInfo[1]
        ], "Image uploaded successfully", 201);
    }
}
