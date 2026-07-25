<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
$action = $_GET['action'] ?? 'add';
$product = null;
$productImages = [];
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
    $images = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ?');
    $images->execute([$_GET['id']]);
    $productImages = $images->fetchAll();
}
$pageTitle = ($action === 'edit') ? 'Edit Product' : 'Add Product';
$errorMessage = sanitizeInput($_GET['error'] ?? '');

$buf = '';
$buf .= '<div class="max-w-3xl mx-auto p-6">';
$buf .= '<h1 class="text-2xl font-bold mb-4">' . $pageTitle . '</h1>';
if ($errorMessage !== '') {
    $buf .= '<div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">' . htmlspecialchars($errorMessage) . '</div>';
}

$buf .= '<form action="product-save.php" method="post" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded-lg shadow">';
$buf .= '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
$buf .= '<input type="hidden" name="action" value="' . htmlspecialchars($action) . '">';
if ($action === 'edit') { $buf .= '<input type="hidden" name="id" value="' . (int)$product['id'] . '">'; }
$buf .= '<div><label class="block text-sm font-medium mb-1">Product Name</label><input name="name" required value="' . htmlspecialchars($product['name'] ?? '') . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Slug (unique)</label><input name="slug" required value="' . htmlspecialchars($product['slug'] ?? '') . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '<div class="grid grid-cols-3 gap-3">';
$buf .= '<div><label class="block text-sm font-medium mb-1">Price</label><input name="price" required type="number" step="0.01" value="' . htmlspecialchars($product['price'] ?? '') . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Stock Quantity</label><input name="stock" type="number" value="' . ((int)($product['stock_quantity'] ?? 0)) . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Category</label><input name="category" value="' . htmlspecialchars($product['category'] ?? '') . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '</div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Sizes (comma separated)</label><input name="sizes" value="' . htmlspecialchars($product['sizes'] ?? 'S,M,L,XL') . '" class="w-full border rounded px-3 py-2"></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Description</label><textarea name="description" rows="4" class="w-full border rounded px-3 py-2">' . htmlspecialchars($product['description'] ?? '') . '</textarea></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Primary Image</label><input type="file" name="image" accept="image/*">';
if (!empty($product['image'])) {
    $buf .= '<div class="mt-2"><img src="../' . ltrim(htmlspecialchars($product['image']), '/') . '" class="img-preview" style="height:80px;object-fit:cover;border-radius:6px"></div>'; }
$buf .= '</div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Additional Images (multiple)</label><input type="file" name="images[]" accept="image/*" multiple>';
if (!empty($productImages)){
    $buf .= '<div class="mt-2 flex gap-2">';
    foreach ($productImages as $img){
        $buf .= '<div class="text-center"><img src="../uploads/products/' . htmlspecialchars($img['filename']) . '" style="height:80px;object-fit:cover;border-radius:6px"><br><a href="product-delete-image.php?id=' . $img['id'] . '&product_id=' . $product['id'] . '" class="text-xs text-red-600">Remove</a></div>';
    }
    $buf .= '</div>';
}
$buf .= '</div>';
$buf .= '<div class="flex gap-3"><button class="bg-purple-700 text-white px-4 py-2 rounded">Save Product</button><a href="products.php" class="px-4 py-2 border rounded">Cancel</a></div>';
$buf .= '</form></div>';

$content = $buf;
$pageTitle = $pageTitle;
include __DIR__ . '/layout.php';
