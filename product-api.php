<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0){
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product){
    echo json_encode(['success' => false, 'message' => 'Product not found']); exit;
}
$imgs = $pdo->prepare('SELECT filename FROM product_images WHERE product_id = ? ORDER BY id ASC');
$imgs->execute([$id]);
$files = $imgs->fetchAll();
$imageList = [];
foreach ($files as $f){ $imageList[] = 'uploads/products/' . ltrim($f['filename'], '/'); }

// include primary image as first if present
if (!empty($product['image'])){ 
  $primaryImg = ltrim($product['image'], '/');
  if (strpos($primaryImg, 'uploads/products/') === false) {
    $primaryImg = 'uploads/products/' . $primaryImg;
  }
  array_unshift($imageList, $primaryImg); 
}

echo json_encode(['success' => true, 'product' => $product, 'images' => $imageList]);
exit;
