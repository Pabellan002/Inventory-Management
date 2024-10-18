<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Set the base URL
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));

if(isset($_GET['supplier_id']) && is_numeric($_GET['supplier_id'])){
    $supplier_id = $_GET['supplier_id'];
    $sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            } else {
                echo "No supplier found with that ID.";
                exit;
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
            exit;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Invalid supplier ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Supplier</title>
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>View Supplier</h1>
        <p><strong>Supplier Name:</strong> <?php echo htmlspecialchars($row['supplier_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($row['contact_number']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
        <p><strong>Product Image:</strong></p>
        <?php
        if(!empty($row['product_image'])){
            $image_path = $base_url . htmlspecialchars($row['product_image']);
            echo "<img src='" . $image_path . "' alt='Product Image' style='max-width: 300px;'>";
        } else {
            echo 'No image available';
        }
        ?>
        <p><a href="index.php?page=suppliers">Back to Suppliers List</a></p>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>