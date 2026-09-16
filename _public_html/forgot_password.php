require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/rate_limiter.php";

$message = null;
$messageType = "danger";
$rateLimit = isRateLimited($conn, 'password_reset', 3, 15);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($rateLimit['limited']) {
        $message = "Too many password reset attempts. Please wait " . $rateLimit['retry_after_minutes'] . " minutes before trying again.";
    } else {
        $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($new_password)) {
        $message = "Please enter your email and a new password.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match. Please verify and try again.";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters in length.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                $message = "Password has been successfully reset! You can now log in with your new credentials.";
                $messageType = "success";
            } else {
                $message = "Could not update password. Please try again later.";
            }
        } else {
            recordFailedAttempt($conn, 'password_reset');
            $message = "No account was found with that email address.";
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | OTT Store</title>
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .auth-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5), 0 0 30px -5px rgba(99, 102, 241, 0.15);
        }

        .brand-icon {
            width: 54px;
            height: 54px;
            background: var(--accent-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            margin: 0 auto 20px auto;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: #818cf8;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-submit {
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none">
            <div class="brand-icon">
                <i class="fa-solid fa-key"></i>
            </div>
        </a>
        <h3 class="fw-bold text-white mb-1">Reset Password</h3>
        <p class="text-secondary small mb-0">Enter your email and choose a new password</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> py-2.5 px-3 small rounded-3 mb-4">
            <i class="fa-solid <?= $messageType == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-1"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($messageType === 'success'): ?>
        <a href="login.php" class="btn-submit text-center text-decoration-none d-block mb-3">
            <span>Proceed to Login</span>
            <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">Registered Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary small fw-semibold">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" required>
            </div>

            <button type="submit" class="btn-submit mb-3">
                <span>Update Password</span>
                <i class="fa-solid fa-check ms-1"></i>
            </button>
        </form>
    <?php endif; ?>

    <div class="text-center mt-3">
        <small class="text-secondary">
            Remembered your password? <a href="login.php" style="color: #a5b4fc; font-weight: 600;" class="text-decoration-none">Sign In here</a>
        </small>
    </div>
</div>

<script src="dist/js/crafted.js"></script>
</body>
</html>

