<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();

function moveUploadedFile($file){
    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    // validate
    if (!isValidImageUpload($file)) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','webp'];
    $ext = strtolower($ext);
    if ($ext === 'jpeg') $ext = 'jpg';
    if (!in_array($ext, $allowed, true)) return null;
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $target = $uploadDir . $name;
    if (move_uploaded_file($file['tmp_name'], $target)){
        return 'uploads/products/' . $name;
    }
    return null;
}

$action = $_POST['action'] ?? 'add';
if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) { die('Invalid CSRF token'); }
$name = sanitizeInput($_POST['name'] ?? '');
$slug = sanitizeInput($_POST['slug'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$category = sanitizeInput($_POST['category'] ?? 'general');
$sizes = sanitizeInput($_POST['sizes'] ?? 'S,M,L,XL');
$description = $_POST['description'] ?? '';

if ($slug === '') {
    $error = 'Product slug is required.';
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        header('Location: product-edit.php?action=edit&id=' . $id . '&error=' . urlencode($error));
    } else {
        header('Location: product-edit.php?action=add&error=' . urlencode($error));
    }
    exit;
}

$slugCheck = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = ?' . ($action === 'edit' ? ' AND id != ?' : ''));
if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $slugCheck->execute([$slug, $id]);
} else {
    $slugCheck->execute([$slug]);
}
if ((int)$slugCheck->fetchColumn() > 0) {
    $error = 'The slug ' . htmlspecialchars($slug) . ' is already in use. Please choose a unique slug.';
    if ($action === 'edit') {
        header('Location: product-edit.php?action=edit&id=' . $id . '&error=' . urlencode($error));
    } else {
        header('Location: product-edit.php?action=add&error=' . urlencode($error));
    }
    exit;
}

if ($action === 'add'){
    $imagePath = null;
    if (!empty($_FILES['image']['tmp_name'])){
        $imagePath = moveUploadedFile($_FILES['image']);
        if ($imagePath) {
            // create thumb for primary image
            $primaryName = basename($imagePath);
            $src = __DIR__ . '/../uploads/products/' . $primaryName;
            $thumbDir = __DIR__ . '/../uploads/products/thumbs/';
            if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
            $thumbPath = $thumbDir . $primaryName . '.jpg';
            @createThumbnail($src, $thumbPath, 400, 400);
        }
    }
    $stmt = $pdo->prepare('INSERT INTO products (name, slug, price, image, description, category, stock_quantity, sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $slug, $price, $imagePath, $description, $category, $stock, $sizes]);
    $productId = $pdo->lastInsertId();

    // additional images
    if (!empty($_FILES['images'])){
        foreach ($_FILES['images']['tmp_name'] as $idx => $tmp){
            if (empty($tmp)) continue;
            $file = [
                'name' => $_FILES['images']['name'][$idx],
                'tmp_name' => $tmp,
                'error' => $_FILES['images']['error'][$idx]
            ];
            $moved = moveUploadedFile($file);
            if ($moved){
                $nameOnly = basename($moved);
                $ins = $pdo->prepare('INSERT INTO product_images (product_id, filename) VALUES (?, ?)');
                $ins->execute([$productId, $nameOnly]);
                // create thumbnail
                $src = __DIR__ . '/../uploads/products/' . $nameOnly;
                $thumbDir = __DIR__ . '/../uploads/products/thumbs/';
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
                $thumbPath = $thumbDir . $nameOnly . '.jpg';
                @createThumbnail($src, $thumbPath, 300, 300);
            }
        }
    }

    header('Location: products.php'); exit;
}

if ($action === 'edit'){
    $id = (int)($_POST['id'] ?? 0);
    $imagePath = null;
    if (!empty($_FILES['image']['tmp_name'])){
        $imagePath = moveUploadedFile($_FILES['image']);
        // update image
        $upd = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
        $upd->execute([$imagePath, $id]);
    }
    $stmt = $pdo->prepare('UPDATE products SET name = ?, slug = ?, price = ?, description = ?, category = ?, stock_quantity = ?, sizes = ? WHERE id = ?');
    $stmt->execute([$name, $slug, $price, $description, $category, $stock, $sizes, $id]);

    if (!empty($_FILES['images'])){
        foreach ($_FILES['images']['tmp_name'] as $idx => $tmp){
            if (empty($tmp)) continue;
            $file = [
                'name' => $_FILES['images']['name'][$idx],
                'tmp_name' => $tmp,
                'error' => $_FILES['images']['error'][$idx]
            ];
            $moved = moveUploadedFile($file);
            if ($moved){
                $ins = $pdo->prepare('INSERT INTO product_images (product_id, filename) VALUES (?, ?)');
                $ins->execute([$id, basename($moved)]);
            }
        }
    }

    header('Location: products.php'); exit;
}

// fallback
header('Location: products.php'); exit;
