<?php
include "config.php";

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: index.php?success=category_added");
        exit();
    }
}

$categoriesResult = mysqli_query($conn,
    "SELECT c.*, COUNT(p.id) AS product_count
       FROM categories c
       LEFT JOIN products p ON c.id = p.category_id
       GROUP BY c.id
       ORDER BY c.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories &mdash; Shopy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        footer { background: #f1f1f1; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg,#667eea,#764ba2);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-store me-2"></i>Shopy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_product.php"><i class="fas fa-plus-circle me-1"></i>Add Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="add_category.php"><i class="fas fa-tags me-1"></i>Categories</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row g-4">

        <!-- Add Category Form -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header text-white rounded-top py-3"
                     style="background: linear-gradient(135deg,#667eea,#764ba2);">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="e.g. Electronics, Clothing…" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i>Add Category
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-3">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Products
                </a>
            </div>
        </div>

        <!-- Existing Categories -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>All Categories
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Products</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while ($cat = mysqli_fetch_assoc($categoriesResult)): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <i class="fas fa-tag text-primary me-2"></i>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo (int)$cat['product_count']; ?> product<?php echo $cat['product_count'] != 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-tags fa-3x mb-3 d-block"></i>
                        <p>No categories yet. Add one on the left!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted">
    <p class="mb-0">&copy; <?php echo date('Y'); ?> Shopy. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
