<?php
require_once __DIR__ . "/auth.php";

header('Content-Type: application/json');

$return = [];

if (isset($_GET['utr']) && isset($_GET['product_id'])) {
    $utr = trim($_GET['utr']);
    $product_id = (int)$_GET['product_id'];
    $account = isset($_GET['account']) ? max(1, (int)$_GET['account']) : 1;

    // Validate Product
    $prodStmt = $conn->prepare("SELECT * FROM categorys WHERE id = ?");
    $prodStmt->bind_param("i", $product_id);
    $prodStmt->execute();
    $prodRes = $prodStmt->get_result();

    if ($prodRes->num_rows !== 1) {
        echo json_encode(['status' => 500, 'message' => 'Invalid Product Selected']);
        exit;
    }

    $product_data = $prodRes->fetch_assoc();
    $totalPayable = (float)$product_data['amount'] * $account;

    // Validate UTR Format (10 to 16 alphanumeric characters, usually 12 digits)
    if (!preg_match('/^[a-zA-Z0-9]{10,18}$/', $utr)) {
        echo json_encode(['status' => 500, 'message' => 'Please enter a valid 12-digit bank UTR / Reference number']);
        exit;
    }

    // Check if UTR is already claimed
    $checkTxn = $conn->prepare("SELECT id FROM sold WHERE txn_id = ?");
    $checkTxn->bind_param("s", $utr);
    $checkTxn->execute();
    if ($checkTxn->get_result()->num_rows > 0) {
        echo json_encode(['status' => 500, 'message' => 'This UTR has already been claimed! If you made another payment, please provide the new UTR.']);
        exit;
    }

    $paymentMode = get_setting('payment_mode', 'test');
    $isPaymentApproved = false;

    if ($paymentMode === 'test') {
        // Instant Mode: Verified directly
        $isPaymentApproved = true;
    } else {
        // BharatPe Live Mode: Query API
        $merchantId = get_setting('bharatpe_merchant_id', '45636937');
        $token = get_setting('bharatpe_token', '31be0c416489437baf1ee12135870582');
        $url = "https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId=" . urlencode($merchantId);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["token: $token"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($output);

        if ($httpCode === 401 || (isset($response->responseCode) && $response->responseCode == '401')) {
            echo json_encode([
                'status' => 500,
                'message' => 'Payment Gateway Error: BharatPe API token expired or unauthorized. Contact admin or switch to Instant Mode in Admin Settings.'
            ]);
            exit;
        }

        if (!isset($response->data->transactions)) {
            echo json_encode([
                'status' => 500,
                'message' => 'Could not verify transaction with bank. Please ensure payment is completed.',
                'raw' => $output
            ]);
            exit;
        }

        foreach ($response->data->transactions as $tx) {
            if (isset($tx->bankReferenceNo) && trim($tx->bankReferenceNo) === $utr) {
                if ((float)$tx->amount >= $totalPayable) {
                    $isPaymentApproved = true;
                    break;
                } else {
                    echo json_encode([
                        'status' => 500,
                        'message' => 'Payment received (₹' . $tx->amount . ') is less than the required amount (₹' . $totalPayable . ').'
                    ]);
                    exit;
                }
            }
        }

        if (!$isPaymentApproved) {
            echo json_encode([
                'status' => 500,
                'message' => 'Payment not found on the bank server yet. Please wait 10-30 seconds after paying and try again.'
            ]);
            exit;
        }
    }

    if ($isPaymentApproved) {
        // Check available stock in 'sell' table
        $stockStmt = $conn->prepare("SELECT id, username, password FROM sell WHERE category_id = ? AND status = '1' LIMIT ?");
        $stockStmt->bind_param("ii", $product_id, $account);
        $stockStmt->execute();
        $stockRes = $stockStmt->get_result();

        if ($stockRes->num_rows < $account) {
            echo json_encode([
                'status' => 500,
                'message' => 'Stock is currently exhausted for this item. Please contact WhatsApp support with your UTR to get instant fulfillment.'
            ]);
            exit;
        }

        // Allocate accounts and deliver
        $deliveredAccounts = [];
        while ($ac = $stockRes->fetch_assoc()) {
            $deliveredAccounts[] = $ac;

            // Record in sold table
            $insSold = $conn->prepare("INSERT INTO sold (txn_id, username, password, created_at) VALUES (?, ?, ?, NOW())");
            $insSold->bind_param("sss", $utr, $ac['username'], $ac['password']);
            $insSold->execute();

            // Mark account as sold (status = '2')
            $updSell = $conn->prepare("UPDATE sell SET status = '2' WHERE id = ?");
            $updSell->bind_param("i", $ac['id']);
            $updSell->execute();
        }

        // Record in purchase_history if user is logged in
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            $uid = (int)$_SESSION['user_id'];
            $pName = $product_data['name'];
            $insHist = $conn->prepare("INSERT INTO purchase_history (user_id, txn_id, product_id, product_name, quantity, price, purchase_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $insHist->bind_param("isisid", $uid, $utr, $product_id, $pName, $account, $totalPayable);
            $insHist->execute();
        }

        echo json_encode([
            'status' => 200,
            'message' => 'Payment Verified! Credentials allocated successfully.',
            'id' => $utr
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 500,
        'message' => 'Invalid parameters'
    ]);
    exit;
}
