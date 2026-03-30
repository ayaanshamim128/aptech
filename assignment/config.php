<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "shopy";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure categories table exists
if (!mysqli_query($conn, "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)")) {
    die("Failed to create categories table: " . mysqli_error($conn));
}

// Ensure products table exists
if (!mysqli_query($conn, "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255),
    category_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)")) {
    die("Failed to create products table: " . mysqli_error($conn));
}

// Add category_id column to products if it doesn't exist (migration)
$_col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'category_id'");
if ($_col && mysqli_num_rows($_col) === 0) {
    if (!mysqli_query($conn, "ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL")) {
        die("Failed to migrate products table (category_id): " . mysqli_error($conn));
    }
}

// Add created_at column to products if it doesn't exist (migration)
$_col2 = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'created_at'");
if ($_col2 && mysqli_num_rows($_col2) === 0) {
    if (!mysqli_query($conn, "ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        die("Failed to migrate products table (created_at): " . mysqli_error($conn));
    }
}

// Ensure the upload directory exists
if (!is_dir(__DIR__ . "/upload")) {
    mkdir(__DIR__ . "/upload", 0755, true);
}

/**
 * Save an uploaded image file.
 * Returns the stored filename on success, or null if no file was uploaded.
 * Throws a RuntimeException on invalid file type or move failure.
 */
function save_uploaded_image(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions, true)) {
        throw new RuntimeException("Invalid image type. Allowed: " . implode(', ', $allowedExtensions));
    }

    $uniqueName = uniqid('img_', true) . '.' . $ext;
    $targetPath = __DIR__ . "/upload/" . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException("Failed to save uploaded file.");
    }

    return $uniqueName;
}
?>