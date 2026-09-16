<?php 
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../rate_limiter.php";

if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
$rateLimit = isRateLimited($conn, 'admin_login', 5, 15);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['access_code'])) {
    if ($rateLimit['limited']) {
        $error = "Too many failed administrative attempts. For security reasons, admin access from this IP is locked for " . $rateLimit['retry_after_minutes'] . " minutes.";
    } else {
        $code = trim($_POST['access_code']);
        
        if (empty($code)) {
            $error = "Please enter your administrative access code.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM login WHERE username = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows > 0) {
                clearFailedAttempts($conn, 'admin_login');
                $_SESSION['auth'] = true;
                $_SESSION['admin_auth'] = true;
                $_SESSION['admin_user'] = $code;
                header('Location: dashboard.php');
                exit;
            } else {
                recordFailedAttempt($conn, 'admin_login');
                $rem = max(0, 5 - ($rateLimit['attempts'] + 1));
                $error = "Invalid admin access code. Access denied." . ($rem > 0 ? " ($rem attempts remaining before lockout)" : " Administrative access locked.");
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
    <title>Admin Portal Login | OTT Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #080c14;
            --bg-card: #0f172a;
            --border-card: rgba(255, 255, 255, 0.08);
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
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

        .login-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.6), 0 0 32px -4px rgba(99, 102, 241, 0.15);
            text-align: center;
        }

        .shield-icon {
            width: 56px;
            height: 56px;
            background: var(--accent-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.6rem;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            padding: 13px 18px;
            border-radius: 12px;
            font-size: 1rem;
            letter-spacing: 1px;
            text-align: center;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: #818cf8;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-enter {
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

        .btn-enter:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="shield-icon">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    
    <h3 class="fw-bold text-white mb-1">Admin Control Portal</h3>
    <p class="text-secondary small mb-4">Enter administrative access code to proceed</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 text-start">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4 text-start">
            <label class="form-label text-secondary small fw-semibold">Access Code / Password</label>
            <input type="password" class="form-control" name="access_code" placeholder="Enter Access Code" required autofocus>
        </div>

        <button type="submit" class="btn-enter mb-3">
            <i class="fa-solid fa-lock-open me-2"></i> Unlock Admin Panel
        </button>

        <div class="mt-3 pt-3 border-top border-secondary border-opacity-25">
            <a href="../index.php" class="text-secondary small text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i> Return to Storefront
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
