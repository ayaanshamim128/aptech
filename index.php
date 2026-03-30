<?php
require_once 'db.php';

$pdo = getDB();

// ─── Helpers ─────────────────────────────────────────────────────────────────

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $params = ''): void {
    header('Location: index.php' . ($params ? '?' . $params : ''));
    exit;
}

// ─── Actions ─────────────────────────────────────────────────────────────────

$message = '';
$messageType = '';

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '') {
        $message = 'Category name cannot be empty.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        redirect('success=Category+added+successfully');
    }
}

// Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name        = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['product_description'] ?? '');
    $price       = $_POST['product_price'] ?? '';
    $categoryId  = $_POST['product_category'] ?? null;

    if ($name === '' || $price === '' || !is_numeric($price) || (float)$price < 0) {
        $message = 'Please provide a valid product name and price.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $description, (float)$price, $categoryId ?: null]);
        redirect('success=Product+added+successfully');
    }
}

// Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $id          = (int)($_POST['product_id'] ?? 0);
    $name        = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['product_description'] ?? '');
    $price       = $_POST['product_price'] ?? '';
    $categoryId  = $_POST['product_category'] ?? null;

    if ($id <= 0 || $name === '' || $price === '' || !is_numeric($price) || (float)$price < 0) {
        $message = 'Please provide valid product details.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, category_id=? WHERE id=?');
        $stmt->execute([$name, $description, (float)$price, $categoryId ?: null, $id]);
        redirect('success=Product+updated+successfully');
    }
}

// Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id=?');
        $stmt->execute([$id]);
        redirect('success=Product+deleted+successfully');
    }
}

// ─── Load data ────────────────────────────────────────────────────────────────

if (!empty($_GET['success'])) {
    $message = $_GET['success'];
    $messageType = 'success';
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$search = trim($_GET['search'] ?? '');
$filterCat = (int)($_GET['filter_cat'] ?? 0);

$sql = '
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE 1=1
';
$params = [];

if ($search !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($filterCat > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $filterCat;
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Product being edited
$editProduct = null;
if (!empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aptech Store – Product Manager</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    :root {
        --brand: #4f46e5;
        --brand-dark: #3730a3;
    }
    body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }

    /* Navbar */
    .navbar-brand { font-weight: 700; letter-spacing: .5px; }
    .navbar { background: var(--brand) !important; }

    /* Hero */
    .hero {
        background: linear-gradient(135deg, var(--brand) 0%, #7c3aed 100%);
        color: #fff;
        padding: 2.5rem 0 3.5rem;
    }
    .hero h1 { font-size: 2rem; font-weight: 700; }

    /* Cards */
    .card { border: none; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .card-header { border-radius: 1rem 1rem 0 0 !important; font-weight: 600; }

    /* Table */
    .table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .6px; color: #64748b; }
    .table td { vertical-align: middle; }
    .badge-cat { background: #ede9fe; color: #4f46e5; border-radius: 20px; padding: 3px 10px; font-size: .75rem; }

    /* Price chip */
    .price-chip { font-weight: 700; color: #16a34a; }

    /* Action buttons */
    .btn-edit  { background: #dbeafe; color: #1d4ed8; border: none; }
    .btn-edit:hover  { background: #bfdbfe; color: #1d4ed8; }
    .btn-del   { background: #fee2e2; color: #dc2626; border: none; }
    .btn-del:hover   { background: #fecaca; color: #dc2626; }

    /* Section headings */
    .section-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        margin-bottom: .5rem;
    }

    /* Sidebar sticky */
    @media (min-width: 992px) {
        .sidebar-sticky { position: sticky; top: 1.5rem; }
    }

    /* Empty state */
    .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 3rem; display: block; margin-bottom: 1rem; }

    /* Search bar */
    .search-input { border-radius: 50px !important; padding-left: 2.5rem !important; }
    .search-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
</style>
</head>
<body>

<!-- ── Navbar ──────────────────────────────────────────────────────────────── -->
<nav class="navbar navbar-dark py-2">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-shop me-2"></i>Aptech Store
        </a>
        <span class="text-white-50 small">Product Manager</span>
    </div>
</nav>

<!-- ── Hero ───────────────────────────────────────────────────────────────── -->
<div class="hero mb-0">
    <div class="container">
        <h1><i class="bi bi-box-seam me-2"></i>Product Manager</h1>
        <p class="mb-0 opacity-75">Manage your categories and products in one place.</p>
    </div>
</div>

<!-- ── Main layout ────────────────────────────────────────────────────────── -->
<div class="container py-4" style="margin-top:-1.5rem;">

    <?php if ($message): ?>
    <div class="alert alert-<?= h($messageType) ?> alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= h($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <div class="col-lg-4">
            <div class="sidebar-sticky">

                <!-- Add / Edit Product -->
                <div class="card mb-4">
                    <div class="card-header bg-indigo text-white py-3"
                         style="background:var(--brand);color:#fff;">
                        <i class="bi bi-<?= $editProduct ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
                        <?= $editProduct ? 'Edit Product' : 'Add Product' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="action"
                                   value="<?= $editProduct ? 'update_product' : 'add_product' ?>">
                            <?php if ($editProduct): ?>
                            <input type="hidden" name="product_id" value="<?= (int)$editProduct['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="product_name" class="form-control"
                                       placeholder="e.g. Wireless Mouse"
                                       value="<?= $editProduct ? h($editProduct['name']) : '' ?>"
                                       required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="product_description" class="form-control" rows="2"
                                          placeholder="Optional product details"><?= $editProduct ? h($editProduct['description']) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Price (PKR) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" name="product_price" class="form-control"
                                           placeholder="0.00" step="0.01" min="0"
                                           value="<?= $editProduct ? h($editProduct['price']) : '' ?>"
                                           required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="product_category" class="form-select">
                                    <option value="">— No category —</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"
                                        <?= ($editProduct && (int)$editProduct['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                        <?= h($cat['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn text-white fw-semibold"
                                        style="background:var(--brand);">
                                    <i class="bi bi-<?= $editProduct ? 'check-lg' : 'plus-lg' ?> me-1"></i>
                                    <?= $editProduct ? 'Update Product' : 'Add Product' ?>
                                </button>
                                <?php if ($editProduct): ?>
                                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Category -->
                <div class="card">
                    <div class="card-header py-3" style="background:#0ea5e9;color:#fff;">
                        <i class="bi bi-tag me-2"></i>Add Category
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="action" value="add_category">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="category_name" class="form-control"
                                       placeholder="e.g. Electronics" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn text-white fw-semibold"
                                        style="background:#0ea5e9;">
                                    <i class="bi bi-plus-lg me-1"></i>Add Category
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($categories)): ?>
                        <hr class="my-3">
                        <p class="section-label mb-2">Existing Categories</p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($categories as $cat): ?>
                            <span class="badge-cat"><i class="bi bi-tag-fill me-1"></i><?= h($cat['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.sidebar-sticky -->
        </div><!-- /.col sidebar -->

        <!-- ── Products Table ───────────────────────────────────────────────── -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between"
                     style="background:#fff;border-bottom:1px solid #e2e8f0;">
                    <span class="fw-bold fs-5"><i class="bi bi-grid me-2 text-indigo"
                          style="color:var(--brand)"></i>Products
                        <span class="badge rounded-pill ms-1"
                              style="background:#ede9fe;color:var(--brand);font-size:.75rem;">
                            <?= count($products) ?>
                        </span>
                    </span>
                </div>

                <!-- Filter/Search bar -->
                <div class="card-body border-bottom pb-3" style="background:#f8fafc;">
                    <form method="GET" action="index.php" class="row g-2 align-items-center">
                        <div class="col-sm-6">
                            <div class="position-relative">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" name="search" class="form-control search-input"
                                       placeholder="Search products…"
                                       value="<?= h($search) ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <select name="filter_cat" class="form-select" style="border-radius:50px;">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"
                                    <?= $filterCat === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= h($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn w-100 text-white"
                                    style="background:var(--brand);border-radius:50px;">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-semibold">No products found.</p>
                        <p class="small">Add your first product using the form on the left.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $i => $p): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= h($p['name']) ?></div>
                                        <?php if ($p['description']): ?>
                                        <div class="text-muted small"><?= h(mb_strimwidth($p['description'], 0, 60, '…')) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['category_name']): ?>
                                        <span class="badge-cat">
                                            <i class="bi bi-tag-fill me-1"></i><?= h($p['category_name']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="price-chip">PKR <?= number_format((float)$p['price'], 2) ?></span></td>
                                    <td class="text-end pe-4">
                                        <a href="index.php?edit=<?= (int)$p['id'] ?>"
                                           class="btn btn-sm btn-edit me-1">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form method="POST" action="index.php" class="d-inline"
                                              onsubmit="return confirm(<?= h(json_encode('Delete: ' . $p['name'] . '?')) ?>)">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-del">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /.col products -->

    </div><!-- /.row -->
</div><!-- /.container -->

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="text-center py-4 mt-4" style="color:#94a3b8;font-size:.85rem;">
    &copy; <?= date('Y') ?> Aptech Store &mdash; Product Manager
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
