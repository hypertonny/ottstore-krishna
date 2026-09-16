<?php
require_once __DIR__ . "/../auth.php";

if (!isset($_SESSION['auth'])) {
    header('Location: ./index.php');
    exit;
}

// Fetch Real-time Analytics & Stats
$totalCategories = (int)($conn->query("SELECT COUNT(*) as c FROM categorys")->fetch_assoc()['c'] ?? 0);
$availableStock = (int)($conn->query("SELECT COUNT(*) as c FROM sell WHERE status='1'")->fetch_assoc()['c'] ?? 0);
$totalSold = (int)($conn->query("SELECT COUNT(*) as c FROM sold")->fetch_assoc()['c'] ?? 0);

// Calculate total estimated revenue from sold items
$revenueQuery = $conn->query("
    SELECT SUM(c.amount) as total_rev 
    FROM sold s 
    INNER JOIN sell se ON s.username = se.username 
    INNER JOIN categorys c ON se.category_id = c.id
");
$revenueRow = $revenueQuery ? $revenueQuery->fetch_assoc() : null;
$totalRevenue = (float)($revenueRow['total_rev'] ?? 0);

// If no join match (e.g. manual entry), calculate estimated revenue from sold count * avg amount
if ($totalRevenue <= 0 && $totalSold > 0) {
    $avgQuery = $conn->query("SELECT AVG(amount) as avg_p FROM categorys");
    $avgP = (float)($avgQuery->fetch_assoc()['avg_p'] ?? 100);
    $totalRevenue = $totalSold * $avgP;
}

// Stock breakdown per category
$categoriesList = $conn->query("
    SELECT c.id, c.name, c.amount, c.logo_url, 
           (SELECT COUNT(*) FROM sell WHERE category_id = c.id AND status = '1') as in_stock,
           (SELECT COUNT(*) FROM sell WHERE category_id = c.id AND status = '2') as sold_count
    FROM categorys c 
    ORDER BY in_stock ASC
");

// Recent 6 transactions
$recentTransactions = $conn->query("SELECT * FROM sold ORDER BY id DESC LIMIT 6");

// Customer accounts count
$usersCount = (int)($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | OTT Store Control Center</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #080c14;
            --bg-card: #0f172a;
            --bg-sidebar: #0b1120;
            --border-card: rgba(255, 255, 255, 0.08);
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            --accent-indigo: #6366f1;
        }

        body {
            background-color: var(--bg-body);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-card);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid var(--border-card);
        }

        .brand-badge {
            background: var(--accent-gradient);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.95rem;
        }

        .sidebar-nav {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
        }

        .nav-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            font-weight: 700;
            padding: 12px 14px 4px 14px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .nav-link-custom:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link-custom.active {
            color: #fff;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .nav-link-custom i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        /* Main Content Container */
        .admin-main {
            margin-left: 260px;
            flex-grow: 1;
            padding: 32px;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        /* Stat Cards */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.25s, border-color 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .icon-indigo { background: rgba(99, 102, 241, 0.12); color: #818cf8; }
        .icon-emerald { background: rgba(16, 185, 129, 0.12); color: #34d399; }
        .icon-amber { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
        .icon-rose { background: rgba(244, 63, 94, 0.12); color: #fb7185; }

        .stat-val {
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }

        .stat-lbl {
            font-size: 0.82rem;
            color: #94a3b8;
            font-weight: 600;
            margin-top: 4px;
        }

        /* Section Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 24px;
            height: 100%;
        }

        .dash-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #f8fafc;
            --bs-table-border-color: rgba(255, 255, 255, 0.06);
            margin-bottom: 0;
        }

        .table-custom th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 14px;
        }

        .table-custom td {
            padding: 12px 14px;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .btn-gradient {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 16px;
            font-size: 0.88rem;
            transition: opacity 0.2s, transform 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-gradient:hover {
            color: #fff;
            transform: translateY(-2px);
            opacity: 0.95;
        }

        .mini-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            padding: 4px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<!-- Left Navigation Sidebar -->
<aside class="admin-sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <div class="brand-badge"><i class="fa-solid fa-shield-halved"></i></div>
        <span>OTT<span style="color:#818cf8;">ADMIN</span></span>
    </a>

    <div class="sidebar-nav">
        <span class="nav-heading">Main</span>
        <a href="dashboard.php" class="nav-link-custom active">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>

        <span class="nav-heading">Catalog & Stock</span>
        <a href="all_category.php" class="nav-link-custom">
            <i class="fa-solid fa-layer-group"></i>
            <span>All Categories</span>
        </a>
        <a href="category.php" class="nav-link-custom">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Add Category</span>
        </a>
        <a href="all_service.php" class="nav-link-custom">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Stock Inventory</span>
        </a>
        <a href="service.php" class="nav-link-custom">
            <i class="fa-solid fa-plus"></i>
            <span>Add Stock Accounts</span>
        </a>

        <span class="nav-heading">Sales & Customers</span>
        <a href="all_transaction.php" class="nav-link-custom">
            <i class="fa-solid fa-receipt"></i>
            <span>Sales Transactions</span>
        </a>
        <a href="all_users.php" class="nav-link-custom">
            <i class="fa-solid fa-users"></i>
            <span>Customer Accounts</span>
        </a>

        <span class="nav-heading">System & Config</span>
        <a href="settings.php" class="nav-link-custom">
            <i class="fa-solid fa-sliders"></i>
            <span>Store & Gateway</span>
        </a>

        <span class="nav-heading">Shortcuts</span>
        <a href="../index.php" target="_blank" class="nav-link-custom">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>Live Storefront</span>
        </a>
        <a href="logout.php" class="nav-link-custom text-danger">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="p-3 border-top border-secondary border-opacity-25">
        <small class="text-secondary d-block">Admin Session Active</small>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
            <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Server Online
        </span>
    </div>
</aside>

<!-- Main Workspace -->
<main class="admin-main">
    <!-- Top Header -->
    <div class="top-header">
        <div>
            <h3 class="fw-bold text-white mb-1">Administrative Overview</h3>
            <p class="text-secondary small mb-0">Monitor catalog, stock reserves, and recent deliveries</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="service.php" class="btn-gradient">
                <i class="fa-solid fa-plus"></i> Add Accounts
            </a>
            <a href="category.php" class="btn btn-outline-light btn-sm px-3 rounded-3" style="padding: 9px 16px;">
                <i class="fa-solid fa-folder-plus me-1"></i> New Category
            </a>
        </div>
    </div>

    <!-- 4 KPI Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalCategories ?></div>
                    <div class="stat-lbl">Active Categories</div>
                </div>
                <div class="stat-icon icon-indigo">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="stat-val text-emerald-400" style="color: #34d399;"><?= $availableStock ?></div>
                    <div class="stat-lbl">Accounts In Stock</div>
                </div>
                <div class="stat-icon icon-emerald">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $totalSold ?></div>
                    <div class="stat-lbl">Accounts Delivered</div>
                </div>
                <div class="stat-icon icon-amber">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="stat-val">&#8377;<?= number_format($totalRevenue, 0) ?></div>
                    <div class="stat-lbl">Delivered Volume</div>
                </div>
                <div class="stat-icon icon-rose">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Inventory Health & Recent Orders -->
    <div class="row g-4">
        <!-- Stock Level Overview -->
        <div class="col-lg-7">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="fa-solid fa-boxes-stacked text-indigo-400 me-2" style="color: #818cf8;"></i>Inventory Stock Health
                    </h5>
                    <a href="all_service.php" class="text-secondary small text-decoration-none">
                        View All Stock <i class="fa-solid fa-angle-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>In Stock</th>
                                <th>Status</th>
                                <th class="text-end">Quick Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categoriesList->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <img src="../<?= htmlspecialchars($cat['logo_url']) ?>" 
                                                 alt="" 
                                                 class="mini-icon"
                                                 onerror="this.onerror=null; this.src='../assets/icons/netflix.svg';">
                                            <span class="fw-semibold text-white"><?= htmlspecialchars($cat['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>&#8377;<?= number_format($cat['amount'], 0) ?></td>
                                    <td>
                                        <span class="fw-bold <?= $cat['in_stock'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $cat['in_stock'] ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cat['in_stock'] > 0): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                Available
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                Restock Needed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="service.php?cat_id=<?= $cat['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1" style="font-size:0.75rem;">
                                            <i class="fa-solid fa-plus me-1"></i> Add Stock
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Sales & Transactions -->
        <div class="col-lg-5">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="fa-solid fa-receipt text-indigo-400 me-2" style="color: #818cf8;"></i>Recent Deliveries
                    </h5>
                    <a href="all_transaction.php" class="text-secondary small text-decoration-none">
                        All Records <i class="fa-solid fa-angle-right ms-1"></i>
                    </a>
                </div>

                <?php if ($recentTransactions && $recentTransactions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>UTR / Ref ID</th>
                                    <th>Delivered Account</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tx = $recentTransactions->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <code class="text-indigo-300 small" style="color:#a5b4fc;"><?= htmlspecialchars($tx['txn_id']) ?></code>
                                        </td>
                                        <td>
                                            <div class="small text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($tx['username']) ?>">
                                                <?= htmlspecialchars($tx['username']) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-secondary">
                        <i class="fa-solid fa-receipt fs-2 mb-2 opacity-50"></i>
                        <p class="small mb-0">No sales transactions recorded yet.</p>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between">
                    <small class="text-secondary">Registered Customers: <strong class="text-white"><?= $usersCount ?></strong></small>
                    <a href="../download.php" target="_blank" class="text-indigo-400 small text-decoration-none" style="color: #818cf8;">
                        Test Download Page &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
