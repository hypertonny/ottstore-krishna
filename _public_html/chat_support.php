<?php
require_once __DIR__ . "/auth.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message'] ?? '');
    $whatsapp = get_setting('support_whatsapp', '13082229928');
    $email = get_setting('support_email', 'buy@ottstore.help');

    $msg = strtolower($message);
    $response = "";

    if (empty($msg)) {
        $response = "Please type a question or topic so I can assist you!";
    } elseif (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false || strpos($msg, 'hey') !== false) {
        $response = "Hello! I am your AI Support Assistant. How can I help you with your subscriptions today?";
    } elseif (strpos($msg, 'order') !== false || strpos($msg, 'history') !== false) {
        $response = "Your orders are listed under 'Recent Orders & Subscriptions'. Click the blue 'Access' button to view and copy your credentials!";
    } elseif (strpos($msg, 'download') !== false || strpos($msg, 'credential') !== false || strpos($msg, 'password') !== false) {
        $response = "You can view your delivered login credentials anytime on the download page using your 12-digit payment UTR number.";
    } elseif (strpos($msg, 'payment') !== false || strpos($msg, 'upi') !== false || strpos($msg, 'pay') !== false) {
        $response = "We accept all UPI payment apps (Google Pay, PhonePe, Paytm, BHIM). Once paid, enter your 12-digit bank UTR for instant delivery.";
    } elseif (strpos($msg, 'refund') !== false || strpos($msg, 'cancel') !== false) {
        $response = "For refund or cancellation requests, please contact our support team directly at $email or via WhatsApp (+$whatsapp).";
    } elseif (strpos($msg, 'warranty') !== false || strpos($msg, 'replace') !== false || strpos($msg, 'not work') !== false) {
        $response = "All subscriptions include a 30-day replacement warranty! If an account stops working, message us on WhatsApp with your UTR.";
    } elseif (strpos($msg, 'bye') !== false || strpos($msg, 'thank') !== false) {
        $response = "You're very welcome! Enjoy your streaming experience and let us know if you need anything else.";
    } else {
        $response = "I'm not completely sure about that, but our team is available 24/7 on WhatsApp (+$whatsapp) to assist you directly!";
    }

    echo "<strong>AI Assistant:</strong> " . htmlspecialchars($response);
}
?>
