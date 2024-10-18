<?php
// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php?page=dashboard");
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Define variables and initialize with empty values
$name = $username = $role = $status = "";
$name_err = $username_err = $role_err = $status_err = "";

// Processing form data when form is submitted
if(isset($_POST["users_id"]) && !empty($_POST["users_id"])){
    // Get hidden input value
    $users_id = $_POST["users_id"];
    
    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } else{
        $name = $input_name;
    }
    
    // Validate username
    $input_username = trim($_POST["username"]);
    if(empty($input_username)){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT users_id FROM users WHERE username = ? AND users_id != ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_username, $param_id);
            
            // Set parameters
            $param_username = $input_username;
            $param_id = $users_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = $input_username;
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate role
    $input_role = trim($_POST["role"]);
    if(empty($input_role)){
        $role_err = "Please select a role.";     
    } else{
        $role = $input_role;
    }

    // Validate status
    $input_status = trim($_POST["status"]);
    if(empty($input_status)){
        $status_err = "Please select a status.";     
    } else{
        $status = $input_status;
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($username_err) && empty($role_err) && empty($status_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET name=?, username=?, role=?, status=? WHERE users_id=?";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssi", $param_name, $param_username, $param_role, $param_status, $param_id);
            
            // Set parameters
            $param_name = $name;
            $param_username = $username;
            $param_role = $role;
            $param_status = $status;
            $param_id = $users_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to landing page
                header("location: index.php?page=manage_users");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($conn);
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $users_id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE users_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $users_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $name = $row["name"];
                    $username = $row["username"];
                    $role = $row["role"];
                    $status = $row["status"];
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
        
        // Close connection
        mysqli_close($conn);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .wrapper {
            width: 360px;
            padding: 20px;
            background-color: #fff;
            margin: 0 auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #0047AB;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #003380;
        }
        .btn-cancel {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .error {
            color: #ff0000;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Edit User</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=edit_user" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                <span class="error"><?php echo $name_err;?></span>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="error"><?php echo $username_err;?></span>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                    <option value="admin" <?php echo ($role == "admin") ? "selected" : ""; ?>>Admin</option>
                    <option value="user" <?php echo ($role == "user") ? "selected" : ""; ?>>User</option>
                </select>
                <span class="error"><?php echo $role_err;?></span>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control <?php echo (!empty($status_err)) ? 'is-invalid' : ''; ?>">
                    <option value="1" <?php echo ($status == "1") ? "selected" : ""; ?>>Active</option>
                    <option value="0" <?php echo ($status == "0") ? "selected" : ""; ?>>Inactive</option>
                </select>
                <span class="error"><?php echo $status_err;?></span>
            </div>
            <input type="hidden" name="users_id" value="<?php echo $users_id; ?>"/>
            <input type="submit" class="btn-submit" value="Update User">
            <a href="index.php?page=manage_users" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>