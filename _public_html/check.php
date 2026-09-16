<?php 
require_once __DIR__ . "/auth.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

if ($availableStock <= 0) {
    header('Location: index.php?msg=outofstock');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | <?= htmlspecialchars($category['name']) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
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

        .checkout-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            padding: 36px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5), 0 0 30px -5px rgba(99, 102, 241, 0.15);
        }

        .product-badge {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }

        .logo-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.07);
            border-color: #818cf8;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-proceed {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 13px;
            border-radius: 12px;
            width: 100%;
            font-size: 1rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-proceed:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        .btn-cancel {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #94a3b8;
            font-weight: 600;
            padding: 11px;
            border-radius: 12px;
            width: 100%;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .price-summary {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="checkout-box">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Confirm Order</h4>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill">
            <i class="fa-solid fa-circle-check me-1"></i> <?= $availableStock ?> Available
        </span>
    </div>

    <!-- Product Summary Card -->
    <div class="product-badge">
        <div class="logo-box">
            <img src="<?= htmlspecialchars($category['logo_url']) ?>" alt="Service Logo" onerror="this.onerror=null; this.src='assets/icons/netflix.svg';">
        </div>
        <div>
            <h6 class="fw-bold text-white mb-0"><?= htmlspecialchars($category['name']) ?></h6>
            <small class="text-secondary">&#8377;<?= number_format($category['amount'], 0) ?> per account</small>
        </div>
    </div>

    <form id="checkoutForm" action="payment.php" method="GET">
        <input type="hidden" name="service" value="<?= htmlspecialchars($id) ?>">

        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">Quantity (Accounts):</label>
            <input type="number" class="form-control" name="account" id="account" value="1" min="1" max="<?= $availableStock ?>" required>
            <small class="text-muted mt-1 d-block">Max available for instant delivery: <?= $availableStock ?></small>
        </div>

        <!-- Total Price Summary -->
        <div class="price-summary">
            <span class="text-secondary fw-medium">Total Payable:</span>
            <span class="fs-4 fw-bold text-white" id="displayAmount">&#8377;<?= number_format($category['amount'], 0) ?></span>
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="terms" required checked>
            <label class="form-check-label text-secondary small" for="terms">
                I understand this is a digital product delivered instantly upon UPI confirmation.
            </label>
        </div>

        <button type="submit" class="btn-proceed">
            <span>Proceed to Payment</span>
            <i class="fa-solid fa-arrow-right ms-1"></i>
        </button>
        <a class="btn-cancel" href="index.php">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Catalog
        </a>
    </form>
</div>

<script>
    const unitPrice = <?= json_encode((float)$category['amount']) ?>;
    const maxStock = <?= json_encode($availableStock) ?>;
    const accountInput = document.getElementById("account");
    const displayAmount = document.getElementById("displayAmount");

    accountInput.addEventListener("input", function() {
        let count = parseInt(this.value) || 1;
        if (count < 1) count = 1;
        if (count > maxStock) count = maxStock;
        this.value = count;
        displayAmount.innerHTML = "&#8377;" + (count * unitPrice);
    });
</script>
<script src="dist/js/crafted.js"></script>
</body>
</html>

