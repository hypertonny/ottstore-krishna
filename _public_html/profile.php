<?php
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$alertMsg = null;
$alertType = "danger";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    if ($action === 'change_password') {
        $curPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($curPass, $user['password'])) {
            $alertMsg = "Current password is incorrect.";
        } elseif (strlen($newPass) < 6) {
            $alertMsg = "New password must be at least 6 characters.";
        } elseif ($newPass !== $confPass) {
            $alertMsg = "New passwords do not match.";
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $uStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $uStmt->bind_param("si", $hash, $user_id);
            if ($uStmt->execute()) {
                $alertMsg = "Password updated successfully!";
                $alertType = "success";
                $user['password'] = $hash;
            } else {
                $alertMsg = "Failed to update password.";
            }
        }
    } elseif ($action === 'delete_account') {
        $delPass = $_POST['delete_password'] ?? '';
        if (!password_verify($delPass, $user['password'])) {
            $alertMsg = "Incorrect password. Account was not deleted.";
        } else {
            $d1 = $conn->prepare("DELETE FROM purchase_history WHERE user_id = ?");
            $d1->bind_param("i", $user_id);
            $d1->execute();

            $d2 = $conn->prepare("DELETE FROM users WHERE id = ?");
            $d2->bind_param("i", $user_id);
            $d2->execute();

            session_destroy();
            header("Location: login.php?msg=account_deleted");
            exit();
        }
    }
}

// Fetch user orders / purchases
$ordersStmt = $conn->prepare("SELECT * FROM purchase_history WHERE user_id = ? ORDER BY id DESC");
$ordersStmt->bind_param("i", $user_id);
$ordersStmt->execute();
$orders = $ordersStmt->get_result();

$whatsappNumber = get_setting('support_whatsapp', '13082229928');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Account | OTT STORE PREMIUM</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome -->
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
            padding-bottom: 40px;
        }

        .navbar-custom {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-card);
            padding: 14px 0;
            margin-bottom: 32px;
        }

        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.4);
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }

        .chat-container {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            padding: 16px;
            height: 200px;
            overflow-y: auto;
        }

        .chat-msg {
            margin-bottom: 10px;
            font-size: 0.88rem;
        }

        .chat-msg.user {
            text-align: right;
            color: #a5b4fc;
        }

        .chat-msg.bot {
            text-align: left;
            color: #cbd5e1;
        }

        .btn-action {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 18px;
            transition: transform 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand navbar-custom">
    <div class="container">
        <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="index.php">
            <span class="badge bg-primary p-2 rounded-3"><i class="fa-solid fa-play"></i></span>
            <span>OTT<span style="color:#818cf8;">STORE</span></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fa-solid fa-store me-1"></i> Storefront
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <?php if ($alertMsg): ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid <?= $alertType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
            <?= htmlspecialchars($alertMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Overview Card -->
        <div class="col-lg-4">
            <div class="profile-card text-center mb-4">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                <p class="text-secondary small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill mb-4">
                    <i class="fa-solid fa-circle-check me-1"></i> Verified Customer
                </span>

                <div class="text-start border-top border-secondary border-opacity-25 pt-3">
                    <div class="d-flex justify-content-between small text-secondary mb-2">
                        <span>Customer ID:</span>
                        <strong class="text-white">#<?= $user['id'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Member Since:</span>
                        <strong class="text-white"><?= date("M Y", strtotime($user['created_at'] ?? 'now')) ?></strong>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="index.php" class="btn btn-action text-decoration-none">
                        <i class="fa-solid fa-bag-shopping me-1"></i> Buy New Subscription
                    </a>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsappNumber) ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp Support
                    </a>
                    <div class="d-flex gap-2 pt-2 border-top border-secondary border-opacity-25 mt-2">
                        <button class="btn btn-outline-light btn-sm rounded-pill w-50" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fa-solid fa-key me-1"></i> Password
                        </button>
                        <button class="btn btn-outline-danger btn-sm rounded-pill w-50" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Instant AI Support Assistant -->
            <div class="profile-card">
                <h6 class="fw-bold text-white mb-3"><i class="fa-solid fa-robot text-primary me-2"></i>Instant Help Assistant</h6>
                <div class="chat-container mb-3" id="chatContainer">
                    <div class="chat-msg bot">
                        <strong>AI Assistant:</strong> Hello! How can I assist you with your OTT subscriptions today?
                    </div>
                </div>
                <div class="input-group">
                    <input type="text" id="chatInput" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Ask about orders, delivery...">
                    <button class="btn btn-primary btn-sm" onclick="sendChatMessage()">Send</button>
                </div>
            </div>
        </div>

        <!-- Orders & Transactions Section -->
        <div class="col-lg-8">
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="fa-solid fa-receipt text-indigo-400 me-2" style="color: #818cf8;"></i>Recent Orders & Subscriptions
                    </h5>
                    <a href="index.php" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-plus me-1"></i> New Order
                    </a>
                </div>

                <?php if ($orders && $orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover border-secondary border-opacity-25 align-middle">
                            <thead>
                                <tr class="text-secondary small">
                                    <th>Service</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Credentials</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-semibold text-white"><?= htmlspecialchars($order['product_name']) ?></td>
                                        <td><?= (int)$order['quantity'] ?></td>
                                        <td>&#8377;<?= number_format($order['price'], 0) ?></td>
                                        <td class="small text-secondary"><?= date("d M Y", strtotime($order['purchase_date'])) ?></td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill">Delivered</span></td>
                                        <td class="text-end">
                                            <?php if (!empty($order['txn_id'])): ?>
                                                <a href="download.php?id=<?= urlencode($order['txn_id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1">
                                                    <i class="fa-solid fa-key me-1"></i> Access
                                                </a>
                                            <?php else: ?>
                                                <span class="text-secondary small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="text-secondary mb-3">
                            <i class="fa-solid fa-box-open fs-1 opacity-50"></i>
                        </div>
                        <h6 class="text-white fw-bold mb-1">No orders placed yet</h6>
                        <p class="text-secondary small mb-3">Explore our catalog to purchase your favorite streaming accounts with instant automated delivery.</p>
                        <a href="index.php" class="btn btn-action btn-sm text-decoration-none px-4">
                            Browse Subscriptions
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-4 p-3 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                    <h6 class="text-white small fw-bold mb-1"><i class="fa-solid fa-info-circle me-1"></i> Fast Retrieval Reminder</h6>
                    <p class="text-secondary small mb-0">
                        If you paid via UPI without signing in, you can always retrieve your accounts anytime on the download page using your 12-digit UTR number!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const container = document.getElementById('chatContainer');
    const msg = input.value.trim();
    if (!msg) return;

    container.innerHTML += `<div class="chat-msg user"><strong>You:</strong> ${msg}</div>`;
    input.value = '';
    container.scrollTop = container.scrollHeight;

    fetch('chat_support.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(res => res.text())
    .then(reply => {
        container.innerHTML += `<div class="chat-msg bot">${reply}</div>`;
        container.scrollTop = container.scrollHeight;
    })
    .catch(() => {
        container.innerHTML += `<div class="chat-msg bot"><strong>AI:</strong> For instant support, please reach out to our WhatsApp team!</div>`;
        container.scrollTop = container.scrollHeight;
    });
}

document.getElementById('chatInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendChatMessage();
});
</script>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #111827; border: 1px solid rgba(255,255,255,0.1); border-radius: 18px;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white fw-bold"><i class="fa-solid fa-key text-primary me-2"></i>Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #111827; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 18px;">
            <div class="modal-header border-danger border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete_account">
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Warning: Deleting your account will remove your account profile and all order history records. This action cannot be reversed.
                    </p>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Confirm with your Password</label>
                        <input type="password" name="delete_password" class="form-control" placeholder="Enter password to confirm" required>
                    </div>
                </div>
                <div class="modal-footer border-danger border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
