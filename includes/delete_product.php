<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

if(isset($_GET["product_id"]) && !empty(trim($_GET["product_id"]))){
    $sql = "DELETE FROM products WHERE product_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        $param_id = trim($_GET["product_id"]);
        
        if(mysqli_stmt_execute($stmt)){
            header("location: index.php?page=products");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    mysqli_close($conn);
} else{
    if(empty(trim($_GET["product_id"]))){
        header("location: index.php?page=products");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Product</title>
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5 mb-3">Delete Product</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                        <div class="alert alert-danger">
                            <input type="hidden" name="product_id" value="<?php echo trim($_GET["product_id"]); ?>"/>
                            <p>Are you sure you want to delete this product?</p>
                            <p>
                                <input type="submit" value="Yes" class="btn btn-danger">
                                <a href="index.php?page=products" class="btn btn-secondary">No</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>