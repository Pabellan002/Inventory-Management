<?php
// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php?page=dashboard");
    exit;
}

// Include config file
require_once __DIR__ . '/../config/database.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch users with pagination
$total_records_sql = "SELECT COUNT(*) FROM users";
$total_records = mysqli_fetch_array(mysqli_query($conn, $total_records_sql))[0];
$total_pages = ceil($total_records / $records_per_page);

$sql = "SELECT users_id, name, username, role, status FROM users LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Inventory Management</title>
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
        .users-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-user-btn {
            background-color: #333333;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .add-user-btn:hover {
            background-color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .add-user-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .users-table th, .users-table td {
            padding: 15px;
            text-align: left;
        }
        .users-table th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }
        .users-table tr {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .users-table tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .action-link {
            margin-right: 10px;
            color: #0047AB;
            text-decoration: none;
            transition: color 0.3s;
        }
        .action-link:hover {
            color: #003380;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: white;
            background-color: #333333;
            padding: 8px 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 4px;
            border-radius: 4px;
            font-weight: bold;
        }
        .pagination a.active {
            background-color: #000000;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #333333;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .action-button:hover {
            background-color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .action-button:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .edit-btn, .delete-btn {
            background-color: #000000;
        }
        .edit-btn:hover, .delete-btn:hover {
            background-color: #333333;
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
                <a href="index.php?page=suppliers" class="menu-item"><i>📥</i> Suppliers</a>
                <a href="index.php?page=manage_users" class="menu-item active"><i>🎫</i> Manage Users</a>
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
            <div class="users-container">
                <div class="users-header">
                    <h1>Manage Users</h1>
                    <a href="index.php?page=add_user" class="add-user-btn">Add New User</a>
                </div>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . $row['role'] . "</td>";
                                echo "<td>" . ($row['status'] ? 'Active' : 'Inactive') . "</td>";
                                echo "<td class='action-buttons'>
                                        <button onclick=\"location.href='index.php?page=edit_user&id=" . $row['users_id'] . "'\" class='action-button edit-btn'>Edit</button>
                                        <button onclick=\"if(confirm('Are you sure you want to delete this user?')) location.href='index.php?page=delete_user&id=" . $row['users_id'] . "'\" class='action-button delete-btn'>Delete</button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo "<a href='index.php?page=manage_users&p=" . $i . "'" . ($i == $page ? " class='active'" : "") . ">" . $i . "</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>