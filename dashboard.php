<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$pdo = getDbConnection();
$pageTitle = 'Admin Dashboard';

// Order Statistics
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Payment Verification Pending'")->fetchColumn();
$deliveredOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Delivered'")->fetchColumn();
$totalSales = (float) $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn();
$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 20')->fetchAll();

// Billing Statistics - with error handling
$totalBillings = 0;
$totalBillingAmount = 0;
$totalBillingCash = 0;
$totalBillingGpay = 0;
$billings = [];

try {
    $totalBillings = (int) $pdo->query('SELECT COUNT(*) FROM billing_records')->fetchColumn();
    $totalBillingAmount = (float) $pdo->query('SELECT COALESCE(SUM(total), 0) FROM billing_records')->fetchColumn();
    $totalBillingCash = (float) $pdo->query('SELECT COALESCE(SUM(cash), 0) FROM billing_records')->fetchColumn();
    $totalBillingGpay = (float) $pdo->query('SELECT COALESCE(SUM(gpay), 0) FROM billing_records')->fetchColumn();
    $billings = $pdo->query('SELECT * FROM billing_records ORDER BY bill_date DESC LIMIT 10')->fetchAll();
} catch (PDOException $e) {
    // Table doesn't exist yet - skip billing section
}

$content = '<div class="mb-4"><h2 class="text-xl font-semibold text-gray-900 mb-4">Orders Overview</h2></div>'
    . '<div class="grid gap-4 md:grid-cols-4">'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Orders</p><p class="mt-2 text-2xl font-bold text-[#301040]">' . $totalOrders . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Pending Orders</p><p class="mt-2 text-2xl font-bold text-[#301040]">' . $pendingOrders . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Delivered</p><p class="mt-2 text-2xl font-bold text-[#301040]">' . $deliveredOrders . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Sales</p><p class="mt-2 text-2xl font-bold text-[#301040]">₹' . number_format($totalSales, 0) . '</p></div>'
    . '</div>';

$content .= '<div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-sm"><div class="border-b border-gray-200 px-6 py-4"><h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-6 py-3">Order ID</th><th class="px-6 py-3">Customer</th><th class="px-6 py-3">Amount</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Date</th></tr></thead><tbody>';
foreach ($orders as $order) {
    $content .= '<tr class="border-t border-gray-200"><td class="px-6 py-4 font-medium text-[#301040]">' . htmlspecialchars($order['order_id']) . '</td><td class="px-6 py-4">' . htmlspecialchars($order['customer_name']) . '</td><td class="px-6 py-4">₹' . number_format((float) $order['total_amount'], 0) . '</td><td class="px-6 py-4">' . htmlspecialchars($order['status']) . '</td><td class="px-6 py-4">' . htmlspecialchars($order['created_at']) . '</td></tr>';
}
$content .= '</tbody></table></div></div>';

// Billing Section
$content .= '<div class="mt-8"><div class="mb-4"><h2 class="text-xl font-semibold text-gray-900 mb-4">Billing Overview</h2></div></div>'
    . '<div class="grid gap-4 md:grid-cols-4">'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Billings</p><p class="mt-2 text-2xl font-bold text-[#301040]">' . $totalBillings . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Amount</p><p class="mt-2 text-2xl font-bold text-[#301040]">₹' . number_format($totalBillingAmount, 0) . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Cash Payments</p><p class="mt-2 text-2xl font-bold text-green-600">₹' . number_format($totalBillingCash, 0) . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">GPay Payments</p><p class="mt-2 text-2xl font-bold text-blue-600">₹' . number_format($totalBillingGpay, 0) . '</p></div>'
    . '</div>';

$content .= '<div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-sm"><div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between"><h2 class="text-xl font-semibold text-gray-900">Recent Billing Records</h2><a href="billing.php" class="text-sm text-[#301040] hover:underline">View All →</a></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-6 py-3">Invoice ID</th><th class="px-6 py-3">Customer</th><th class="px-6 py-3">Total</th><th class="px-6 py-3">Cash</th><th class="px-6 py-3">GPay</th><th class="px-6 py-3">Balance</th><th class="px-6 py-3">Date</th></tr></thead><tbody>';
foreach ($billings as $bill) {
    $content .= '<tr class="border-t border-gray-200"><td class="px-6 py-4 font-medium text-[#301040]">' . htmlspecialchars($bill['invoice_id']) . '</td><td class="px-6 py-4">' . htmlspecialchars($bill['customer_name'] ?? 'N/A') . '</td><td class="px-6 py-4">₹' . number_format((float) $bill['total'], 0) . '</td><td class="px-6 py-4 text-green-600">₹' . number_format((float) $bill['cash'], 2) . '</td><td class="px-6 py-4 text-blue-600">₹' . number_format((float) $bill['gpay'], 2) . '</td><td class="px-6 py-4">' . (((float) $bill['balance'] > 0) ? '<span class="text-red-600">₹' . number_format((float) $bill['balance'], 2) . '</span>' : '<span class="text-green-600">Paid</span>') . '</td><td class="px-6 py-4">' . htmlspecialchars($bill['bill_date'] ?? 'N/A') . '</td></tr>';
}
$content .= '</tbody></table></div></div>';

include __DIR__ . '/layout.php';
?>