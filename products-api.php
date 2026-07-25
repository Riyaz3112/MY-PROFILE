<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
try {
    $stmt = $pdo->query('SELECT id, name, slug, price, image, description, category, stock_quantity, sizes FROM products ORDER BY id DESC');
    $products = $stmt->fetchAll();
    $out = [];
    foreach ($products as $p) {
        $imgs = $pdo->prepare('SELECT filename FROM product_images WHERE product_id = ? ORDER BY id ASC');
        $imgs->execute([$p['id']]);
        $files = $imgs->fetchAll();
        $imageList = [];
        if (!empty($p['image'])) { $imageList[] = ltrim($p['image'], '/'); }
        foreach ($files as $f) { $imageList[] = 'uploads/products/' . ltrim($f['filename'], '/'); }
        $out[] = [
            'id' => (int)$p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'price' => (float)$p['price'],
            'image' => $imageList[0] ?? ($p['image'] ? ltrim($p['image'], '/') : null),
            'images' => $imageList,
            'description' => $p['description'],
            'category' => $p['category'],
            'stock' => (int)$p['stock_quantity'],
            'sizes' => $p['sizes']
        ];
    }
    echo json_encode(['success' => true, 'products' => $out]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
