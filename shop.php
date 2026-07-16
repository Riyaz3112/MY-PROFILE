<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shop | LookStylo Clothing';
$pdo = getDbConnection();
$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-purple-700">LookStylo Collection</p>
        <h1 class="mt-2 text-3xl font-bold text-[#301040]">Premium Streetwear Essentials</h1>
    </div>
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($products as $product): ?>
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
                <div class="mb-4 flex h-48 items-center justify-center rounded-2xl bg-gray-100">
                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-40 w-full object-contain">
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="text-lg font-bold text-purple-900">₹<?php echo number_format((float) $product['price'], 0); ?></div>
                </div>
                <p class="mt-3 text-sm text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>
                <form method="post" action="cart.php" class="mt-6 space-y-3">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="size" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            <option value="M">Size: M</option>
                            <option value="L">Size: L</option>
                            <option value="XL">Size: XL</option>
                        </select>
                        <select name="color" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            <option value="Black">Color: Black</option>
                            <option value="White">Color: White</option>
                            <option value="Maroon">Color: Maroon</option>
                        </select>
                    </div>
                    <button class="w-full rounded-full bg-[#301040] px-4 py-3 text-sm font-semibold text-white hover:bg-purple-900">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>