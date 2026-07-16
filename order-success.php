<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$pageTitle = 'Order Success | LookStylo Clothing';
$orderCode = $_GET['order'] ?? '';
$order = $pdo->prepare('SELECT * FROM orders WHERE order_id = ?');
$order->execute([$orderCode]);
$orderData = $order->fetch();
include __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="rounded-3xl border border-green-200 bg-white p-10 text-center shadow-soft">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-[#301040]">Thank You for Your Order!</h1>
        <p class="mt-3 text-gray-600">Your order has been placed successfully and is pending payment verification.</p>
        <?php if ($orderData): ?>
            <div class="mt-8 grid gap-4 rounded-2xl bg-gray-50 p-6 text-left md:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Order ID</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($orderData['order_id']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Customer Name</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($orderData['customer_name']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Amount</p>
                    <p class="text-lg font-semibold text-gray-900">₹<?php echo number_format((float) $orderData['total_amount'], 0); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Payment Status</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($orderData['status']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="shop.php" class="rounded-full bg-[#301040] px-6 py-3 font-semibold text-white">Continue Shopping</a>
            <a href="track-order.php" class="rounded-full border border-gray-300 px-6 py-3 font-semibold text-gray-700">Track Order</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>