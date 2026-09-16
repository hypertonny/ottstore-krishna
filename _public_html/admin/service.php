<?php
$pageTitle = "Add Stock Accounts";
require_once __DIR__ . "/header.php";

$message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cat_id = (int)($_POST['cat_id'] ?? 0);
    $mode = $_POST['mode'] ?? 'single';

    if ($cat_id <= 0) {
        $message = ["type" => "warning", "text" => "Please select a valid category."];
    } else {
        if ($mode === 'single') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($username) && !empty($password)) {
                $stmt = $conn->prepare("INSERT INTO sell (category_id, username, password, status) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("iss", $cat_id, $username, $password);
                if ($stmt->execute()) {
                    $message = ["type" => "success", "text" => "Account credential added successfully!"];
                } else {
                    $message = ["type" => "danger", "text" => "Failed to add account."];
                }
            } else {
                $message = ["type" => "warning", "text" => "Please provide both username and password."];
            }
        } elseif ($mode === 'bulk') {
            $bulkData = trim($_POST['bulk_accounts'] ?? '');
            $lines = explode("\n", $bulkData);
            $inserted = 0;

            $stmt = $conn->prepare("INSERT INTO sell (category_id, username, password, status) VALUES (?, ?, ?, 1)");

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Support both "user:password" and "user|password" formats
                if (strpos($line, ':') !== false) {
                    $parts = explode(':', $line, 2);
                } elseif (strpos($line, '|') !== false) {
                    $parts = explode('|', $line, 2);
                } else {
                    $parts = [$line, ''];
                }

                $u = trim($parts[0]);
                $p = trim($parts[1] ?? '');

                if (!empty($u)) {
                    $stmt->bind_param("iss", $cat_id, $u, $p);
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                }
            }

            if ($inserted > 0) {
                $message = ["type" => "success", "text" => "Successfully imported $inserted accounts into inventory!"];
            } else {
                $message = ["type" => "warning", "text" => "No valid accounts were found in the provided text."];
            }
        }
    }
}

// Fetch categories for select dropdown
$categories = $conn->query("SELECT id, name FROM categorys ORDER BY name ASC");
$selectedCatId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
?>

<style>
    .form-select, .form-control {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }
    .form-select option {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        padding: 8px 12px;
    }
</style>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Add Stock Accounts</h3>
        <p class="text-secondary small mb-0">Upload individual credentials or bulk import accounts for instant delivery</p>
    </div>
    <a href="all_service.php" class="btn btn-outline-light btn-sm px-3 rounded-pill">
        <i class="fa-solid fa-arrow-left me-1"></i> View Stock
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid <?= $message['type'] == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
        <?= htmlspecialchars($message['text']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2" id="stockTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-4" id="bulk-tab" data-bs-toggle="pill" data-bs-target="#bulk" type="button">
                    <i class="fa-solid fa-list-check me-2"></i> Bulk Import (Recommended)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-4" id="single-tab" data-bs-toggle="pill" data-bs-target="#single" type="button">
                    <i class="fa-solid fa-user-plus me-2"></i> Single Account
                </button>
            </li>
        </ul>

        <div class="tab-content" id="stockTabContent">
            <!-- Bulk Import Form -->
            <div class="tab-pane fade show active" id="bulk" role="tabpanel">
                <div class="dash-card">
                    <form method="POST">
                        <input type="hidden" name="mode" value="bulk">

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Target Subscription Category</label>
                            <select name="cat_id" class="form-select" required>
                                <option value="">-- Choose Category --</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $selectedCatId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">
                                Paste Accounts (One per line, in <code>email:password</code> format)
                            </label>
                            <textarea name="bulk_accounts" class="form-control font-monospace" rows="8" placeholder="account1@domain.com:Pass123&#10;account2@domain.com:Pass456&#10;account3@domain.com:Pass789" required></textarea>
                            <small class="text-secondary mt-1 d-block">
                                <i class="fa-solid fa-info-circle me-1"></i> Each line will be parsed and immediately added as an available unsold stock unit.
                            </small>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn-gradient px-4">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Import Accounts Into Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Single Account Form -->
            <div class="tab-pane fade" id="single" role="tabpanel">
                <div class="dash-card">
                    <form method="POST">
                        <input type="hidden" name="mode" value="single">

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Target Subscription Category</label>
                            <select name="cat_id" class="form-select" required>
                                <option value="">-- Choose Category --</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $selectedCatId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Account Username / Email</label>
                            <input type="text" name="username" class="form-control" placeholder="user@gmail.com" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-semibold">Account Password</label>
                            <input type="text" name="password" class="form-control" placeholder="SecretPassword123" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn-gradient px-4">
                                <i class="fa-solid fa-circle-check me-1"></i> Add Single Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
