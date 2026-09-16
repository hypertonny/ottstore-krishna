<?php
/**
 * OTT Store Rate Limiter & Brute-Force Protection
 * Protects login, admin access, and sensitive endpoints from brute force and credential stuffing.
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

/**
 * Resolves the genuine client IP address, respecting Cloudflare edge headers.
 */
function getClientIP(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return trim($_SERVER['HTTP_CF_CONNECTING_IP']);
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Ensures the login_attempts table exists in the database.
 */
function ensureRateLimitTable(mysqli $conn): void {
    static $checked = false;
    if ($checked) return;

    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        attempt_type VARCHAR(32) NOT NULL,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rate_limit (ip_address, attempt_type, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    @$conn->query($sql);
    $checked = true;
}

/**
 * Checks if the current client IP has exceeded the allowed attempts for a given action.
 *
 * @param mysqli $conn
 * @param string $type ('user_login', 'admin_login', 'password_reset')
 * @param int $maxAttempts (default: 5)
 * @param int $decayMinutes (default: 15)
 * @return array ['limited' => bool, 'attempts' => int, 'remaining_attempts' => int, 'retry_after_minutes' => int]
 */
function isRateLimited(mysqli $conn, string $type = 'user_login', int $maxAttempts = 5, int $decayMinutes = 15): array {
    ensureRateLimitTable($conn);
    $ip = getClientIP();

    // Clean up attempts older than decay window periodically (1 in 50 requests)
    if (mt_rand(1, 50) === 1) {
        @$conn->query("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL " . intval($decayMinutes * 2) . " MINUTE");
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as failed_count, MAX(attempt_time) as last_attempt FROM login_attempts WHERE ip_address = ? AND attempt_type = ? AND attempt_time >= NOW() - INTERVAL ? MINUTE");
    if (!$stmt) {
        return ['limited' => false, 'attempts' => 0, 'remaining_attempts' => $maxAttempts, 'retry_after_minutes' => 0];
    }

    $stmt->bind_param("ssi", $ip, $type, $decayMinutes);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $attempts = intval($row['failed_count'] ?? 0);
    $isLimited = $attempts >= $maxAttempts;

    return [
        'limited' => $isLimited,
        'attempts' => $attempts,
        'remaining_attempts' => max(0, $maxAttempts - $attempts),
        'retry_after_minutes' => $decayMinutes
    ];
}

/**
 * Records a failed authentication attempt.
 */
function recordFailedAttempt(mysqli $conn, string $type = 'user_login'): void {
    ensureRateLimitTable($conn);
    $ip = getClientIP();

    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempt_type, attempt_time) VALUES (?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ss", $ip, $type);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Clears failed attempts upon successful authentication.
 */
function clearFailedAttempts(mysqli $conn, string $type = 'user_login'): void {
    ensureRateLimitTable($conn);
    $ip = getClientIP();

    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ? AND attempt_type = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $ip, $type);
        $stmt->execute();
        $stmt->close();
    }
}
