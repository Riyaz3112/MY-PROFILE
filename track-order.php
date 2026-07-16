<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$pageTitle = 'Track Order | LookStylo Clothing';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = sanitizeInput($_POST['order_id'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $stmt = $pdo->prepare('SELECT o.*, ot.status, ot.note, ot.created_at FROM orders o LEFT JOIN order_tracking ot ON ot.order_id = o.id WHERE o.order_id = ? AND o.mobile = ? ORDER BY ot.created_at DESC LIMIT 1');
    $stmt->execute([$orderId, $mobile]);
    $result = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-purple-700">Order Tracking</p>
        <h1 class="mt-2 text-3xl font-bold text-[#301040]">Track Your Order</h1>
    </div>
    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="post" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <label class="mb-2 block text-sm font-medium text-gray-700">Order ID</label>
            <input type="text" name="order_id" required class="mb-4 w-full rounded-xl border border-gray-300 px-4 py-3">
            <label class="mb-2 block text-sm font-medium text-gray-700">Mobile Number</label>
            <input type="tel" name="mobile" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
            <button class="mt-6 w-full rounded-full bg-[#301040] px-4 py-3 font-semibold text-white">Track</button>
        </form>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
            <?php if ($result): ?>
                <h2 class="text-xl font-semibold text-gray-900">Order Details</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">Customer Name</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($result['customer_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Current Status</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($result['status'] ?: 'Payment Verification Pending'); ?></p>
                    </div>
                </div>
                <div class="mt-6 rounded-2xl bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-900">Timeline</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li>• Order Placed</li>
                        <li>• Payment Verification Pending</li>
                        <li>• Confirmed</li>
                        <li>• Packed</li>
                        <li>• Shipped</li>
                        <li>• Delivered</li>
                    </ul>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Enter your order ID and mobile number to see the current status.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>