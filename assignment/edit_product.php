<?php
include "config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: index.php");
    exit();
}

$error = '';

if (isset($_POST['update'])) {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = $_POST['price'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Keep existing image unless a new one is uploaded
    $imageName = $row['image'];
    try {
        if (isset($_FILES['image'])) {
            $uploaded = save_uploaded_image($_FILES['image']);
            if ($uploaded !== null) {
                $imageName = $uploaded;
            }
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        $stmt = mysqli_prepare($conn,
            "UPDATE products SET name=?, description=?, price=?, image=?, category_id=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssdsii", $name, $description, $price, $imageName, $category_id, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php?success=updated");
            exit();
        }

        $error = "Failed to update product. Please try again.";
        mysqli_stmt_close($stmt);
    }
}

$categoriesResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product &mdash; Shopy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .form-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .current-image { width: 100%; max-height: 200px; object-fit: cover; border-radius: 10px; }
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
                    <a class="nav-link" href="add_category.php"><i class="fas fa-tags me-1"></i>Categories</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="card form-card">
                <div class="card-header text-white text-center py-3"
                     style="background: linear-gradient(135deg,#28a745,#20c997); border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Product</h4>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($row['image'])): ?>
                    <div class="mb-4 text-center">
                        <label class="form-label fw-semibold d-block">Current Image</label>
                        <img src="upload/<?php echo htmlspecialchars($row['image']); ?>"
                             alt="Current product image" class="current-image shadow-sm">
                    </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                   value="<?php echo htmlspecialchars($row['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php while ($cat = mysqli_fetch_assoc($categoriesResult)): ?>
                                <option value="<?php echo (int)$cat['id']; ?>"
                                    <?php echo ((int)$row['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Price (USD) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="price"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($row['price']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <?php echo !empty($row['image']) ? 'Replace Image (optional)' : 'Product Image'; ?>
                            </label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if (!empty($row['image'])): ?>
                            <div class="form-text">Leave empty to keep the current image. Accepted: JPG, JPEG, PNG, GIF, WEBP</div>
                            <?php else: ?>
                            <div class="form-text">Accepted formats: JPG, JPEG, PNG, GIF, WEBP</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update" class="btn btn-success flex-fill">
                                <i class="fas fa-save me-1"></i>Update Product
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary flex-fill">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>

                    </form>
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
