<?php

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

if(isset($_POST["supplier_id"]) && !empty($_POST["supplier_id"])){
    $supplier_id = trim($_POST["supplier_id"]);
    
    // Prepare a select statement to get the supplier name and image path
    $sql = "SELECT supplier_name, product_image FROM suppliers WHERE supplier_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $supplier_name = $row["supplier_name"];
                $image_path = $row["product_image"];
                
                // Prepare a delete statement
                $sql = "DELETE FROM suppliers WHERE supplier_id = ?";
                
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "i", $supplier_id);
                    
                    if(mysqli_stmt_execute($stmt)){
                        // Delete the image file if it exists
                        if(!empty($image_path) && file_exists(__DIR__ . '/..' . $image_path)){
                            unlink(__DIR__ . '/..' . $image_path);
                        }
                        
                        // Set success message
                        $_SESSION['delete_message'] = "Supplier '$supplier_name' was successfully deleted.";
                        
                        // Redirect to landing page
                        header("location: index.php?page=suppliers");
                        exit();
                    } else{
                        $_SESSION['delete_message'] = "Error: Unable to delete the supplier. Please try again later.";
                    }
                }
            } else{
                $_SESSION['delete_message'] = "No supplier found with that ID.";
            }
        } else{
            $_SESSION['delete_message'] = "Oops! Something went wrong. Please try again later.";
        }
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($conn);
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // If it's a GET request, show the confirmation form
    $supplier_id = trim($_GET["supplier_id"]);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Delete Supplier</title>
        <style>
            .wrapper{ width: 600px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <h2>Delete Supplier</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=delete_supplier" method="post">
                <div>
                    <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>"/>
                    <p>Are you sure you want to delete this supplier?</p>
                    <p>
                        <input type="submit" value="Yes">
                        <a href="index.php?page=suppliers">No</a>
                    </p>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
} else {
    // If supplier_id is not provided
    $_SESSION['delete_message'] = "Error: No supplier specified for deletion.";
    header("location: index.php?page=suppliers");
    exit();
}

// If we get here, redirect back to the suppliers page
header("location: index.php?page=suppliers");
exit();
?>