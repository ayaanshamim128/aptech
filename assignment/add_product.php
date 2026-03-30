
 

 <?php
include "config.php";

if (isset($_POST['submit'])) {
    $_name=$_POST['name'];
    $_description=$_POST['description'];
    $_price=$_POST['price'];

    $imageName=$_FILES['image']['name'];
    $tempName=$_FILES['image']['tmp_name'];

    $folder="upload/".$imageName;

    move_uploaded_file($tempName,$folder);

    $sql="INSERT into products (name,description,price,image) values ('$_name',' $_description','$_price','$imageName')";

    if (mysqli_query($conn,$sql)) {
        
        echo "Product Added Successfully";

    }else {

        echo "Error";

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>

</head>

<body>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-header text-center">
                    <h4>Add Product</h4>
                </div>

                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" placeholder="Description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <input type="submit" name="submit" value="Add Product" class="btn btn-primary">
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>
</div>
</body>
</html>

