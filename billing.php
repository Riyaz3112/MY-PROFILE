<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$pdo = getDbConnection();
$pageTitle = 'Billing Records';

// Initialize variables
$billings = [];
$totalBillings = 0;
$totalAmount = 0;
$totalCash = 0;
$totalGpay = 0;

try {
    // Fetch all billing records
    $billings = $pdo->query('SELECT * FROM billing_records ORDER BY bill_date DESC LIMIT 100')->fetchAll();

    // Calculate totals
    $totalBillings = count($billings);
    $totalAmount = (float) $pdo->query('SELECT COALESCE(SUM(total), 0) FROM billing_records')->fetchColumn();
    $totalCash = (float) $pdo->query('SELECT COALESCE(SUM(cash), 0) FROM billing_records')->fetchColumn();
    $totalGpay = (float) $pdo->query('SELECT COALESCE(SUM(gpay), 0) FROM billing_records')->fetchColumn();
} catch (PDOException $e) {
    // Handle table not found error
    $billings = [];
    $totalBillings = 0;
    $totalAmount = 0;
    $totalCash = 0;
    $totalGpay = 0;
}

$content = '<div class="grid gap-4 md:grid-cols-4">'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Billings</p><p class="mt-2 text-2xl font-bold text-[#301040]">' . $totalBillings . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Amount</p><p class="mt-2 text-2xl font-bold text-[#301040]">₹' . number_format($totalAmount, 0) . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Cash Payments</p><p class="mt-2 text-2xl font-bold text-[#301040]">₹' . number_format($totalCash, 0) . '</p></div>'
    . '<div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">GPay Payments</p><p class="mt-2 text-2xl font-bold text-[#301040]">₹' . number_format($totalGpay, 0) . '</p></div>'
    . '</div>';

$content .= '<div class="mt-8 overflow-hidden rounded-3xl bg-white shadow-sm">'
    . '<div class="border-b border-gray-200 px-6 py-4">'
    . '<h2 class="text-xl font-semibold text-gray-900">Billing Records</h2>'
    . '</div>'
    . '<div class="overflow-x-auto">'
    . '<table class="min-w-full text-sm">'
    . '<thead class="bg-gray-50 text-left text-gray-600">'
    . '<tr>'
    . '<th class="px-6 py-3">Invoice ID</th>'
    . '<th class="px-6 py-3">Customer</th>'
    . '<th class="px-6 py-3">Phone</th>'
    . '<th class="px-6 py-3">Total</th>'
    . '<th class="px-6 py-3">Cash</th>'
    . '<th class="px-6 py-3">GPay</th>'
    . '<th class="px-6 py-3">Balance</th>'
    . '<th class="px-6 py-3">Date</th>'
    . '<th class="px-6 py-3">Bill Type</th>'
    . '</tr>'
    . '</thead>'
    . '<tbody>';

foreach ($billings as $bill) {
    $content .= '<tr class="border-t border-gray-200 hover:bg-gray-50">'
        . '<td class="px-6 py-4 font-medium text-[#301040]">' . htmlspecialchars($bill['invoice_id']) . '</td>'
        . '<td class="px-6 py-4">' . htmlspecialchars($bill['customer_name'] ?? 'N/A') . '</td>'
        . '<td class="px-6 py-4">' . htmlspecialchars($bill['customer_phone'] ?? 'N/A') . '</td>'
        . '<td class="px-6 py-4 font-semibold">₹' . number_format((float) $bill['total'], 0) . '</td>'
        . '<td class="px-6 py-4 text-green-600">₹' . number_format((float) $bill['cash'], 2) . '</td>'
        . '<td class="px-6 py-4 text-blue-600">₹' . number_format((float) $bill['gpay'], 2) . '</td>'
        . '<td class="px-6 py-4">' . (((float) $bill['balance'] > 0) ? '<span class="text-red-600">₹' . number_format((float) $bill['balance'], 2) . '</span>' : '<span class="text-green-600">Paid</span>') . '</td>'
        . '<td class="px-6 py-4">' . htmlspecialchars($bill['bill_date'] ?? 'N/A') . '</td>'
        . '<td class="px-6 py-4"><span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">' . htmlspecialchars($bill['bill_type'] ?? 'N/A') . '</span></td>'
        . '</tr>';
}

$content .= '</tbody>'
    . '</table>'
    . '</div>'
    . '</div>';

include __DIR__ . '/layout.php';
?>
