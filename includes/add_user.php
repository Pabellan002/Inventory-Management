<?php
// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php?page=dashboard");
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Define variables and initialize with empty values
$name = $username = $password = $confirm_password = $role = "";
$name_err = $username_err = $password_err = $confirm_password_err = $role_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement
        $sql = "SELECT users_id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate role
    if(empty(trim($_POST["role"]))){
        $role_err = "Please select a role.";
    } else{
        $role = trim($_POST["role"]);
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_username, $param_password, $param_role);
            
            // Set parameters
            $param_name = $name;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = $role;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to manage users page
                header("location: index.php?page=manage_users");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
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
        input[type="password"],
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
        .error {
            color: #ff0000;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Add New User</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?page=add_user" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                <span class="error"><?php echo $name_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="error"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="error"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="error"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Role</option>
                    <option value="admin" <?php echo ($role == "admin") ? "selected" : ""; ?>>Admin</option>
                    <option value="user" <?php echo ($role == "user") ? "selected" : ""; ?>>User</option>
                </select>
                <span class="error"><?php echo $role_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Add User">
            </div>
        </form>
    </div>    
</body>
</html>