<?php
// weblangu/api/ResponseHelper.php

class ResponseHelper {
    /**
     * Sends a standardized JSON response.
     */
    public static function json(string $status, string $message, $data = null, int $httpCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        http_response_code($httpCode);
        echo json_encode([
            'status' => $status,
            'success' => ($status === 'success'),
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    public static function success(string $message = 'Success', $data = null): void {
        self::json('success', $message, $data, 200);
    }

    public static function error(string $message = 'Error', int $httpCode = 400): void {
        self::json('error', $message, null, $httpCode);
    }
}
?>