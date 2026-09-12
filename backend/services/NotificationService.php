<?php
/**
 * Notification Service - Firebase Cloud Messaging (FCM) & Push Dispatcher
 */

require_once dirname(__DIR__) . '/helpers/Env.php';
require_once dirname(__DIR__) . '/helpers/Logger.php';

class NotificationService {

    public static function getFcmConfig(): array {
        return [
            'server_key' => Env::get('FCM_SERVER_KEY', ''),
            'project_id' => Env::get('FCM_PROJECT_ID', ''),
            'sender_id'  => Env::get('FCM_SENDER_ID', ''),
            'is_configured' => !empty(Env::get('FCM_SERVER_KEY'))
        ];
    }

    /**
     * Send Push Notification via Firebase Cloud Messaging
     */
    public static function sendPush(string $title, string $body, ?string $fcmToken = null, array $extraData = []): array {
        $serverKey = Env::get('FCM_SERVER_KEY');
        $targetToken = $fcmToken ?: 'device_token_sample_web_app';

        if (empty($serverKey) || str_contains($serverKey, 'sample')) {
            // Local simulation log
            $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . DIRECTORY_SEPARATOR . 'fcm.log';
            $entry = "[" . date('Y-m-d H:i:s') . "] [FCM_SIMULATION] Title: {$title} | Body: {$body} | Token: {$targetToken}\n";
            @file_put_contents($logFile, $entry, FILE_APPEND);

            return [
                'success'    => true,
                'mode'       => 'simulation',
                'title'      => $title,
                'body'       => $body,
                'fcm_token'  => $targetToken,
                'message'    => "FCM notification simulated successfully (Logged in fcm.log)"
            ];
        }

        $payload = [
            'to'           => $targetToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'icon'  => 'https://static-assets-web.flixcart.com/fk-p-linchpin-web/fk-cp-zion/img/flipkart-plus_8d85f4.png'
            ],
            'data'         => $extraData
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://fcm.googleapis.com/fcm/send',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: key={$serverKey}",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT        => 8
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Logger::error("FCM dispatch error: " . $err);
            return [
                'success' => false,
                'error'   => $err,
                'message' => "FCM dispatch failed: {$err}"
            ];
        }

        return [
            'success'  => true,
            'response' => json_decode($response, true),
            'message'  => "FCM Push notification sent successfully"
        ];
    }

    /**
     * Test FCM push notification
     */
    public static function testFcm(string $title, string $body, ?string $fcmToken = null): array {
        return self::sendPush($title, $body, $fcmToken, ['type' => 'TEST_ADMIN_ALERT', 'timestamp' => time()]);
    }
}
