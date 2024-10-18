<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$supplier_name = $email = $contact_number = $address = $product_name = $current_image = $status = "";
$supplier_name_err = $email_err = $contact_number_err = $address_err = $product_name_err = $image_err = $status_err = "";

if(isset($_GET["supplier_id"]) && !empty(trim($_GET["supplier_id"]))){
    $supplier_id = trim($_GET["supplier_id"]);
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        // Validate and sanitize the posted data
        $supplier_name = trim($_POST["supplier_name"]);
        if(empty($supplier_name)){
            $supplier_name_err = "Please enter a supplier name.";
        }
        
        $email = trim($_POST["email"]);
        if(empty($email)){
            $email_err = "Please enter an email.";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $email_err = "Please enter a valid email address.";
        }
        
        $contact_number = trim($_POST["contact_number"]);
        if(empty($contact_number)){
            $contact_number_err = "Please enter a contact number.";
        }
        
        $address = trim($_POST["address"]);
        if(empty($address)){
            $address_err = "Please enter an address.";
        }
        
        $product_name = trim($_POST["product_name"]);
        if(empty($product_name)){
            $product_name_err = "Please enter a product name.";
        }
        
        // Handle file upload
        if(isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0){
            $allowed = ["jpg", "jpeg", "png", "gif"];
            $filename = $_FILES["product_image"]["name"];
            $filetype = $_FILES["product_image"]["type"];
            $filesize = $_FILES["product_image"]["size"];

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)){
                $image_err = "Please select a valid image file (JPG, JPEG, PNG, GIF).";
            }

            $maxsize = 5 * 1024 * 1024; // 5MB
            if($filesize > $maxsize){
                $image_err = "File size is larger than the allowed limit (5MB).";
            }

            if(empty($image_err)){
                $new_filename = uniqid() . "." . $ext;
                $upload_path = __DIR__ . "/../uploads/" . $new_filename;
                if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $upload_path)){
                    $product_image = "/uploads/" . $new_filename;
                } else {
                    $image_err = "Failed to upload image. Please try again.";
                }
            }
        }
        
        $status = isset($_POST["status"]) ? "active" : "inactive";
        
        // If there are no errors, proceed with the update
        if(empty($supplier_name_err) && empty($email_err) && empty($contact_number_err) && empty($address_err) && empty($product_name_err) && empty($image_err)){
            if(isset($product_image)){
                $sql = "UPDATE suppliers SET supplier_name=?, email=?, contact_number=?, address=?, product_name=?, product_image=?, status=? WHERE supplier_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssssi", $supplier_name, $email, $contact_number, $address, $product_name, $product_image, $status, $supplier_id);
            } else {
                $sql = "UPDATE suppliers SET supplier_name=?, email=?, contact_number=?, address=?, product_name=?, status=? WHERE supplier_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssi", $supplier_name, $email, $contact_number, $address, $product_name, $status, $supplier_id);
            }
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php?page=suppliers");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
        
    } else {
        // Fetch the supplier data
        $sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $supplier_id);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    $supplier_name = $row["supplier_name"];
                    $email = $row["email"];
                    $contact_number = $row["contact_number"];
                    $address = $row["address"];
                    $product_name = $row["product_name"];
                    $current_image = $row["product_image"];
                    $status = $row["status"];
                } else {
                    header("location: index.php?page=suppliers");
                    exit();
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
} else {
    header("location: index.php?page=suppliers");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier - Inventory Management</title>
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
        .edit-supplier-form {
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
            <div class="edit-supplier-form">
                <h2>Edit Supplier</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=edit_supplier&supplier_id=' . $supplier_id; ?>" method="post" enctype="multipart/form-data">
                    <div>
                        <label for="supplier_name">Supplier Name</label>
                        <input type="text" name="supplier_name" id="supplier_name" value="<?php echo htmlspecialchars($supplier_name); ?>">
                        <span class="error"><?php echo $supplier_name_err; ?></span>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="error"><?php echo $email_err; ?></span>
                    </div>
                    <div>
                        <label for="contact_number">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                        <span class="error"><?php echo $contact_number_err; ?></span>
                    </div>
                    <div>
                        <label for="address">Address</label>
                        <textarea name="address" id="address"><?php echo htmlspecialchars($address); ?></textarea>
                        <span class="error"><?php echo $address_err; ?></span>
                    </div>
                    <div>
                        <label for="product_name">Product Name</label>
                        <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
                        <span class="error"><?php echo $product_name_err; ?></span>
                    </div>
                    <div>
                        <label for="product_image">Product Image</label>
                        <input type="file" name="product_image" id="product_image">
                        <span class="error"><?php echo $image_err; ?></span>
                    </div>
                    <div class="status-toggle">
                        <label for="status">Status:</label>
                        <label class="switch">
                            <input type="checkbox" id="status" name="status" <?php echo ($status == 'active') ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="status-indicator <?php echo ($status == 'active') ? 'status-active' : 'status-inactive'; ?>" id="statusDot"></span>
                        <span class="status-text" id="statusText"><?php echo ucfirst($status); ?></span>
                    </div>
                    <div>
                        <input type="submit" value="Update Supplier">
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