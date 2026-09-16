<?php 
require_once __DIR__ . "/auth.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) as live_stock FROM sell WHERE category_id = ? AND status = '1'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo (string)((int)($res['live_stock'] ?? 0));
} else {
    echo "0";
}
?>