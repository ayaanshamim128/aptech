<?php
include "config.php";

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error   = isset($_GET['error'])   ? $_GET['error']   : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopy &mdash; Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; }
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .product-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .product-img-placeholder {
            height: 200px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: #6c63ff;
        }
        .btn-edit   { background-color: #28a745; color: #fff; border: none; }
        .btn-edit:hover   { background-color: #218838; color: #fff; }
        .btn-delete { background-color: #dc3545; color: #fff; border: none; }
        .btn-delete:hover { background-color: #c82333; color: #fff; }
        .empty-state { text-align: center; padding: 80px 0; color: #6c757d; }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; }
        footer { background: #f1f1f1; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg,#667eea,#764ba2);">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-store me-2"></i>Shopy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_product.php"><i class="fas fa-plus-circle me-1"></i>Add Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add_category.php"><i class="fas fa-tags me-1"></i>Categories</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <?php if ($success === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Product added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($success === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Product updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($success === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-trash me-2"></i>Product deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($success === 'category_added'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Category added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error === 'delete_failed'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>Failed to delete product. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-boxes me-2 text-primary"></i>All Products</h2>
        <div>
            <a href="add_product.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i>Add Product
            </a>
            <a href="add_category.php" class="btn btn-outline-secondary">
                <i class="fas fa-tags me-1"></i>Categories
            </a>
        </div>
    </div>

    <?php
    $productsResult = mysqli_query($conn,
        "SELECT p.*, c.name AS category_name
           FROM products p
           LEFT JOIN categories c ON p.category_id = c.id
           ORDER BY p.id DESC");

    if ($productsResult && mysqli_num_rows($productsResult) > 0):
    ?>
    <div class="row g-4">
        <?php while ($row = mysqli_fetch_assoc($productsResult)): ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card product-card h-100">
                <?php if (!empty($row['image'])): ?>
                    <img src="upload/<?php echo htmlspecialchars($row['image']); ?>"
                         class="card-img-top"
                         alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php else: ?>
                    <div class="product-img-placeholder">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <?php if (!empty($row['category_name'])): ?>
                        <span class="badge bg-primary mb-2 align-self-start">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($row['category_name']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary mb-2 align-self-start">
                            <i class="fas fa-tag me-1"></i>Uncategorized
                        </span>
                    <?php endif; ?>
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="card-text text-muted small flex-grow-1">
                        <?php echo htmlspecialchars($row['description']); ?>
                    </p>
                    <div class="price-tag mb-3">$<?php echo number_format((float)$row['price'], 2); ?></div>
                    <div class="d-flex gap-2">
                        <a href="edit_product.php?id=<?php echo (int)$row['id']; ?>"
                           class="btn btn-edit btn-sm flex-fill">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="delete_product.php?id=<?php echo (int)$row['id']; ?>"
                           class="btn btn-delete btn-sm flex-fill"
                           onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash me-1"></i>Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-box-open d-block"></i>
        <h4>No Products Found</h4>
        <p>Start by adding your first product.</p>
        <a href="add_product.php" class="btn btn-primary btn-lg mt-2">
            <i class="fas fa-plus me-2"></i>Add First Product
        </a>
    </div>
    <?php endif; ?>

</div>

<footer class="mt-5 py-4 text-center text-muted">
    <p class="mb-0">&copy; <?php echo date('Y'); ?> Shopy. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>