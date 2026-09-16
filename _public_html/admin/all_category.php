<?php
$pageTitle = "Category Management";
require_once __DIR__ . "/header.php";

$message = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categorys WHERE id = ?");
    $stmt->bind_param("i", $delId);
    if ($stmt->execute()) {
        $message = ["type" => "success", "text" => "Category deleted successfully."];
    }
}

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $info = trim($_POST['info'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $logo_url = trim($_POST['logo_url'] ?? '');

    if (!empty($name) && $amount > 0) {
        $updateStmt = $conn->prepare("UPDATE categorys SET name = ?, info = ?, amount = ?, logo_url = ? WHERE id = ?");
        $updateStmt->bind_param("ssdsi", $name, $info, $amount, $logo_url, $id);
        if ($updateStmt->execute()) {
            $message = ["type" => "success", "text" => "Category updated successfully."];
        } else {
            $message = ["type" => "danger", "text" => "Failed to update category."];
        }
    }
}

// Check if editing a specific category
$editingCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editStmt = $conn->prepare("SELECT * FROM categorys WHERE id = ?");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $editingCategory = $editStmt->get_result()->fetch_assoc();
}

// Fetch all categories with real available stock
$categories = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM sell WHERE category_id = c.id AND status = '1') as real_stock 
    FROM categorys c 
    ORDER BY c.id ASC
");
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Subscription Categories</h3>
        <p class="text-secondary small mb-0">Manage services, subscription pricing, and catalog details</p>
    </div>
    <a href="category.php" class="btn-gradient">
        <i class="fa-solid fa-plus"></i> Add New Category
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid <?= $message['type'] == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
        <?= htmlspecialchars($message['text']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Edit Category Card (If Selected) -->
<?php if ($editingCategory): ?>
    <div class="dash-card mb-4 border-primary border-opacity-50">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-white mb-0">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Category #<?= $editingCategory['id'] ?>
            </h5>
            <a href="all_category.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Cancel</a>
        </div>

        <form method="POST">
            <input type="hidden" name="update_category" value="1">
            <input type="hidden" name="id" value="<?= $editingCategory['id'] ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-semibold">Category Title</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editingCategory['name']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-semibold">Unit Price (INR)</label>
                    <input type="number" step="1" name="amount" class="form-control" value="<?= htmlspecialchars($editingCategory['amount']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-semibold">Logo URL / Icon Path</label>
                    <input type="text" name="logo_url" class="form-control" value="<?= htmlspecialchars($editingCategory['logo_url']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label text-secondary small fw-semibold">Features / Specs (HTML &lt;br&gt; separated)</label>
                    <textarea name="info" class="form-control" rows="3" required><?= htmlspecialchars($editingCategory['info']) ?></textarea>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn-gradient">
                    <i class="fa-solid fa-check me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Categories Table -->
<div class="dash-card">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Service Name</th>
                    <th>Price</th>
                    <th>Live Stock</th>
                    <th>Features Summary</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td class="text-secondary small">#<?= $cat['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="../<?= htmlspecialchars($cat['logo_url']) ?>" 
                                         class="mini-icon" 
                                         alt=""
                                         onerror="this.onerror=null; this.src='../assets/icons/netflix.svg';">
                                    <strong class="text-white"><?= htmlspecialchars($cat['name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-white">&#8377;<?= number_format($cat['amount'], 0) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $cat['real_stock'] > 0 ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $cat['real_stock'] > 0 ? 'text-success' : 'text-danger' ?> border <?= $cat['real_stock'] > 0 ? 'border-success' : 'border-danger' ?> border-opacity-25 px-2.5 py-1.5 rounded-pill">
                                    <?= $cat['real_stock'] ?> in stock
                                </span>
                            </td>
                            <td class="text-secondary small" style="max-width: 320px;">
                                <div class="text-truncate" title="<?= htmlspecialchars(strip_tags($cat['info'])) ?>">
                                    <?= htmlspecialchars(strip_tags(str_replace('<br>', ' • ', $cat['info']))) ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="all_category.php?edit=<?= $cat['id'] ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 me-1">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                                <a href="all_category.php?delete=<?= $cat['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1"
                                   onclick="return confirm('Are you sure you want to delete category \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?');">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            No categories created yet. Click "Add New Category" above to create one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
