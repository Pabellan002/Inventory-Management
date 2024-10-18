<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

error_log('get_product_details.php called with product_id: ' . $_GET['product_id']);

if (isset($_GET['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
    $sql = "SELECT * FROM products WHERE product_id = '$product_id'";
    error_log('SQL query: ' . $sql);
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            error_log('Product found: ' . json_encode($row));
            echo json_encode($row);
        } else {
            error_log('Product not found');
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        error_log('MySQL error: ' . mysqli_error($conn));
        echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    }
} else {
    error_log('No product ID provided');
    echo json_encode(['error' => 'No product ID provided']);
}

mysqli_close($conn);