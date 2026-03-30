
<?php
include "config.php";

$id = $_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM products WHERE id=$id");

$row = mysqli_fetch_assoc($result);

if(isset($_POST['update'])){

$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];

$sql = "UPDATE products SET
name='$name',
description='$description',
price='$price'
WHERE id=$id";

if(mysqli_query($conn,$sql)){
header("Location: view_product.php");
}

}

?>

<h2>Edit Product</h2>

<form method="post">

<input type="text" name="name" value="<?php echo $row['name']; ?>">

<textarea name="description"><?php echo $row['description']; ?></textarea>

<input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>">

<button type="submit" name="update">Update</button>

</form>

