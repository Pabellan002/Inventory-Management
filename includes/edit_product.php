<?php
function formatPricePHP($price) {
    return '₱ ' . number_format($price, 2, '.', ',');
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$name = $description = $price = $image_path = $expiry_date = "";
$name_err = $description_err = $price_err = $image_err = $expiry_date_err = "";
$product_id = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = $_POST["product_id"];
    
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a product name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))){
        $description_err = "Please enter a product description.";
    } else{
        $description = trim($_POST["description"]);
    }
    
    // Validate price
    if(empty(trim($_POST["price"]))){
        $price_err = "Please enter the price.";
    } elseif(!is_numeric($_POST["price"])){
        $price_err = "Please enter a valid number for price.";
    } else{
        $price = trim($_POST["price"]);
    }
    
    // Validate expiry date
    if(empty(trim($_POST["expiry_date"]))){
        $expiry_date_err = "Please enter the expiry date.";
    } else{
        $expiry_date = trim($_POST["expiry_date"]);
    }
    
    // Handle file upload
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        $target_dir = __DIR__ . "/../uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $image_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = "uploads/" . basename($_FILES["image"]["name"]);
                } else {
                    $image_err = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $image_err = "File is not an image.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($image_err) && empty($expiry_date_err)){
        // Prepare an update statement
        $sql = "UPDATE products SET name=?, description=?, price=?, expiry_date=?";
        $params = array($name, $description, $price, $expiry_date);
        $types = "ssds";
        
        if(!empty($image_path)){
            $sql .= ", image_path=?";
            $params[] = $image_path;
            $types .= "s";
        }
        
        $sql .= " WHERE product_id=?";
        $params[] = $product_id;
        $types .= "i";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=view_product&product_id=".$product_id);
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
} else {
    // Check existence of id parameter before processing further
    if(isset($_GET["product_id"]) && !empty(trim($_GET["product_id"]))){
        // Get URL parameter
        $product_id =  trim($_GET["product_id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM products WHERE product_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $product_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $name = $row["name"];
                    $description = $row["description"];
                    $price = $row["price"];
                    $image_path = $row["image_path"];
                    $expiry_date = $row["expiry_date"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .dashboard-container {
            display: flex;
            width: 98%;
            max-width: 2300px;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 20px;
            height: calc(100vh - 40px);
            border-right: 1px solid #e0e0e0;
        }
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 30px;
            height: 30px;
            background-color: #0047AB;
            margin-right: 10px;
        }
        .logo-text {
            font-size: 20px;
            font-weight: bold;
            color: #0047AB;
        }
        .menu-group {
            background-color: #f8f8f8;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            color: #333;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .menu-item:hover, .menu-item.active {
            background-color: #0047AB;
            color: #ffffff;
            transform: translateX(5px);
        }
        .menu-item i {
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .menu-item:hover i, .menu-item.active i {
            transform: scale(1.2);
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .top-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .user-profile {
            display: flex;
            align-items: center;
        }
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
        }
        .edit-product-form {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        input[type="file"] {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #0047AB;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #003380;
        }
        .error {
            color: #f44336;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0047AB;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #003380;
        }
        .current-image {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon"></div>
                <div class="logo-text">Green & Sweets Inventory Management System</div>
            </div>
            <div class="menu-group">
                <a href="index.php?page=dashboard" class="menu-item"><i>📊</i> Dashboard</a>
                <a href="index.php?page=products" class="menu-item active"><i>📑</i> Products</a>
                <a href="index.php?page=stocks" class="menu-item"><i>📄</i> Stocks</a>
            </div>
            <div class="menu-group">
                <a href="index.php?page=suppliers" class="menu-item"><i>📥</i> Suppliers</a>
                <a href="index.php?page=users" class="menu-item"><i>🎫</i> Manage Users</a>
                <a href="index.php?page=reports" class="menu-item"><i>➕</i> Reports</a>
            </div>
            <div class="menu-group">
                <a href="index.php?page=logout" class="menu-item"><i>🔧</i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div class="top-bar">
                <div class="user-profile">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                    <img src="./../uploads/green.jpg" alt="User Profile">
                </div>
            </div>
            <div class="edit-product-form">
                <h2>Edit Product</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <div>
                        <label for="name">Product Name</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">
                        <span class="error"><?php echo $name_err; ?></span>
                    </div> 
                    <div>
                        <label for="description">Description</label>
                        <textarea name="description" id="description"><?php echo htmlspecialchars($description); ?></textarea>
                        <span class="error"><?php echo $description_err; ?></span>
                    </div>
                    <div>
                        <label for="price">Price (PHP)</label>
                        <input type="number" step="0.01" name="price" id="price" value="<?php echo $price; ?>">
                        <span class="error"><?php echo $price_err; ?></span>
                    </div>
                    <div>
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" value="<?php echo $expiry_date; ?>">
                        <span class="error"><?php echo $expiry_date_err; ?></span>
                    </div>
                    <div>
                        <label for="image">Product Image</label>
                        <input type="file" name="image" id="image">
                        <span class="error"><?php echo $image_err; ?></span>
                    </div>
                    <div>
                        <input type="submit" value="Update Product">
                    </div>
                </form>
                <a href="index.php?page=view_product&product_id=<?php echo $product_id; ?>" class="back-link">← Back to Product Details</a>
            </div>
        </div>
    </div>
</body>
</html>