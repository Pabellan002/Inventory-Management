<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$product_id = $stock_out_amount = "";
$product_id_err = $stock_out_amount_err = "";
$product_name = $current_total_stock = $last_stock_in_date = "";

if(isset($_GET["product_id"]) && !empty(trim($_GET["product_id"]))){
    $product_id = trim($_GET["product_id"]);
    
    // Fetch product details
    $sql = "SELECT name, stocks, stock_in_date FROM products WHERE product_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $product_name = $row["name"];
                $current_total_stock = $row["stocks"];
                $last_stock_in_date = $row["stock_in_date"];
            } else {
                header("location: index.php?page=products");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate stock out amount
    if(empty(trim($_POST["stock_out_amount"]))){
        $stock_out_amount_err = "Please enter the stock out amount.";     
    } elseif(!ctype_digit($_POST["stock_out_amount"])){
        $stock_out_amount_err = "Please enter a valid number for stock out amount.";
    } else{
        $stock_out_amount = intval(trim($_POST["stock_out_amount"]));
        if($stock_out_amount > $current_total_stock){
            $stock_out_amount_err = "Stock out amount cannot be greater than current total stock.";
        }
    }
    
    // Process stock out if no errors
    if(empty($stock_out_amount_err)){
        $sql = "UPDATE products SET stocks = stocks - ? WHERE product_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ii", $stock_out_amount, $product_id);
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=products");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out</title>
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
        p {
            margin-bottom: 15px;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
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
        <h2>Stock Out - <?php echo htmlspecialchars($product_name); ?></h2>
        <p><strong>Current Total Stock:</strong> <?php echo $current_total_stock; ?></p>
        <p><strong>Last Stock In Date:</strong> <?php echo $last_stock_in_date; ?></p>
        
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
            <div>
                <label>Stock Out Amount</label>
                <input type="number" name="stock_out_amount" value="<?php echo htmlspecialchars($stock_out_amount); ?>">
                <span class="error"><?php echo $stock_out_amount_err; ?></span>
            </div>
            <div>
                <input type="submit" value="Stock Out">
            </div>
        </form>
        <a href="index.php?page=products">Cancel</a>
    </div>    
</body>
</html>