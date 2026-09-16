<?php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

if (!defined('DB_SERVER')) define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USERNAME')) define('DB_USERNAME', getenv('DB_USER') ?: 'u168620846_ott');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '4uQ3=1+v>&');
if (!defined('DB_DATABASE')) define('DB_DATABASE', getenv('DB_NAME') ?: 'u168620846_ott');
// Global database connection singleton
global $conn;
if (!isset($conn) || !($conn instanceof mysqli)) {
    mysqli_report(MYSQLI_REPORT_OFF);
    
    // Attempt 1: Configured database credentials
    $conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    
    // Attempt 2: Local XAMPP default fallback if configured credentials fail
    if ($conn->connect_error) {
        $conn = @new mysqli('localhost', 'root', '', DB_DATABASE);
        if ($conn->connect_error) {
            $conn = @new mysqli('localhost', 'root', '', 'ott_store');
        }
    }
    
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
}

// Dynamic Site & Gateway Settings
global $site_settings;
$site_settings = [];
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $settingsRes = @$conn->query("SELECT setting_key, setting_value FROM settings");
    if ($settingsRes) {
        while ($row = $settingsRes->fetch_assoc()) {
            $site_settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $site_settings;
        return isset($site_settings[$key]) && $site_settings[$key] !== '' ? $site_settings[$key] : $default;
    }
}

if (!function_exists('update_setting')) {
    function update_setting($key, $value) {
        global $conn, $site_settings;
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $key, $value);
        $result = $stmt->execute();
        if ($result) {
            $site_settings[$key] = $value;
        }
        return $result;
    }
}

// Fallback constants mapped to dynamic settings
if (!defined('PAYTM_UPI_ID')) define('PAYTM_UPI_ID', get_setting('upi_id', 'paytmqr1rudv8w05q@paytm'));
if (!defined('SUPPORT_WHATSAPP')) define('SUPPORT_WHATSAPP', get_setting('support_whatsapp', '13082229928'));
if (!defined('SUPPORT_EMAIL')) define('SUPPORT_EMAIL', get_setting('support_email', 'buy@ottstore.help'));
if (!defined('SUPPORT_TELEGRAM')) define('SUPPORT_TELEGRAM', get_setting('support_telegram', 'ottbuyofficial'));
?>