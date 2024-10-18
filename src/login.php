<?php
require_once __DIR__ . '/../config/database.php';

echo "Script started.<br>";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully to the database.<br>";

$sql = "SELECT admin_id, username, password FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "Query executed successfully.<br>";
    echo "Number of rows: " . mysqli_num_rows($result) . "<br>";
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo "Username from DB: " . htmlspecialchars($row['username']) . "<br>";
        echo "Hashed password from DB: " . htmlspecialchars($row['password']) . "<br>";
    } else {
        echo "No user found with username 'admin'.<br>";
    }
} else {
    echo "Error executing query: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
echo "Connection closed.<br>";
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="text" name="username" placeholder="Username">
    <input type="password" name="password" placeholder="Password">
    <input type="submit" value="Login">
</form>