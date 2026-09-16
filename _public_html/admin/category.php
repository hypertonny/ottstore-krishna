<?php
$pageTitle = "Add Category";
require_once __DIR__ . "/header.php";

$message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $info = trim($_POST['info'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $logo_url = trim($_POST['logo_url'] ?? '');
    $total_stock = 0;

    if (!empty($name) && $amount > 0 && !empty($logo_url)) {
        $stmt = $conn->prepare("INSERT INTO categorys (name, info, amount, total_stock, logo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $name, $info, $amount, $total_stock, $logo_url);
        
        if ($stmt->execute()) {
            $message = ["type" => "success", "text" => "Category '$name' created successfully!"];
        } else {
            $message = ["type" => "danger", "text" => "Failed to save category. Please check your data."];
        }
    } else {
        $message = ["type" => "warning", "text" => "Please complete all fields with valid information."];
    }
}
?>

<div class="top-header">
    <div>
        <h3 class="fw-bold text-white mb-1">Add Subscription Category</h3>
        <p class="text-secondary small mb-0">Create a new streaming service category for your catalog</p>
    </div>
    <a href="all_category.php" class="btn btn-outline-light btn-sm px-3 rounded-pill">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Categories
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
        <div class="dash-card">
            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label text-secondary small fw-semibold">Service Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Netflix Premium 4K UHD" required autofocus>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label text-secondary small fw-semibold">Price per Unit (INR)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary">&#8377;</span>
                            <input type="number" step="1" name="amount" class="form-control" placeholder="199" required>
                        </div>
                    </div>
                </div>

                <!-- Logo Picker / Input -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Logo URL / Local Asset Path</label>
                    <input type="text" id="logoUrlInput" name="logo_url" class="form-control" placeholder="assets/icons/netflix.svg" required>
                    
                    <!-- Quick Presets -->
                    <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                        <small class="text-secondary me-1">Quick Presets:</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/netflix.svg')">Netflix</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/prime.svg')">Prime</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/hotstar.svg')">Hotstar</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/youtube.svg')">YouTube</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/spotify.svg')">Spotify</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/crunchyroll.svg')">Crunchyroll</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="setLogo('assets/icons/gmail.svg')">Gmail</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-semibold">Description & Features (Separate lines with &lt;br&gt; or new lines)</label>
                    <textarea name="info" class="form-control" rows="4" placeholder="✓ 4K Ultra HD Streaming&#10;✓ 1 Private Screen with PIN&#10;✓ Works on TV & Mobile&#10;✓ 30 Days Warranty" required></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="all_category.php" class="text-secondary small text-decoration-none">Cancel</a>
                    <button type="submit" class="btn-gradient px-4">
                        <i class="fa-solid fa-circle-check me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setLogo(url) {
    document.getElementById('logoUrlInput').value = url;
}
</script>

<?php require_once __DIR__ . "/footer.php"; ?>
