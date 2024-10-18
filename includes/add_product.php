<?php
// Remove the require_once line for utilities.php
// require_once __DIR__ . '/utilities.php';

// Add the formatPricePHP function directly in this file
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

if($_SERVER["REQUEST_METHOD"] == "POST"){
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
        // You can add additional validation here if needed
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
    } else {
        $image_err = "Please select an image to upload.";
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($image_err)){
        // Get the current user's ID from the session
        $user_id = $_SESSION['users_id'];
        
        $sql = "INSERT INTO products (name, description, price, image_path, expiry_date, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssdssi", $param_name, $param_description, $param_price, $param_image_path, $param_expiry_date, $param_created_by);
            
            // Set parameters
            $param_name = $name;
            $param_description = $description;
            $param_price = $price;
            $param_image_path = $image_path;
            $param_expiry_date = $expiry_date;
            $param_created_by = $_SESSION["user_id"]; // Assuming you store user_id in the session
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=products");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Inventory Management</title>
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
        .add-product-form {
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
            <div class="add-product-form">
                <h2>Add New Product</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                    <div>
                        <label for="name">Product Name</label>
                        <input type="text" name="name" id="name" value="<?php echo $name; ?>">
                        <span class="error"><?php echo $name_err; ?></span>
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <textarea name="description" id="description"><?php echo $description; ?></textarea>
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
                        <input type="submit" value="Add Product">
                    </div>
                </form>
                <a href="index.php?page=products" class="back-link">← Back to Products</a>
            </div>
        </div>
    </div>
</body>
</html>