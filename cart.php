<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$pageTitle = 'Cart | LookStylo Clothing';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        die('Invalid CSRF token.');
    }

    if ($_POST['action'] === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $size = sanitizeInput($_POST['size'] ?? 'M');
        $color = sanitizeInput($_POST['color'] ?? 'Black');
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        addToCart($pdo, $productId, $size, $color, $quantity);
        header('Location: cart.php');
        exit;
    }

    if ($_POST['action'] === 'update') {
        updateCartQuantity($pdo, (int) ($_POST['cart_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
        header('Location: cart.php');
        exit;
    }

    if ($_POST['action'] === 'remove') {
        removeCartItem($pdo, (int) ($_POST['cart_id'] ?? 0));
        header('Location: cart.php');
        exit;
    }
}

$items = getCartItems($pdo);
$total = getCartTotal($items);
include __DIR__ . '/includes/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-purple-700">Your Cart</p>
        <h1 class="mt-2 text-3xl font-bold text-[#301040]">Shopping Cart</h1>
    </div>
    <?php if (empty($items)): ?>
        <div class="rounded-3xl border border-gray-200 bg-white p-10 text-center shadow-soft">
            <h2 class="text-2xl font-semibold text-gray-900">Your cart is empty</h2>
            <p class="mt-3 text-gray-600">Add some premium items from our collection to continue.</p>
            <a href="shop.php" class="mt-6 inline-flex rounded-full bg-[#301040] px-6 py-3 font-semibold text-white">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-soft md:flex-row md:items-center">
                        <div class="h-24 w-full rounded-2xl bg-gray-100 md:w-24">
                            <img src="<?php echo htmlspecialchars($item['image'] ?: 'images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-full w-full object-cover rounded-2xl">
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="mt-1 text-sm text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?> • Color: <?php echo htmlspecialchars($item['color']); ?></p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <form method="post" class="flex items-center gap-2">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo (int) $item['cart_id']; ?>">
                                    <label class="text-sm text-gray-500">Qty</label>
                                    <input type="number" name="quantity" value="<?php echo (int) $item['quantity']; ?>" min="1" class="w-20 rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <button class="rounded-full border border-gray-300 px-3 py-2 text-sm">Update</button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?php echo (int) $item['cart_id']; ?>">
                                    <button class="rounded-full border border-red-200 px-3 py-2 text-sm text-red-600">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Price</p>
                            <p class="text-xl font-bold text-[#301040]">₹<?php echo number_format((float) $item['price'] * (int) $item['quantity'], 0); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
                <h2 class="text-xl font-semibold text-gray-900">Order Summary</h2>
                <div class="mt-6 space-y-3 text-sm text-gray-600">
                    <div class="flex justify-between"><span>Subtotal</span><span>₹<?php echo number_format($total, 0); ?></span></div>
                    <div class="flex justify-between"><span>Shipping</span><span>Free</span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-semibold text-gray-900"><span>Total Amount</span><span>₹<?php echo number_format($total, 0); ?></span></div>
                </div>
                <a href="checkout.php" class="mt-6 inline-flex w-full justify-center rounded-full bg-[#301040] px-4 py-3 text-center font-semibold text-white hover:bg-purple-900">Proceed to Checkout</a>
                <a href="shop.php" class="mt-3 inline-flex w-full justify-center rounded-full border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>