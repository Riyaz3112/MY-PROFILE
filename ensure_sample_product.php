<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo = getDbConnection();
    $r = $pdo->query("SELECT id FROM products LIMIT 1")->fetch();
    if ($r) { echo "product_exists:" . $r['id'] . "\n"; exit; }
    $stmt = $pdo->prepare('INSERT INTO products (name,slug,price,image,description,category) VALUES (?,?,?,?,?,?)');
    $stmt->execute(['Sample Product','sample-product',99.00,'images/sample.jpg','Sample product for testing','testing']);
    echo "inserted_product_id:" . $pdo->lastInsertId() . "\n";
} catch (Throwable $e) { echo 'ERROR: ' . $e->getMessage() . "\n"; }
