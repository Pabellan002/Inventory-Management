<?php
// Add the formatPricePHP function directly in this file
function formatPricePHP($price) {
    return '₱ ' . number_format($price, 2, '.', ',');
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

if(isset($_GET["product_id"]) && !empty(trim($_GET["product_id"]))){
    $sql = "SELECT p.*, u.username, u.role FROM products p 
            LEFT JOIN users u ON p.created_by = u.users_id 
            WHERE p.product_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        $param_id = trim($_GET["product_id"]);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            } else{
                header("location: index.php?page=products");
                exit();
            }
            
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    mysqli_close($conn);
} else{
    header("location: index.php?page=products");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .product-details p {
            margin: 10px 0;
        }
        .product-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 20px auto;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>View Product</h1>
        <div class="product-details">
            <h2><?php echo htmlspecialchars($row["name"]); ?></h2>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($row["description"]); ?></p>
            <p><strong>Price:</strong> <?php echo formatPricePHP($row["price"]); ?></p>
            <p><strong>Expiry Date:</strong> <?php echo $row["expiry_date"] ? htmlspecialchars($row["expiry_date"]) : 'N/A'; ?></p>
            <p><strong>Added By:</strong> <?php echo ucfirst(htmlspecialchars($row["role"])) . " - " . htmlspecialchars($row["username"]); ?></p>
            <?php if (!empty($row["image_path"])): ?>
                <img src="../<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Product Image" class="product-image">
            <?php else: ?>
                <p>No image available</p>
            <?php endif; ?>
        </div>
        <p>
            <a href="index.php?page=edit_product&product_id=<?php echo $row["product_id"]; ?>" class="btn btn-primary">Edit</a>
            <a href="index.php?page=products" class="btn btn-secondary">Back to Products</a>
        </p>
    </div>
</body>
</html>