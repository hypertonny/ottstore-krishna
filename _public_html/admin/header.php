<?php
require_once __DIR__ . "/../auth.php";

if (!isset($_SESSION['auth'])) {
    header('Location: ./index.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?? 'Admin Control Center' ?> | OTT Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

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

        /* Section Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.4);
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
            padding: 14px;
        }

        .table-custom td {
            padding: 14px;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .btn-gradient {
            background: var(--accent-gradient);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 18px;
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

        .form-control, .form-select {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #f8fafc;
            padding: 12px 16px;
            border-radius: 12px;
        }

        .form-control:focus, .form-select:focus {
            background-color: #1e293b;
            border-color: #818cf8;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-select option, select option {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            padding: 10px 14px;
        }

        .mini-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
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
        <a href="dashboard.php" class="nav-link-custom <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>

        <span class="nav-heading">Catalog & Stock</span>
        <a href="all_category.php" class="nav-link-custom <?= $currentPage == 'all_category.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>All Categories</span>
        </a>
        <a href="category.php" class="nav-link-custom <?= $currentPage == 'category.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Add Category</span>
        </a>
        <a href="all_service.php" class="nav-link-custom <?= $currentPage == 'all_service.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Stock Inventory</span>
        </a>
        <a href="service.php" class="nav-link-custom <?= $currentPage == 'service.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-plus"></i>
            <span>Add Stock Accounts</span>
        </a>

        <span class="nav-heading">Sales & Customers</span>
        <a href="all_transaction.php" class="nav-link-custom <?= $currentPage == 'all_transaction.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-receipt"></i>
            <span>Sales Transactions</span>
        </a>
        <a href="all_users.php" class="nav-link-custom <?= $currentPage == 'all_users.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Customer Accounts</span>
        </a>

        <span class="nav-heading">System & Config</span>
        <a href="settings.php" class="nav-link-custom <?= $currentPage == 'settings.php' ? 'active' : '' ?>">
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

<main class="admin-main">
