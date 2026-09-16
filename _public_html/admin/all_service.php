<?php
$pageTitle = "Stock Inventory";
require_once __DIR__ . "/header.php";

$message = null;

// Handle Delete Account
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $delStmt = $conn->prepare("DELETE FROM sell WHERE id = ?");
    $delStmt->bind_param("i", $delId);
    if ($delStmt->execute()) {
        $message = ["type" => "success", "text" => "Account credential removed from inventory."];
    }
}

// Category Filter
$filterCatId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$statusFilter = isset($_GET['status']) ? (int)$_GET['status'] : 0; // 0=all, 1=available, 2=sold

// Build Query
$whereClauses = [];
if ($filterCatId > 0) {
    $whereClauses[] = "s.category_id = $filterCatId";
}
if ($statusFilter > 0) {
    $whereClauses[] = "s.status = $statusFilter";
}
$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$accountsQuery = $conn->query("
    SELECT s.*, c.name as cat_name, c.logo_url 
    FROM sell s 
    LEFT JOIN categorys c ON s.category_id = c.id 
    $whereSql 
    ORDER BY s.id DESC 
    LIMIT 200
");

// Category options for dropdown
$categories = $conn->query("SELECT id, name FROM categorys ORDER BY name ASC");

// Quick Counts
$totalStock = (int)($conn->query("SELECT COUNT(*) as c FROM sell")->fetch_assoc()['c'] ?? 0);
$availableCount = (int)($conn->query("SELECT COUNT(*) as c FROM sell WHERE status='1'")->fetch_assoc()['c'] ?? 0);
$soldCount = (int)($conn->query("SELECT COUNT(*) as c FROM sell WHERE status='2'")->fetch_assoc()['c'] ?? 0);
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Stock Inventory</h3>
        <p class="text-secondary small mb-0">Browse and manage digital account credentials in stock</p>
    </div>
    <div class="d-flex gap-2">
        <a href="service.php" class="btn-gradient">
            <i class="fa-solid fa-plus"></i> Add Accounts
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid <?= $message['type'] == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
        <?= htmlspecialchars($message['text']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Quick Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
            <span class="text-secondary small fw-semibold">Total Stock Registered:</span>
            <strong class="text-white fs-5"><?= $totalStock ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
            <span class="text-secondary small fw-semibold">Available for Sale:</span>
            <strong class="text-success fs-5"><?= $availableCount ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
            <span class="text-secondary small fw-semibold">Accounts Sold:</span>
            <strong class="text-secondary fs-5"><?= $soldCount ?></strong>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="dash-card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <select name="cat_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filterCatId == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0" <?= $statusFilter == 0 ? 'selected' : '' ?>>All Statuses</option>
                <option value="1" <?= $statusFilter == 1 ? 'selected' : '' ?>>Only Available (In Stock)</option>
                <option value="2" <?= $statusFilter == 2 ? 'selected' : '' ?>>Only Sold</option>
            </select>
        </div>

        <div class="col-md-3">
            <?php if ($filterCatId > 0 || $statusFilter > 0): ?>
                <a href="all_service.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-xmark me-1"></i> Reset Filters
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Accounts Inventory Table -->
<div class="dash-card">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Category</th>
                    <th>Username / Credentials</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($accountsQuery && $accountsQuery->num_rows > 0): ?>
                    <?php while ($acc = $accountsQuery->fetch_assoc()): ?>
                        <tr>
                            <td class="text-secondary small">#<?= $acc['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="../<?= htmlspecialchars($acc['logo_url'] ?? 'assets/icons/netflix.svg') ?>" 
                                         class="mini-icon" 
                                         alt=""
                                         onerror="this.onerror=null; this.src='../assets/icons/netflix.svg';">
                                    <span class="small fw-semibold text-white"><?= htmlspecialchars($acc['cat_name'] ?? 'Unknown') ?></span>
                                </div>
                            </td>
                            <td>
                                <code class="text-info small" title="<?= htmlspecialchars($acc['username']) ?>">
                                    <?= htmlspecialchars($acc['username']) ?>
                                </code>
                            </td>
                            <td>
                                <code class="text-secondary small">
                                    <?= htmlspecialchars($acc['password']) ?>
                                </code>
                            </td>
                            <td>
                                <?php if ($acc['status'] == 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-check"></i> Sold
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="all_service.php?delete=<?= $acc['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1"
                                   onclick="return confirm('Delete this account credential permanently?');">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            No accounts found matching your selected filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
