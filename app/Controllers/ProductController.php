<?php
require_once __DIR__ . '/../Models/Product.php';

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index()
    {
        $products = $this->productModel->getAllProducts();
        require __DIR__ . '/../Views/products.php';
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission
            $this->productModel->addProduct($_POST);
            header('Location: /products');
        } else {
            require __DIR__ . '/../Views/add_product.php';
        }
    }

    // Add other methods like edit, delete, etc.
}