<?php
require_once __DIR__ . "/auth.php";

$account = isset($_GET['account']) ? max(1, (int)$_GET['account']) : 1;
$id = isset($_GET['service']) ? (int)$_GET['service'] : 0;

$stmt1 = $conn->prepare("SELECT * FROM categorys WHERE id = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows <= 0) {
    header('Location: index.php');
    exit;
}

$category = $result1->fetch_assoc();

// Check available stock
$stmt2 = $conn->prepare("SELECT COUNT(*) as in_stock FROM sell WHERE category_id = ? AND status = '1'");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stockRow = $stmt2->get_result()->fetch_assoc();
$availableStock = (int)$stockRow['in_stock'];

if ($availableStock < $account) {
    echo "<script>alert('Requested quantity not available in stock'); window.location='index.php';</script>";
    exit;
}

$amount = (float)$category['amount'] * $account;
$upiId = get_setting('upi_id', 'paytmqr1rudv8w05q@paytm');
$upiName = get_setting('upi_name', 'OTT Store Premium');
$whatsappNumber = get_setting('support_whatsapp', '13082229928');

// Standard UPI payment URI
$upiString = "upi://pay?pa=" . rawurlencode($upiId) . "&pn=" . rawurlencode($upiName) . "&am=" . $amount . "&cu=INR&tn=" . rawurlencode("Order " . $category['name']);
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=" . urlencode($upiString);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI QR Payment | OTT Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        :root {
            --bg-body: #090d16;
            --bg-card: #111827;
            --border-card: rgba(255, 255, 255, 0.08);
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }

        body {
            background-color: var(--bg-body);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .payment-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 32px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5), 0 0 30px -5px rgba(99, 102, 241, 0.15);
            text-align: center;
        }

        .qr-wrapper {
            background: #fff;
            padding: 12px;
            border-radius: 16px;
            display: inline-block;
            margin: 16px 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .qr-wrapper img {
            width: 220px;
            height: 220px;
            display: block;
        }

        .amount-display {
            font-size: 2rem;
            font-weight: 800;
            color: #38bdf8;
            margin: 8px 0;
        }

        .upi-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 8px 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 1.05rem;
            text-align: center;
            letter-spacing: 1px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: #818cf8;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-verify {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 13px 24px;
            border-radius: 12px;
            width: 100%;
            font-size: 1rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        .btn-download-now {
            background: #10b981;
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            text-decoration: none;
            display: none;
            font-size: 1.05rem;
            margin-top: 14px;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        }

        .btn-download-now:hover {
            background: #059669;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="payment-box">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <a href="check.php?id=<?= $id ?>" class="text-secondary text-decoration-none small">
            <i class="fa-solid fa-arrow-left me-1"></i> Change
        </a>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill">
            <i class="fa-solid fa-shield-check me-1"></i> Auto Verification
        </span>
    </div>

    <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($category['name']) ?></h4>
    <small class="text-secondary">Quantity: <?= $account ?> Account<?= $account > 1 ? 's' : '' ?></small>

    <!-- Total Payable -->
    <div class="amount-display">&#8377;<?= number_format($amount, 0) ?></div>

    <!-- QR Code -->
    <div class="qr-wrapper">
        <img src="<?= $qrCodeUrl ?>" alt="Scan to Pay UPI QR">
    </div>

    <div class="d-block">
        <div class="upi-badge">
            <i class="fa-solid fa-qrcode text-info"></i>
            <span>UPI ID: <strong><?= htmlspecialchars($upiId) ?></strong></span>
            <button type="button" class="btn btn-sm btn-link text-info p-0 ms-1" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($upiId) ?>'); alert('UPI ID Copied!');">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
    </div>

    <p class="text-secondary small mb-3">
        1. Scan & pay via Google Pay, PhonePe, Paytm, or BHIM.<br>
        2. Enter the <strong>12-digit UTR / Ref Number</strong> below to claim your credentials instantly.
    </p>

    <input type="hidden" id="product_id" value="<?= $id ?>">
    <input type="hidden" id="quantity" value="<?= $account ?>">

    <div class="mb-3">
        <input type="text" class="form-control" id="utr" placeholder="Enter 12-Digit UTR Number" maxlength="16" required>
    </div>

    <button type="button" id="buy" class="btn-verify">
        <i class="fa-solid fa-bolt me-1"></i> Verify Payment & Get Account
    </button>

    <a id="myLink" class="btn-download-now" href="#">
        <i class="fa-solid fa-circle-check me-2"></i> Download Account Credentials
    </a>

    <div id="statusAlert" class="mt-3 text-start" style="display:none;"></div>

    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between">
        <a href="index.php" class="text-secondary small text-decoration-none">
            <i class="fa-solid fa-house me-1"></i> Home
        </a>
        <a href="https://wa.me/<?= htmlspecialchars($whatsappNumber) ?>?text=<?= urlencode('Need help with payment for ' . $category['name'] . ' amount ' . $amount) ?>" 
           target="_blank" 
           class="text-success small text-decoration-none">
            <i class="fa-brands fa-whatsapp me-1"></i> Payment Help
        </a>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#buy").click(function () {
        var utr = $('#utr').val().trim();
        var product = $('#product_id').val();
        var qty = $('#quantity').val();
        var $btn = $(this);
        var $alert = $('#statusAlert');

        if (utr === '') {
            alert('Please enter the 12-digit UTR / Reference number from your payment app.');
            $('#utr').focus();
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying with Bank...');
        $alert.hide();

        $.ajax({
            url: 'bharatpe.php?utr=' + encodeURIComponent(utr) + '&product_id=' + encodeURIComponent(product) + '&account=' + encodeURIComponent(qty),
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    $alert.html('<div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>' + response.message + '</div>').show();
                    $('#myLink').attr('href', 'download.php?id=' + encodeURIComponent(response.id)).show();
                    $btn.hide();
                } else {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-1"></i> Verify Payment & Get Account');
                    $alert.html('<div class="alert alert-warning"><i class="fa-solid fa-circle-exclamation me-2"></i>' + (response.message || 'Payment not verified yet. Please ensure the payment is completed.') + '</div>').show();
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-1"></i> Verify Payment & Get Account');
                $alert.html('<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Error connecting to verification server. Please check your connection or contact WhatsApp support.</div>').show();
            }
        });
    });
});
</script>
<script src="dist/js/crafted.js"></script>
</body>
</html>

