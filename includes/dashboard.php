<?php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?page=login");
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get total number of products
$sql = "SELECT COUNT(*) as total_products FROM products";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'];

// Get total number of suppliers
$sql = "SELECT COUNT(*) as total_suppliers FROM suppliers";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_suppliers = $row['total_suppliers'];

// Get total stock value
$sql = "SELECT SUM(price) as total_value FROM products";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_value = $row['total_value'];

// Get low stock products (you may need to adjust this query based on your new stock management system)
$sql = "SELECT name, price FROM products ORDER BY price ASC LIMIT 5";
$low_stock_products = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff; /* Soft beige background */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .dashboard-container {
            display: flex;
            width: 99%;
            max-width: 2300px;
            background-color: #;
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
            background-color: #ffffff; /* Keep main content white */
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
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
        }
        .card .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .chart {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .recent-activities {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            font-weight: bold;
            color: #666;
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
                <a href="index.php?page=dashboard" class="menu-item active"><i>📊</i> Dashboard</a>
                <a href="index.php?page=products" class="menu-item"><i>📑</i> Products</a>
                <a href="index.php?page=stocks" class="menu-item"><i>📄</i> Stocks</a>
            </div>
            <div class="menu-group">
                <a href="index.php?page=suppliers" class="menu-item"><i>📥</i> Suppliers</a>
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
                    <img src="path_to_default_avatar.jpg" alt="User Profile">
                </div>
            </div>
            <div class="dashboard-content">
                <h1>Dashboard</h1>
                <div class="dashboard-cards">
                    <div class="card">
                        <h2>Total Products</h2>
                        <p><?php echo $total_products; ?></p>
                    </div>
                    <div class="card">
                        <h2>Total Suppliers</h2>
                        <p><?php echo $total_suppliers; ?></p>
                    </div>
                    <div class="card">
                        <h2>Total Stock Value</h2>
                        <p>₱<?php echo number_format($total_value, 2); ?></p>
                    </div>
                </div>
                <div class="dashboard-tables">
                    <div class="table-container">
                        <h2>Low Stock Products</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($low_stock_products)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>