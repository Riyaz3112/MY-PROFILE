<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'My Orders | LookStylo Clothing';
include __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$customerId = $_SESSION['customer_id'] ?? null;

// Get user info if logged in
$userInfo = null;
$orders = [];

if ($customerId) {
    // Fetch user info
    $userStmt = $pdo->prepare('SELECT id, full_name, mobile, email FROM users WHERE id = ?');
    $userStmt->execute([$customerId]);
    $userInfo = $userStmt->fetch();
    
    // Fetch user's orders
    $orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $orderStmt->execute([$customerId]);
    $orders = $orderStmt->fetchAll();
}

?>

<main class="max-w-7xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">My Orders</h1>
        <p class="text-gray-600">View and track your orders</p>
    </div>

    <?php if (!$customerId): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <p class="text-gray-700 mb-4">You need to place an order first to view your orders.</p>
            <a href="shop.php" class="inline-block bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-semibold">Continue Shopping</a>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <p class="text-gray-700 mb-4">You haven't placed any orders yet.</p>
            <a href="shop.php" class="inline-block bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-semibold">Start Shopping</a>
        </div>
    <?php else: ?>
        <!-- User Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Account Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="font-medium"><?php echo htmlspecialchars($userInfo['full_name']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Mobile</p>
                    <p class="font-medium"><?php echo htmlspecialchars($userInfo['mobile']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-medium"><?php echo htmlspecialchars($userInfo['email']); ?></p>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
                <?php
                // Get order items
                $itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
                $itemStmt->execute([$order['id']]);
                $items = $itemStmt->fetchAll();
                
                // Get status color
                $statusColor = 'gray';
                if (strpos($order['status'], 'Delivered') !== false) $statusColor = 'green';
                elseif (strpos($order['status'], 'Pending') !== false) $statusColor = 'yellow';
                elseif (strpos($order['status'], 'Cancelled') !== false) $statusColor = 'red';
                elseif (strpos($order['status'], 'Verification') !== false) $statusColor = 'blue';
                ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <!-- Order Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 pb-4 border-b">
                        <div>
                            <p class="text-sm text-gray-600">Order ID</p>
                            <p class="font-bold text-lg text-[#301040]"><?php echo htmlspecialchars($order['order_id']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-medium"><?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="font-bold text-lg text-purple-700">₹<?php echo number_format((float)$order['total_amount'], 0); ?></p>
                        </div>
                        <div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold" style="background: <?php echo $statusColor; ?>50; color: <?php echo $statusColor; ?>700; border: 1px solid <?php echo $statusColor; ?>200;">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-600 mb-2">Items (<?php echo count($items); ?>)</p>
                        <div class="space-y-2">
                            <?php foreach ($items as $item): ?>
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                        <p class="text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?> | Color: <?php echo htmlspecialchars($item['color']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">Qty: <?php echo (int)$item['quantity']; ?></p>
                                        <p class="text-gray-600">₹<?php echo number_format((float)$item['price'], 0); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="mb-4 p-3 bg-gray-50 rounded">
                        <p class="text-xs font-semibold text-gray-600 mb-2">DELIVERY TO</p>
                        <p class="text-sm"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['address']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> - <?php echo htmlspecialchars($order['pincode']); ?></p>
                    </div>

                    <!-- Payment Info -->
                    <div class="flex flex-col md:flex-row gap-4 text-sm">
                        <div class="flex-1">
                            <p class="text-gray-600">Payment Method</p>
                            <p class="font-medium">UPI Transfer / Bank Transfer</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-600">Transaction ID (UTR)</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['utr_number']); ?></p>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-600">Mobile</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['mobile']); ?></p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 pt-4 border-t flex gap-2">
                        <a href="track-order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="inline-block bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded text-sm font-medium">
                            Track Order
                        </a>
                        <?php if (!empty($order['payment_screenshot'])): ?>
                            <a href="uploads/payments/<?php echo htmlspecialchars($order['payment_screenshot']); ?>" target="_blank" class="inline-block border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded text-sm font-medium">
                                View Receipt
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-8 text-center">
        <a href="shop.php" class="text-purple-700 hover:underline font-medium">Continue Shopping</a>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
