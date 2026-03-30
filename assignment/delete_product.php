<?php



include "config.php";

$id = $_GET['id'];
$sql="DELETE FROM products WHERE id=$id";



if (mysqli_query($conn,$sql)) {     
    header('Location: view_product.php');
}else {
    echo "Delete Failed";
}



?>