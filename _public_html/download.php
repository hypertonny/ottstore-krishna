<?php
require_once __DIR__ . "/auth.php";

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$records = [];

if (!empty($id)) {
    $stmt = $conn->prepare("SELECT * FROM sold WHERE txn_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Credentials | OTT Store Premium</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 32px 16px;
        }

        .credential-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 36px;
            width: 100%;
            max-width: 680px;
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.6), 0 0 32px -4px rgba(99, 102, 241, 0.2);
        }

        .cred-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.2s;
        }

        .cred-item:hover {
            border-color: rgba(99, 102, 241, 0.3);
            background: rgba(255, 255, 255, 0.05);
        }

        .cred-field {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.95rem;
            color: #38bdf8;
            word-break: break-all;
            margin-top: 6px;
        }

        .btn-copy {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-copy:hover {
            color: #fff;
        }

        .btn-home {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.25s ease;
        }

        .btn-home:hover {
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="credential-container">
    <?php if (!empty($records)): ?>
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle p-3 mb-3" style="width: 64px; height: 64px;">
                <i class="fa-solid fa-circle-check fs-2"></i>
            </div>
            <h3 class="fw-bold text-white mb-1">Order Delivered Successfully!</h3>
            <p class="text-secondary small mb-0">UTR / Ref: <code><?= htmlspecialchars($id) ?></code></p>
        </div>

        <div class="alert alert-info bg-opacity-10 border-info text-info small mb-4">
            <i class="fa-solid fa-lightbulb me-1"></i> <strong>Important:</strong> Save your credentials immediately. Do not share or alter profile names/PINs if indicated in rules.
        </div>

        <?php foreach ($records as $index => $record): ?>
            <div class="cred-item">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                        Account #<?= $index + 1 ?>
                    </span>
                    <button class="btn-copy small" onclick="copyAll('<?= htmlspecialchars($record['username']) ?>', '<?= htmlspecialchars($record['password']) ?>')">
                        <i class="fa-regular fa-copy me-1"></i> Copy Both
                    </button>
                </div>

                <div class="mb-2">
                    <label class="small text-secondary fw-semibold">Email / Username / Data:</label>
                    <div class="cred-field">
                        <span><?= htmlspecialchars($record['username']) ?></span>
                        <button class="btn-copy" onclick="copyText('<?= htmlspecialchars($record['username']) ?>')" title="Copy Email">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="small text-secondary fw-semibold">Password:</label>
                    <div class="cred-field">
                        <span><?= htmlspecialchars($record['password']) ?></span>
                        <button class="btn-copy" onclick="copyText('<?= htmlspecialchars($record['password']) ?>')" title="Copy Password">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mt-4">
            <a href="index.php" class="btn-home flex-grow-1 text-center">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Store
            </a>
            <button class="btn btn-outline-secondary rounded-3 px-3" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print / PDF
            </button>
        </div>

    <?php else: ?>
        <div class="text-center py-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle p-3 mb-3" style="width: 64px; height: 64px;">
                <i class="fa-solid fa-triangle-exclamation fs-2"></i>
            </div>
            <h4 class="fw-bold text-white mb-2">Transaction Not Found</h4>
            <p class="text-secondary small mb-4">
                No delivered accounts were found for Reference ID <code><?= htmlspecialchars($id) ?></code>.<br>
                If your payment was completed, please contact our support team on WhatsApp with your payment screenshot.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="index.php" class="btn btn-outline-light rounded-pill px-4">Back to Store</a>
                <a href="https://wa.me/<?= htmlspecialchars(SUPPORT_WHATSAPP) ?>?text=<?= urlencode('Hello, my UTR ' . $id . ' did not release credentials.') ?>" 
                   target="_blank" 
                   class="btn btn-success rounded-pill px-4">
                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp Support
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert("Copied to clipboard: " + text);
    });
}

function copyAll(user, pass) {
    navigator.clipboard.writeText("Username: " + user + "\nPassword: " + pass).then(function() {
        alert("Account credentials copied to clipboard!");
    });
}
</script>

</body>
</html>
