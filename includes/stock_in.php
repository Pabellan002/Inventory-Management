<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$product_id = $additional_stock = "";
$product_id_err = $additional_stock_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate product selection
    if(empty(trim($_POST["product_id"]))){
        $product_id_err = "Please select a product.";
    } else{
        $product_id = trim($_POST["product_id"]);
    }
    
    // Validate additional stock
    if(empty(trim($_POST["additional_stock"]))){
        $additional_stock_err = "Please enter the additional stock amount.";     
    } elseif(!ctype_digit($_POST["additional_stock"])){
        $additional_stock_err = "Please enter a valid number for additional stock.";
    } else{
        $additional_stock = trim($_POST["additional_stock"]);
    }
    
    // Check input errors before updating in database
    if(empty($product_id_err) && empty($additional_stock_err)){
        // Update stock in products table
        $sql = "UPDATE products SET stocks = stocks + ?, stock_in_date = CURRENT_DATE() WHERE product_id = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ii", $additional_stock, $product_id);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=products");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .wrapper {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Stock In</h2>
        <p>Please fill this form to add stock to a product.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
            <div>
                <label>Product</label>
                <select name="product_id">
                    <option value="">Select a product</option>
                    <?php
                    $sql = "SELECT product_id, name FROM products";
                    if($result = mysqli_query($conn, $sql)){
                        while($row = mysqli_fetch_array($result)){
                            echo '<option value="' . $row['product_id'] . '"' . ($product_id == $row['product_id'] ? ' selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
                        }
                        mysqli_free_result($result);
                    }
                    ?>
                </select>
                <span class="error"><?php echo $product_id_err; ?></span>
            </div>    
            <div>
                <label>Additional Stock</label>
                <input type="number" name="additional_stock" value="<?php echo $additional_stock; ?>">
                <span class="error"><?php echo $additional_stock_err; ?></span>
            </div>
            <div>
                <input type="submit" value="Add Stock">
            </div>
        </form>
        <a href="index.php?page=products">Cancel</a>
    </div>    
</body>
</html>