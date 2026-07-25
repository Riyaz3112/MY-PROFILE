<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) {
    header('Location: login.php'); exit;
}
$pdo = getDbConnection();
$pageTitle = 'Manage Products';
$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$errorMessage = sanitizeInput($_GET['error'] ?? '');

$content = '<div class="max-w-6xl mx-auto p-6">';
if ($errorMessage !== '') {
    $content .= '<div class="mb-4 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">' . htmlspecialchars($errorMessage) . '</div>';
}
$content .= '<div class="flex items-center justify-between mb-4"><h1 class="text-2xl font-bold">Products</h1><div><a href="product-edit.php?action=add" role="button" aria-label="Add Product" class="inline-block px-3 py-2 rounded bg-[#301040] text-white font-medium shadow hover:opacity-95 focus:outline-none">Add Product</a></div></div>'
    . '<div class="overflow-x-auto bg-white rounded-lg shadow"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-3">ID</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Actions</th></tr></thead><tbody>';

foreach ($products as $p) {
    $content .= '<tr class="border-t"><td class="px-4 py-3">' . $p['id'] . '</td><td class="px-4 py-3">' . htmlspecialchars($p['name']) . '</td><td class="px-4 py-3">₹' . number_format((float)$p['price'],0) . '</td><td class="px-4 py-3">' . htmlspecialchars($p['category']) . '</td><td class="px-4 py-3">' . (int)$p['stock_quantity'] . '</td><td class="px-4 py-3"><a href="product-edit.php?action=edit&id=' . $p['id'] . '" class="text-indigo-600 mr-3">Edit</a><a href="product-delete.php?id=' . $p['id'] . '" onclick="return confirm(\'Delete this product?\');" class="text-red-600">Delete</a></td></tr>';
}

$content .= '</tbody></table></div></div>';

include __DIR__ . '/layout.php';
