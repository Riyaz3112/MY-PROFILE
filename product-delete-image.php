<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);
$productId = (int)($_GET['product_id'] ?? 0);
if ($id > 0){
    $stmt = $pdo->prepare('SELECT filename FROM product_images WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row){
        $p = __DIR__ . '/../uploads/products/' . $row['filename'];
        if (file_exists($p)) @unlink($p);
    }
    $del = $pdo->prepare('DELETE FROM product_images WHERE id = ?');
    $del->execute([$id]);
}
header('Location: product-edit.php?action=edit&id=' . $productId);
exit;
