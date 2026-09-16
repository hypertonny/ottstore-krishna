<?php
$pageTitle = "Store & Gateway Settings";
require_once __DIR__ . "/header.php";

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upi_id = trim($_POST['upi_id'] ?? '');
    $upi_name = trim($_POST['upi_name'] ?? '');
    $support_whatsapp = trim($_POST['support_whatsapp'] ?? '');
    $support_telegram = trim($_POST['support_telegram'] ?? '');
    $support_email = trim($_POST['support_email'] ?? '');
    $payment_mode = trim($_POST['payment_mode'] ?? 'test');
    $bharatpe_merchant_id = trim($_POST['bharatpe_merchant_id'] ?? '');
    $bharatpe_token = trim($_POST['bharatpe_token'] ?? '');
    $admin_code = trim($_POST['admin_code'] ?? '');

    // Update settings in database
    update_setting('upi_id', $upi_id);
    update_setting('upi_name', $upi_name);
    update_setting('support_whatsapp', $support_whatsapp);
    update_setting('support_telegram', $support_telegram);
    update_setting('support_email', $support_email);
    update_setting('payment_mode', $payment_mode);
    update_setting('bharatpe_merchant_id', $bharatpe_merchant_id);
    update_setting('bharatpe_token', $bharatpe_token);

    if (!empty($admin_code)) {
        update_setting('admin_code', $admin_code);
        // Also update login table
        $conn->query("UPDATE login SET username = '" . $conn->real_escape_string($admin_code) . "' WHERE id = 1");
    }

    $message = "Settings updated successfully! Changes take effect immediately across all storefront pages.";
    $messageType = "success";
}

// Current values
$current_upi_id = get_setting('upi_id', 'paytmqr1rudv8w05q@paytm');
$current_upi_name = get_setting('upi_name', 'OTT Store Premium');
$current_whatsapp = get_setting('support_whatsapp', '13082229928');
$current_telegram = get_setting('support_telegram', 'ottbuyofficial');
$current_email = get_setting('support_email', 'buy@ottstore.help');
$current_payment_mode = get_setting('payment_mode', 'test');
$current_bp_mid = get_setting('bharatpe_merchant_id', '45636937');
$current_bp_token = get_setting('bharatpe_token', '31be0c416489437baf1ee12135870582');
$current_admin_code = get_setting('admin_code', 'radium');
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Store & Gateway Configuration</h3>
        <p class="text-secondary small mb-0">Control UPI payment QR, live verification gateways, support handles, and admin credentials</p>
    </div>
    <div>
        <a href="../index.php" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview Store
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form method="POST" action="settings.php">
    <div class="row g-4">
        <!-- UPI & QR Code Settings -->
        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-badge bg-primary"><i class="fa-solid fa-qrcode"></i></div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">UPI Payment & QR Settings</h5>
                        <small class="text-secondary">Used for generating the dynamic scan-and-pay UPI QR code</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">UPI ID (VPA)</label>
                    <input type="text" name="upi_id" class="form-control" value="<?= htmlspecialchars($current_upi_id) ?>" placeholder="e.g. merchant@paytm or 9876543210@upi" required>
                    <small class="text-secondary">Payments from GPay, PhonePe, Paytm will be routed to this VPA.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Payee / Store Display Name</label>
                    <input type="text" name="upi_name" class="form-control" value="<?= htmlspecialchars($current_upi_name) ?>" placeholder="e.g. OTT Store Premium" required>
                    <small class="text-secondary">Visible in customer's UPI payment apps during scan.</small>
                </div>

                <div class="p-3 rounded-3" style="background: rgba(99, 102, 241, 0.08); border: 1px dashed rgba(99, 102, 241, 0.3);">
                    <div class="d-flex align-items-center gap-2 text-indigo mb-1">
                        <i class="fa-solid fa-info-circle"></i>
                        <span class="small fw-bold">Live QR Preview</span>
                    </div>
                    <code class="small text-secondary d-block text-break">
                        upi://pay?pa=<?= htmlspecialchars($current_upi_id) ?>&pn=<?= urlencode($current_upi_name) ?>&cu=INR
                    </code>
                </div>
            </div>
        </div>

        <!-- Payment Mode & Gateway -->
        <div class="col-lg-6">
            <div class="dash-card h-100">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-badge" style="background: #10b981;"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">Payment Verification Engine</h5>
                        <small class="text-secondary">Choose how customer transactions are verified</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-bold">Verification Mode</label>
                    <div class="d-flex flex-column gap-2">
                        <label class="p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer" style="background: rgba(255,255,255,0.03); border-color: <?= $current_payment_mode === 'test' ? '#10b981 !important' : 'rgba(255,255,255,0.1)' ?>;">
                            <input type="radio" name="payment_mode" value="test" class="form-check-input mt-0" <?= $current_payment_mode === 'test' ? 'checked' : '' ?>>
                            <div>
                                <div class="fw-bold text-white"><span class="badge bg-success me-1">Instant / Fast Mode</span> Auto-Delivers on Valid UTR</div>
                                <small class="text-secondary">Validates 12-digit UTR format, automatically delivers available account stock, and prevents duplicate UTR reuse. Recommended for testing & manual payment auditing.</small>
                            </div>
                        </label>

                        <label class="p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer" style="background: rgba(255,255,255,0.03); border-color: <?= $current_payment_mode === 'bharatpe' ? '#6366f1 !important' : 'rgba(255,255,255,0.1)' ?>;">
                            <input type="radio" name="payment_mode" value="bharatpe" class="form-check-input mt-0" <?= $current_payment_mode === 'bharatpe' ? 'checked' : '' ?>>
                            <div>
                                <div class="fw-bold text-white"><span class="badge bg-primary me-1">BharatPe Live Gateway</span> Bank API Verification</div>
                                <small class="text-secondary">Queries BharatPe merchant transaction API in real time. Requires an active Merchant ID and live API Token.</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label text-light small fw-bold">BharatPe Merchant ID</label>
                        <input type="text" name="bharatpe_merchant_id" class="form-control" value="<?= htmlspecialchars($current_bp_mid) ?>" placeholder="e.g. 45636937">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label text-light small fw-bold">BharatPe API Token</label>
                        <input type="text" name="bharatpe_token" class="form-control" value="<?= htmlspecialchars($current_bp_token) ?>" placeholder="Token from BharatPe App">
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Channels -->
        <div class="col-lg-6">
            <div class="dash-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-badge" style="background: #22c55e;"><i class="fa-brands fa-whatsapp"></i></div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">Customer Support Channels</h5>
                        <small class="text-secondary">Floating contact buttons & help links on storefront</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">WhatsApp Support Number (With Country Code)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-success"><i class="fa-brands fa-whatsapp"></i></span>
                        <input type="text" name="support_whatsapp" class="form-control" value="<?= htmlspecialchars($current_whatsapp) ?>" placeholder="e.g. 919876543210 (without +)" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Telegram Username (Without @)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-info"><i class="fa-brands fa-telegram"></i></span>
                        <input type="text" name="support_telegram" class="form-control" value="<?= htmlspecialchars($current_telegram) ?>" placeholder="e.g. ottbuyofficial" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Support Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($current_email) ?>" placeholder="e.g. support@ottstore.com" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security & Admin Access -->
        <div class="col-lg-6">
            <div class="dash-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-badge" style="background: #ef4444;"><i class="fa-solid fa-key"></i></div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">Admin Security & Access</h5>
                        <small class="text-secondary">Master passcode required to log into the Admin Control Center</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Admin Master Access Code</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-danger"><i class="fa-solid fa-lock"></i></span>
                        <input type="text" name="admin_code" class="form-control" value="<?= htmlspecialchars($current_admin_code) ?>" placeholder="e.g. radium" required>
                    </div>
                    <small class="text-secondary">This is the access code used on the admin login screen (`/admin/index.php`).</small>
                </div>

                <div class="p-3 rounded-3 mt-4" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div class="d-flex align-items-center gap-2 text-danger mb-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span class="small fw-bold">Security Notice</span>
                    </div>
                    <small class="text-secondary">
                        Changing the master access code will take effect on your next login. Always keep this passcode safe.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <button type="submit" class="btn-gradient px-4 py-2.5">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . "/footer.php"; ?>
