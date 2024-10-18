<?php
require_once __DIR__ . '/../Core/Database.php';

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAllProducts()
    {
        $sql = "SELECT * FROM products";
        return $this->db->query($sql);
    }

    public function addProduct($data)
    {
        $sql = "INSERT INTO products (name, description, price, expiry_date) VALUES (?, ?, ?, ?)";
        $params = [$data['name'], $data['description'], $data['price'], $data['expiry_date']];
        return $this->db->execute($sql, $params);
    }

    // Add other methods like getProduct, updateProduct, deleteProduct, etc.
}