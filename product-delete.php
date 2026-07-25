<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0){
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
    $countStmt->execute([$id]);
    $referenced = (int)$countStmt->fetchColumn();

    if ($referenced > 0) {
        header('Location: products.php?error=' . urlencode('Cannot delete product because it is referenced by existing orders.'));
        exit;
    }

    // delete product images from disk
    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image'])){
        $path = __DIR__ . '/../' . $row['image'];
        if (file_exists($path)) @unlink($path);
    }
    $imgs = $pdo->prepare('SELECT filename FROM product_images WHERE product_id = ?');
    $imgs->execute([$id]);
    foreach ($imgs->fetchAll() as $img){
        $p = __DIR__ . '/../uploads/products/' . $img['filename'];
        if (file_exists($p)) @unlink($p);
    }

    $del = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $del->execute([$id]);
}
header('Location: products.php'); exit;
