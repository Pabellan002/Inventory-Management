<?php
// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch suppliers with pagination
$total_records_sql = "SELECT COUNT(*) FROM suppliers";
$total_records = mysqli_fetch_array(mysqli_query($conn, $total_records_sql))[0];
$total_pages = ceil($total_records / $records_per_page);

$sql = "SELECT * FROM suppliers LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $offset, $records_per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - Inventory Management</title>
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
        .suppliers-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .suppliers-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-supplier-btn {
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
        .add-supplier-btn:hover {
            background-color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .add-supplier-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .suppliers-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .suppliers-table th, .suppliers-table td {
            padding: 15px;
            text-align: left;
        }
        .suppliers-table th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }
        .suppliers-table tr {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .suppliers-table tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
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
            background-color: #333333; /* Dark gray, almost black */
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-decoration: none;
        }
        .action-button:hover {
            background-color: #000000; /* Pure black on hover */
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .action-button:active {
            transform: translateY(0);
            box-shadow: none;
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
                <?php if($_SESSION["role"] === "admin"): ?>
                <a href="index.php?page=manage_users" class="menu-item"><i>🎫</i> Manage Users</a>
                <?php endif; ?>
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
            <div class="suppliers-container">
                <div class="suppliers-header">
                    <h1>Manage Suppliers</h1>
                    <a href="index.php?page=add_supplier" class="add-supplier-btn">Add New Supplier</a>
                </div>
                <table class="suppliers-table">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Product Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                echo "<td>
                                        <span class='status-indicator status-" . htmlspecialchars($row['status']) . "'></span>" 
                                        . ucfirst(htmlspecialchars($row['status'])) . 
                                    "</td>";
                                echo "<td class='action-buttons'>
                                        <a href='index.php?page=view_supplier&supplier_id=" . $row['supplier_id'] . "' class='action-button'><i class='fas fa-eye'></i> View</a>
                                        <a href='index.php?page=edit_supplier&supplier_id=" . $row['supplier_id'] . "' class='action-button'><i class='fas fa-edit'></i> Edit</a>
                                        <a href='index.php?page=delete_supplier&supplier_id=" . $row['supplier_id'] . "' class='action-button'><i class='fas fa-trash-alt'></i> Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No suppliers found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo "<a href='index.php?page=suppliers&p=" . $i . "'" . ($i == $page ? " class='active'" : "") . ">" . $i . "</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>