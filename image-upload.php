<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$pdo = getDbConnection();
$pageTitle = 'Upload Product Images';

// Handle image upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['productImage'])) {
    $productId = (int) ($_POST['product_id'] ?? 0);
    
    if ($productId <= 0) {
        $message = '<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded">Please select a product</div>';
    } else {
        $file = $_FILES['productImage'];
        
        // Validate
        if ($file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if ($ext === 'jpeg') $ext = 'jpg';
            
            if (!in_array($ext, $allowed)) {
                $message = '<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded">Invalid file type. Only JPG, PNG, WebP allowed</div>';
            } else {
                $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                $target = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    // Save as primary image if checked
                    if (!empty($_POST['set_primary'])) {
                        $upd = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
                        $upd->execute(['uploads/products/' . $filename, $productId]);
                        $message = '<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded">✓ Image uploaded and set as primary!</div>';
                    } else {
                        // Save as additional image
                        $ins = $pdo->prepare('INSERT INTO product_images (product_id, filename) VALUES (?, ?)');
                        $ins->execute([$productId, $filename]);
                        $message = '<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded">✓ Image uploaded and added to product!</div>';
                    }
                } else {
                    $message = '<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded">Failed to save image</div>';
                }
            }
        } else {
            $message = '<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded">Upload error: ' . $file['error'] . '</div>';
        }
    }
}

// Get all products
$products = $pdo->query('SELECT id, name FROM products ORDER BY name ASC')->fetchAll();

$content = '<div class="max-w-2xl mx-auto p-6">';
$content .= '<h1 class="text-2xl font-bold mb-6">Upload Product Images</h1>';

if ($message) {
    $content .= $message . '<br>';
}

$content .= '<div class="bg-white p-6 rounded-lg shadow-sm space-y-4">';
$content .= '<form method="post" enctype="multipart/form-data" class="space-y-4">';
$content .= '<div>';
$content .= '<label class="block text-sm font-medium mb-2">Select Product</label>';
$content .= '<select name="product_id" required class="w-full border border-gray-300 rounded px-3 py-2">';
$content .= '<option value="">-- Choose Product --</option>';
foreach ($products as $p) {
    $content .= '<option value="' . (int)$p['id'] . '">' . htmlspecialchars($p['name']) . '</option>';
}
$content .= '</select>';
$content .= '</div>';

$content .= '<div>';
$content .= '<label class="block text-sm font-medium mb-2">Select Image File</label>';
$content .= '<input type="file" name="productImage" accept="image/*" required class="w-full border border-gray-300 rounded px-3 py-2">';
$content .= '<p class="text-xs text-gray-600 mt-1">Supported: JPG, PNG, WebP (Max 5MB)</p>';
$content .= '</div>';

$content .= '<div class="flex items-center gap-2">';
$content .= '<input type="checkbox" name="set_primary" id="setPrimary" class="rounded">';
$content .= '<label for="setPrimary" class="text-sm">Set as primary product image</label>';
$content .= '</div>';

$content .= '<button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold px-4 py-2 rounded">Upload Image</button>';
$content .= '</form>';
$content .= '</div>';

$content .= '<div class="mt-8 bg-white p-6 rounded-lg shadow-sm">';
$content .= '<h2 class="text-lg font-semibold mb-4">Quick Info</h2>';
$content .= '<ul class="text-sm text-gray-700 space-y-2">';
$content .= '<li>✓ Images are saved to: uploads/products/</li>';
$content .= '<li>✓ Each product can have multiple images</li>';
$content .= '<li>✓ First image is shown on shop page</li>';
$content .= '<li>✓ All images are used on product detail page</li>';
$content .= '<li><a href="products.php" class="text-purple-700 hover:underline">Go to Products →</a></li>';
$content .= '</ul>';
$content .= '</div>';

$content .= '</div>';

include __DIR__ . '/layout.php';
?>
