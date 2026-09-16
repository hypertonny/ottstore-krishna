<?php
require_once __DIR__ . "/auth.php";

$isLoggedIn = isset($_SESSION['auth']) && isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'User';

// WhatsApp & Support Details
$whatsappNumber = defined('SUPPORT_WHATSAPP') ? SUPPORT_WHATSAPP : '13082229928';
$supportEmail = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'buy@ottstore.help';
$telegramHandle = defined('SUPPORT_TELEGRAM') ? SUPPORT_TELEGRAM : 'ottbuyofficial';

// Safe check for payment state
$paymentDone = isset($_SESSION['payment_done']) && $_SESSION['payment_done'] === true;
$idPass = $_SESSION['id_pass'] ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTT STORE PREMIUM | Instant Digital Subscriptions</title>
    <meta name="description" content="Buy genuine Netflix, Prime Video, Hotstar, YouTube Premium, Spotify, and OTT subscriptions at cheap prices with instant automated delivery.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #090d16;
            --bg-card: #111827;
            --bg-card-hover: #162032;
            --border-card: rgba(255, 255, 255, 0.08);
            --border-card-hover: rgba(99, 102, 241, 0.4);
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            --accent-cyan: #06b6d4;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --success-color: #10b981;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Subtle radial glowing background */
        .bg-glow {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            height: 480px;
            background: radial-gradient(circle at 50% 20%, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.08) 40%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-card);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: var(--accent-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            padding: 48px 0 24px 0;
            text-align: center;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 999px;
            color: #a5b4fc;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #a5b4fc 0%, #ffffff 50%, #e879f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 650px;
            margin: 0 auto 28px auto;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            font-size: 0.9rem;
            color: #cbd5e1;
        }

        .hero-features span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Product Cards Grid */
        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            background: var(--bg-card-hover);
            border-color: var(--border-card-hover);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.5), 0 0 24px -4px rgba(99, 102, 241, 0.15);
        }

        .product-card:hover::before {
            opacity: 1;
        }

        .card-header-flex {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .logo-box {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            line-height: 1.3;
        }

        .stock-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            margin-top: 4px;
        }

        .stock-in {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .stock-out {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .price-badge {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: baseline;
            gap: 2px;
        }

        .price-symbol {
            color: #818cf8;
            font-size: 1rem;
        }

        .price-period {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .info-box {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 14px;
            margin: 16px 0 20px 0;
            font-size: 0.86rem;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .info-box p {
            margin: 0;
        }

        /* Modern Buy Button */
        .btn-buy-active {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-buy-active:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .btn-buy-disabled {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #64748b;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 12px;
            text-align: center;
            display: block;
            width: 100%;
            cursor: not-allowed;
        }

        /* Floating Support Badges */
        .floating-support {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 1050;
        }

        .float-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .float-btn:hover {
            color: #fff;
            transform: scale(1.12);
        }

        .float-wa {
            background: #25D366;
        }

        .float-tg {
            background: #229ED9;
        }

        /* Footer */
        .footer-custom {
            margin-top: auto;
            background: #0b111e;
            border-top: 1px solid var(--border-card);
            padding: 32px 0 24px 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            margin: 0 12px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>

<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="brand-logo" href="index.php">
            <div class="brand-icon">
                <i class="fa-solid fa-play"></i>
            </div>
            <span>OTT<span style="color:#818cf8;">STORE</span></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <i class="fa-solid fa-bars fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link text-white fw-medium px-3" href="index.php"><i class="fa-solid fa-house me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link text-secondary fw-medium px-3" href="#store"><i class="fa-solid fa-layer-group me-1"></i> Subscriptions</a></li>
                <li class="nav-item"><a class="nav-link text-secondary fw-medium px-3" href="https://wa.me/<?= htmlspecialchars($whatsappNumber) ?>" target="_blank"><i class="fa-brands fa-whatsapp me-1"></i> Support</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                        <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($username) ?>
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 rounded-pill">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm px-3 rounded-pill">Login</a>
                    <a href="register.php" class="btn btn-buy-active btn-sm px-3 rounded-pill">Create Account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-badge">
            <i class="fa-solid fa-bolt text-warning"></i> Instant Automated UPI Delivery
        </div>
        <h1 class="hero-title">
            Premium OTT Subscriptions<br>
            <span class="gradient-text">At Honest & Affordable Prices</span>
        </h1>
        <p class="hero-subtitle">
            Get instant access to Netflix, Prime Video, Disney+ Hotstar, Spotify, and more. 100% genuine private accounts with instant delivery and full warranty.
        </p>

        <div class="hero-features">
            <span><i class="fa-solid fa-circle-check text-success"></i> Instant Credentials Release</span>
            <span><i class="fa-solid fa-shield-halved text-primary"></i> 30 Days Replacement Warranty</span>
            <span><i class="fa-solid fa-qrcode text-info"></i> Direct QR & UPI Payment</span>
            <span><i class="fa-solid fa-headset text-warning"></i> 24/7 WhatsApp Support</span>
        </div>
    </div>
</section>

<!-- Product Catalog -->
<main class="container py-4 my-2" id="store" style="position: relative; z-index: 1;">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <h3 class="fw-bold mb-1">Available Subscriptions</h3>
            <p class="text-secondary small mb-0">Select your preferred streaming service to order instantly</p>
        </div>
        <span class="badge bg-dark border border-secondary text-secondary px-3 py-2 rounded-pill">
            <i class="fa-solid fa-cube me-1"></i> Live Inventory
        </span>
    </div>

    <div class="row g-4">
        <?php
        $sql = $conn->query("SELECT * FROM categorys ORDER BY id ASC");
        if ($sql && $sql->num_rows > 0) {
            while ($data = $sql->fetch_assoc()) {
                $cat_id = (int)$data['id'];
                
                // Count real available stock with status = 1
                $stockQuery = $conn->query("SELECT COUNT(*) as count FROM sell WHERE category_id='$cat_id' AND status='1'");
                $stockRow = $stockQuery->fetch_assoc();
                $count = (int)$stockRow['count'];
                
                // Format description safely: replace literal '<br>' with real HTML linebreaks, strip dangerous tags
                $rawInfo = $data['info'];
                // Clean up any historical double-escaped artifacts
                $cleanInfo = str_ireplace(['&lt;br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'], '<br>', $rawInfo);
                $cleanInfo = strip_tags($cleanInfo, '<br><strong><b><i>');
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="product-card">
                <div>
                    <!-- Header with Logo and Title -->
                    <div class="card-header-flex">
                        <div class="logo-box">
                            <img src="<?= htmlspecialchars($data['logo_url']) ?>" 
                                 alt="<?= htmlspecialchars($data['name']) ?>"
                                 onerror="this.onerror=null; this.src='assets/icons/netflix.svg';">
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="product-title"><?= htmlspecialchars($data['name']) ?></h5>
                            <?php if ($count > 0): ?>
                                <span class="stock-tag stock-in">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> <?= $count ?> in stock
                                </span>
                            <?php else: ?>
                                <span class="stock-tag stock-out">
                                    <i class="fa-solid fa-circle-xmark"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Price Display -->
                    <div class="d-flex align-items-baseline justify-content-between my-2">
                        <div class="price-badge">
                            <span class="price-symbol">&#8377;</span><?= number_format($data['amount'], 0) ?>
                            <span class="price-period">/ account</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded">
                            Instant Delivery
                        </span>
                    </div>

                    <!-- Features & Specs Box -->
                    <div class="info-box">
                        <?php 
                        $lines = preg_split('/<br\s*\/?>|\n/i', $data['info']);
                        foreach ($lines as $line) {
                            $cleanLine = trim(preg_replace('/^[✓✔•\?\-\*]\s*/u', '', $line));
                            if (!empty($cleanLine)) {
                                echo '<div class="d-flex align-items-center gap-2 mb-1.5"><i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 0.8rem;"></i><span>' . htmlspecialchars($cleanLine) . '</span></div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <?php if ($count > 0): ?>
                        <a href="check.php?id=<?= $data['id'] ?>" class="btn-buy-active">
                            <span>Buy Now</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <button class="btn-buy-disabled" disabled>
                            <i class="fa-solid fa-lock me-1"></i> Sold Out
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            echo '<div class="col-12 text-center py-5 text-secondary">No categories found in database.</div>';
        }
        ?>
    </div>

    <!-- Payment ID/Pass display if active session exists -->
    <?php if ($paymentDone && !empty($idPass)): ?>
        <div class="card bg-success bg-opacity-10 border border-success mt-4 p-3 text-center">
            <h5 class="text-success fw-bold"><i class="fa-solid fa-circle-check me-2"></i>Order Completed!</h5>
            <p class="mb-2">Your Credentials:</p>
            <code class="fs-5 text-light bg-dark p-2 rounded d-inline-block"><?= htmlspecialchars($idPass) ?></code>
        </div>
    <?php endif; ?>
</main>

<!-- Floating Support Buttons -->
<div class="floating-support">
    <a href="https://wa.me/<?= htmlspecialchars($whatsappNumber) ?>?text=<?= urlencode('Hi, I need help regarding OTT subscription purchase.') ?>" 
       class="float-btn float-wa" 
       target="_blank" 
       title="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <a href="https://t.me/<?= htmlspecialchars($telegramHandle) ?>" 
       class="float-btn float-tg" 
       target="_blank" 
       title="Join Telegram Channel">
        <i class="fa-brands fa-telegram"></i>
    </a>
</div>

<!-- Footer -->
<footer class="footer-custom text-center">
    <div class="container">
        <div class="mb-3 footer-links">
            <a href="index.php">Home</a>
            <a href="About.html">About Us</a>
            <a href="PrivacyPolicy.html">Privacy Policy</a>
            <a href="terms-and-conditions.html">Terms of Service</a>
            <a href="admin/index.php" class="text-muted"><i class="fa-solid fa-shield-cat me-1"></i>Admin</a>
        </div>
        <p class="mb-1 text-secondary">
            OTT STORE PREMIUM &copy; <?= date("Y") ?>. All rights reserved. Instant Digital Subscriptions.
        </p>
        <small class="text-muted">Disclaimer: All logos and trademarks are properties of their respective owners.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
