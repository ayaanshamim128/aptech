
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Archive</title>

    <style>
        .container{
        display:flex;
        flex-wrap: wrap;
    }

    .card{
        width: 280px;
        border: 1px solid black;
        padding: 18px;
        margin: 12px;
        border-radius: 14px;
        box-sizing: border-box;
        border: none;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .imgFluid {
        aspect-ratio: 3 / 3;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2), 0 2px 4px 0 rgba(0, 0, 0, 0.19);
        border-radius: 14px;
        margin-bottom:14px;
    }

    .imgFluid img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;

    }

    .card .btnContainer{
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        gap: 16px;
        padding: 6px 0px;
        margin-top: 10px;
    }

    .card .btn{
        padding: 8px 14px;
        width: 40%;
        border-radius: 10px;
    }

    .card .edit{
        background-color: green;
        color: white;
    }

    .card .delete{
        background-color: red;
        color: white;
    }

    </style>

</head>
<body>
<div class="container">

    <?php
        $productsResult=mysqli_query($conn,"SELECT * FROM products");
        if (mysqli_num_rows($productsResult)>0) {
            while ($productsRow=mysqli_fetch_assoc($productsResult)) {
                echo '<div class="card">';
                echo '<div class="imgFluid"><img src="upload/'.$productsRow['image'].'"></div>';
                echo '<h3>'.$productsRow['name'].'</h3>';
                echo '<p>'.$productsRow['description'].'</p>';
                echo '<strong>'.$productsRow['price'].'$ USD</strong>';

                echo '<div class="btnContainer">';
                echo '<a class="btn edit" href="edit_product.php?id='.$productsRow['id'].'">Edit</a>';
                echo '<a class="btn delete" href="delete_product.php?id='.$productsRow['id'].'">Delete</a>';
                echo '</div>';

                echo '</div>';
            }
        }
    ?>

    </div>
</body>
</html>
