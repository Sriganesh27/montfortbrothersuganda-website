<?php
// web/api/rate_limiter.php

/**
 * Advanced Rate Limiter utilizing strict typing and optimized cleanup.
 */
function check_rate_limit(PDO $pdo, int $limit = 5, int $minutes = 1): bool {
    // Use proxy headers if behind Cloudflare/Load Balancer, otherwise fallback
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    try {
        // 1. Clean up old records asynchronously
        $time_limit = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        $stmtClean = $pdo->prepare("DELETE FROM web_api_limits WHERE last_request < ?");
        $stmtClean->execute([$time_limit]);

        // 2. Check current request count
        $stmtCheck = $pdo->prepare("SELECT request_count FROM web_api_limits WHERE ip_address = ?");
        $stmtCheck->execute([$ip]);
        $record = $stmtCheck->fetch();

        if ($record) {
            if ((int)$record['request_count'] >= $limit) {
                error_log("Security Warning: Rate limit exceeded for IP: $ip");
                return false; 
            }
            $stmtUpdate = $pdo->prepare("UPDATE web_api_limits SET request_count = request_count + 1, last_request = NOW() WHERE ip_address = ?");
            $stmtUpdate->execute([$ip]);
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO web_api_limits (ip_address) VALUES (?)");
            $stmtInsert->execute([$ip]);
        }
        return true; 
        
    } catch (PDOException $e) {
        error_log("Rate Limiter DB Error: " . $e->getMessage());
        return true; // Fail open to prevent blocking legitimate traffic on DB disconnect
    }
}
?>