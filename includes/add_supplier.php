<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$supplier_name = $email = $contact_number = $address = $product_name = $product_image = $status = "";
$supplier_name_err = $email_err = $contact_number_err = $address_err = $product_name_err = $product_image_err = $status_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate supplier name
    $supplier_name = trim($_POST["supplier_name"]);
    if(empty($supplier_name)){
        $supplier_name_err = "Please enter a supplier name.";
    }

    // Validate email
    $email = trim($_POST["email"]);
    if(empty($email)){
        $email_err = "Please enter an email.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    }

    // Validate contact number
    $contact_number = trim($_POST["contact_number"]);
    if(empty($contact_number)){
        $contact_number_err = "Please enter a contact number.";
    }

    // Validate address
    $address = trim($_POST["address"]);
    if(empty($address)){
        $address_err = "Please enter an address.";
    }

    // Validate product name
    $product_name = trim($_POST["product_name"]);
    if(empty($product_name)){
        $product_name_err = "Please enter a product name.";
    }

    // Handle file upload
    if(isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0){
        $upload_dir = __DIR__ . '/../uploads/';
        $target_file = $upload_dir . basename($_FILES["product_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if($check !== false) {
            // File is an image
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $product_image = '/uploads/' . basename($_FILES["product_image"]["name"]);  // Store the relative path
            } else {
                $product_image_err = "Sorry, there was an error uploading your file. Error: " . error_get_last()['message'];
            }
        } else {
            $product_image_err = "File is not an image.";
        }
    }

    $status = isset($_POST["status"]) ? "active" : "inactive";

    // If no errors, proceed with insertion
    if(empty($supplier_name_err) && empty($email_err) && empty($contact_number_err) && empty($address_err) && empty($product_name_err) && empty($product_image_err)){
        $sql = "INSERT INTO suppliers (supplier_name, email, contact_number, address, product_name, product_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssss", $supplier_name, $email, $contact_number, $address, $product_name, $product_image, $status);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=suppliers");
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
    <title>Add Supplier - Inventory Management</title>
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
        .add-supplier-form {
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
        input[type="email"],
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
        .status-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .status-toggle label {
            margin-right: 10px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .status-text {
            margin-left: 10px;
            font-weight: bold;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-active {
            background-color: #4CAF50;
        }
        .status-inactive {
            background-color: #F44336;
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
                <a href="index.php?page=products" class="menu-item"><i>📑</i> Products</a>
                <a href="index.php?page=stocks" class="menu-item"><i>📄</i> Stocks</a>
            </div>
            <div class="menu-group">
                <a href="index.php?page=suppliers" class="menu-item active"><i>📥</i> Suppliers</a>
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
            <div class="add-supplier-form">
                <h2>Add New Supplier</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data">
                    <div>
                        <label for="supplier_name">Supplier Name</label>
                        <input type="text" name="supplier_name" id="supplier_name" value="<?php echo $supplier_name; ?>">
                        <span class="error"><?php echo $supplier_name_err; ?></span>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo $email; ?>">
                        <span class="error"><?php echo $email_err; ?></span>
                    </div>
                    <div>
                        <label for="contact_number">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" value="<?php echo $contact_number; ?>">
                        <span class="error"><?php echo $contact_number_err; ?></span>
                    </div>
                    <div>
                        <label for="address">Address</label>
                        <textarea name="address" id="address"><?php echo $address; ?></textarea>
                        <span class="error"><?php echo $address_err; ?></span>
                    </div>
                    <div>
                        <label for="product_name">Product Name</label>
                        <input type="text" name="product_name" id="product_name" value="<?php echo $product_name; ?>">
                        <span class="error"><?php echo $product_name_err; ?></span>
                    </div>
                    <div>
                        <label for="product_image">Product Image</label>
                        <input type="file" name="product_image" id="product_image">
                        <span class="error"><?php echo $product_image_err; ?></span>
                    </div>
                    <div class="status-toggle">
                        <label for="status">Status:</label>
                        <label class="switch">
                            <input type="checkbox" id="status" name="status" checked>
                            <span class="slider"></span>
                        </label>
                        <span class="status-indicator status-active" id="statusDot"></span>
                        <span class="status-text" id="statusText">Active</span>
                    </div>
                    <div>
                        <input type="submit" value="Add Supplier">
                    </div>
                </form>
                <a href="index.php?page=suppliers" class="back-link">← Back to Suppliers</a>
            </div>
        </div>
    </div>
    <script>
        const statusToggle = document.getElementById('status');
        const statusText = document.getElementById('statusText');
        const statusDot = document.getElementById('statusDot');

        statusToggle.addEventListener('change', function() {
            if (this.checked) {
                statusText.textContent = 'Active';
                statusDot.className = 'status-indicator status-active';
            } else {
                statusText.textContent = 'Inactive';
                statusDot.className = 'status-indicator status-inactive';
            }
        });
    </script>
</body>
</html>