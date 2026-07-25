<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
$pageTitle = 'Orders';

$stmt = $pdo->query("SELECT o.*, p.status AS payment_status FROM orders o LEFT JOIN payments p ON p.order_id = o.id GROUP BY o.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

$content = '<div class="max-w-7xl mx-auto p-6">';
$content .= '<div class="flex items-center justify-between mb-4"><h1 class="text-2xl font-bold">Orders</h1></div>';
$content .= '<div class="overflow-x-auto bg-white rounded-lg shadow"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-4 py-3">Order ID</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Actions</th></tr></thead><tbody>';
foreach ($orders as $o){
    $content .= '<tr class="border-t"><td class="px-4 py-3 font-medium text-[#301040]">' . htmlspecialchars($o['order_id']) . '</td>';
    $content .= '<td class="px-4 py-3">' . htmlspecialchars($o['customer_name']) . '<br><span class="text-xs text-gray-500">' . htmlspecialchars($o['mobile']) . '</span></td>';
    $content .= '<td class="px-4 py-3">₹' . number_format((float)$o['total_amount'],0) . '</td>';
    $content .= '<td class="px-4 py-3">' . htmlspecialchars($o['payment_status'] ?? 'N/A') . '</td>';
    $content .= '<td class="px-4 py-3">' . htmlspecialchars($o['status']) . '</td>';
    $content .= '<td class="px-4 py-3">' . htmlspecialchars($o['created_at']) . '</td>';
    $content .= '<td class="px-4 py-3"><a href="order-view.php?order=' . urlencode($o['order_id']) . '" class="text-indigo-600 mr-3">View</a><a href="invoice.php?order=' . urlencode($o['order_id']) . '" class="text-green-600 mr-3">Invoice</a></td></tr>';
}
$content .= '</tbody></table></div></div>';

include __DIR__ . '/layout.php';
