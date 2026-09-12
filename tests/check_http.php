<?php
$urls = [
    'http://localhost/ecomerce/backend/api/products',
    'http://localhost/ecomerce/backend/public/api/products',
    'http://localhost/ecomerce/backend/public/index.php',
    'http://localhost/ecommerce_api/api/products',
    'http://localhost/ecomerce/customer_web/'
];

foreach ($urls as $url) {
    $ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 2]]);
    $res = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'NO_RESPONSE';
    echo "$url -> $status (len: " . strlen($res) . ")\n";
    if ($status && strpos($status, '200') !== false) {
        echo "   Sample: " . substr(strip_tags($res), 0, 100) . "\n";
    }
}
