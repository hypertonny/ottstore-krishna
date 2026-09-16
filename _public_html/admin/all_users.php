<?php
$pageTitle = "Customer Accounts";
require_once __DIR__ . "/header.php";

$message = "";
$messageType = "";

// Delete customer
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delStmt->bind_param("i", $delId);
        if ($delStmt->execute()) {
            $message = "Customer account #$delId deleted successfully.";
            $messageType = "success";
        } else {
            $message = "Error deleting customer.";
            $messageType = "danger";
        }
    }
}

// Search
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $searchPattern = '%' . $search . '%';
    $stmt = $conn->prepare("
        SELECT 
            u.*, 
            COUNT(ph.id) AS total_orders, 
            COALESCE(SUM(ph.price), 0) AS total_spent
        FROM users u
        LEFT JOIN purchase_history ph ON u.id = ph.user_id
        WHERE u.username LIKE ? OR u.email LIKE ?
        GROUP BY u.id
        ORDER BY u.id DESC
    ");
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
    $stmt->execute();
    $usersRes = $stmt->get_result();
} else {
    $usersRes = $conn->query("
        SELECT 
            u.*, 
            COUNT(ph.id) AS total_orders, 
            COALESCE(SUM(ph.price), 0) AS total_spent
        FROM users u
        LEFT JOIN purchase_history ph ON u.id = ph.user_id
        GROUP BY u.id
        ORDER BY u.id DESC
    ");
}

$totalCustomers = (int)($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0);
$totalOrdersPlaced = (int)($conn->query("SELECT COUNT(*) as c FROM purchase_history")->fetch_assoc()['c'] ?? 0);
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Customer Accounts</h3>
        <p class="text-secondary small mb-0">Manage registered customers, view purchasing history, and user activity</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
            <i class="fa-solid fa-users me-1"></i> Total Users: <?= $totalCustomers ?>
        </span>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
            <i class="fa-solid fa-bag-shopping me-1"></i> User Orders: <?= $totalOrdersPlaced ?>
        </span>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Search Bar -->
<div class="dash-card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name or email address..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Filter</button>
            <?php if (!empty($search)): ?>
                <a href="all_users.php" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="dash-card">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Customer</th>
                    <th>Email Address</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>
                    <th>Joined Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usersRes && $usersRes->num_rows > 0): ?>
                    <?php 
                    $i = 1;
                    while ($user = $usersRes->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="text-secondary small"><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff; font-size: 0.85rem;">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white"><?= htmlspecialchars($user['username']) ?></div>
                                        <small class="text-secondary">UID: #<?= $user['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-light small"><?= htmlspecialchars($user['email']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1">
                                    <?= (int)$user['total_orders'] ?> Orders
                                </span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">&#8377;<?= number_format((float)$user['total_spent'], 2) ?></span>
                            </td>
                            <td class="text-secondary small">
                                <?= !empty($user['created_at']) ? date("d M Y, h:i A", strtotime($user['created_at'])) : 'Unknown' ?>
                            </td>
                            <td class="text-end">
                                <a href="all_users.php?delete=<?= $user['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1"
                                   onclick="return confirm('Are you sure you want to delete customer <?= htmlspecialchars(addslashes($user['username'])) ?>? This cannot be undone.')">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="fa-solid fa-user-slash fs-1 d-block mb-3 opacity-25"></i>
                            No registered customers found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
