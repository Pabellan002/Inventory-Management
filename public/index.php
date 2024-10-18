<?php
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Controllers/ProductController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/SupplierController.php';
require_once __DIR__ . '/../app/Controllers/StockController.php';

$router = new Router();

$router->addRoute('', 'ProductController', 'index');
$router->addRoute('products', 'ProductController', 'index');
$router->addRoute('add_product', 'ProductController', 'add');
$router->addRoute('users', 'UserController', 'index');
$router->addRoute('suppliers', 'SupplierController', 'index');
$router->addRoute('stocks', 'StockController', 'index');

// Add more routes as needed

$url = $_GET['url'] ?? '';
$router->dispatch($url);