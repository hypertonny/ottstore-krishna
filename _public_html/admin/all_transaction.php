<?php
$pageTitle = "Sales Transactions";
require_once __DIR__ . "/header.php";

$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $searchPattern = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM sold WHERE txn_id LIKE ? OR username LIKE ? ORDER BY id DESC LIMIT 250");
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
    $stmt->execute();
    $salesQuery = $stmt->get_result();
} else {
    $salesQuery = $conn->query("SELECT * FROM sold ORDER BY id DESC LIMIT 250");
}
$totalSold = (int)($conn->query("SELECT COUNT(*) as c FROM sold")->fetch_assoc()['c'] ?? 0);
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Delivered Sales & Orders</h3>
        <p class="text-secondary small mb-0">Audit delivered credentials, bank reference IDs, and customer receipts</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
            <i class="fa-solid fa-receipt me-1"></i> Total Orders: <?= $totalSold ?>
        </span>
        <button class="btn btn-outline-light btn-sm px-3 rounded-pill" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="dash-card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by UTR or Username..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Filter</button>
            <?php if (!empty($search)): ?>
                <a href="all_transaction.php" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="dash-card">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Bank UTR / Reference</th>
                    <th>Delivered Username / Data</th>
                    <th>Password</th>
                    <th>Date Delivered</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($salesQuery && $salesQuery->num_rows > 0): ?>
                    <?php 
                    $i = 1;
                    while ($tx = $salesQuery->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="text-secondary small"><?= $i++ ?></td>
                            <td>
                                <code class="text-info fw-bold" style="letter-spacing: 0.5px;">
                                    <?= htmlspecialchars($tx['txn_id']) ?>
                                </code>
                            </td>
                            <td>
                                <span class="text-white small font-monospace">
                                    <?= htmlspecialchars($tx['username']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary small font-monospace">
                                    <?= htmlspecialchars($tx['password']) ?>
                                </span>
                            </td>
                            <td class="text-secondary small">
                                <?= !empty($tx['created_at']) ? date("d M Y, h:i A", strtotime($tx['created_at'])) : 'Completed' ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1" 
                                        onclick="navigator.clipboard.writeText('<?= htmlspecialchars(addslashes($tx['username'])) ?> | <?= htmlspecialchars(addslashes($tx['password'])) ?>'); alert('Credentials copied!');"
                                        title="Copy Credentials">
                                    <i class="fa-regular fa-copy"></i> Copy
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            No sales transactions found matching your search.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
