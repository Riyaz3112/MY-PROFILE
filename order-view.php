<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
$orderCode = $_GET['order'] ?? '';
if (!$orderCode) { header('Location: orders.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ?');
$stmt->execute([$orderCode]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }
$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$order['id']]);
$items = $items->fetchAll();
$payments = $pdo->prepare('SELECT * FROM payments WHERE order_id = ?');
$payments->execute([$order['id']]);
$payment = $payments->fetch();
$trackingStmt = $pdo->prepare('SELECT status, note, created_at FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC');
$trackingStmt->execute([$order['id']]);
$trackingEntries = $trackingStmt->fetchAll();

$pageTitle = 'Order ' . htmlspecialchars($order['order_id']);
$buf = '';
$buf .= '<div class="max-w-4xl mx-auto p-6">';
$buf .= '<div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-bold">Order ' . htmlspecialchars($order['order_id']) . '</h1><div><a href="invoice.php?order=' . urlencode($order['order_id']) . '" class="rounded-full bg-green-600 px-3 py-2 text-white">Download Invoice</a> <a href="orders.php" class="rounded-full border px-3 py-2">Back</a></div></div>';

$buf .= '<div class="grid gap-6 md:grid-cols-2">';
$buf .= '<div class="rounded-2xl bg-white p-4"><h3 class="font-semibold">Customer</h3><p class="mt-2 text-sm">' . htmlspecialchars($order['customer_name']) . '</p><p class="text-sm text-gray-600">' . htmlspecialchars($order['mobile']) . ' • ' . htmlspecialchars($order['email']) . '</p><p class="mt-2 text-sm">' . nl2br(htmlspecialchars($order['address'])) . '</p><p class="mt-2 text-sm text-gray-600">' . htmlspecialchars($order['city']) . ', ' . htmlspecialchars($order['state']) . ' - ' . htmlspecialchars($order['pincode']) . '</p></div>';
$buf .= '<div class="rounded-2xl bg-white p-4"><h3 class="font-semibold">Payment</h3><p class="mt-2 text-sm">UTR: ' . htmlspecialchars($order['utr_number']) . '</p><p class="text-sm">Payment Status: <strong>' . htmlspecialchars($payment['status'] ?? 'N/A') . '</strong></p>';
if (!empty($order['payment_screenshot'])){ $buf .= '<div class="mt-3"><img src="/uploads/payments/' . htmlspecialchars($order['payment_screenshot']) . '" class="w-48 rounded"></div>'; }
$buf .= '</div></div>';

$buf .= '<div class="mt-6 rounded-2xl bg-white p-4"><h3 class="font-semibold">Items</h3><div class="mt-3 space-y-3">';
foreach ($items as $it){
    $buf .= '<div class="flex justify-between"><div><div class="font-medium">' . htmlspecialchars($it['product_name']) . '</div><div class="text-sm text-gray-600">Size: ' . htmlspecialchars($it['size']) . ' • Color: ' . htmlspecialchars($it['color']) . '</div></div><div class="text-sm">₹' . number_format((float)$it['price'] * (int)$it['quantity'],0) . ' <span class="text-gray-500">(x' . (int)$it['quantity'] . ')</span></div></div>';
}
$buf .= '<div class="mt-4 border-t pt-3 text-right font-semibold">Total: ₹' . number_format((float)$order['total_amount'],0) . '</div></div>';

$buf .= '<div class="mt-6 rounded-2xl bg-white p-4"><h3 class="font-semibold">Tracking History</h3>';
if (!empty($trackingEntries)) {
    foreach ($trackingEntries as $track) {
        $buf .= '<div class="mt-3 border-t pt-3">
                    <div class="font-medium">' . htmlspecialchars($track['status']) . '</div>
                    <div class="text-sm text-gray-600">' . nl2br(htmlspecialchars($track['note'])) . '</div>
                    <div class="text-xs text-gray-400 mt-1">' . htmlspecialchars($track['created_at']) . '</div>
                </div>';
    }
} else {
    $buf .= '<p class="mt-3 text-sm text-gray-600">No tracking updates yet.</p>';
}
$buf .= '</div>';

$buf .= '<form method="post" action="order-update.php" class="mt-6 bg-white p-4 rounded-2xl">';
$buf .= '<input type="hidden" name="csrf_token" value="' . csrfToken() . '"><input type="hidden" name="order_id" value="' . htmlspecialchars($order['order_id']) . '">';
$buf .= '<div class="grid gap-3 md:grid-cols-2"><div><label class="block text-sm font-medium mb-1">Order Status</label><select name="status" class="w-full border rounded px-3 py-2">';
$statuses = ['Payment Verification Pending','Processing','Shipped','Delivered','Cancelled'];
foreach ($statuses as $s){ $buf .= '<option value="' . htmlspecialchars($s) . '"' . (($order['status'] === $s) ? ' selected' : '') . '>' . htmlspecialchars($s) . '</option>'; }
$buf .= '</select></div>';
$buf .= '<div><label class="block text-sm font-medium mb-1">Payment Status</label><select name="payment_status" class="w-full border rounded px-3 py-2">';
$pStates = ['Pending Verification','Verified','Failed']; foreach ($pStates as $ps){ $buf .= '<option value="' . htmlspecialchars($ps) . '"' . ((($payment['status'] ?? '') === $ps) ? ' selected' : '') . '>' . htmlspecialchars($ps) . '</option>'; }
$buf .= '</select></div></div>';
$buf .= '<div class="mt-4 flex gap-3"><button class="bg-[#301040] text-white px-4 py-2 rounded">Update</button><a href="orders.php" class="px-4 py-2 border rounded">Cancel</a></div></form>';

$buf .= '<form method="post" action="verify-payment.php" class="mt-4 inline-block"><input type="hidden" name="csrf_token" value="' . csrfToken() . '"><input type="hidden" name="order_id" value="' . htmlspecialchars($order['order_id']) . '"><button class="bg-green-600 text-white px-4 py-2 rounded">Mark Payment Verified</button></form>';

$buf .= '</div>';

$content = $buf;
include __DIR__ . '/layout.php';
