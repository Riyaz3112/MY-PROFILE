<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();
$orderCode = $_GET['order'] ?? '';
if (!$orderCode) { header('Location: orders.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ?');
$stmt->execute([$orderCode]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }
$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

$html = '<!doctype html><html><head><meta charset="utf-8"><title>Invoice '.$orderCode.'</title><style>body{font-family:Arial,Helvetica,sans-serif}table{width:100%;border-collapse:collapse}td,th{padding:8px;border:1px solid #ddd}</style></head><body>';
$html .= '<h2>LookStylo Clothing - Invoice</h2>';
$html .= '<p><strong>Order ID:</strong> '.htmlspecialchars($order['order_id']).'<br>';
$html .= '<strong>Date:</strong> '.htmlspecialchars($order['created_at']).'</p>';
$html .= '<h3>Customer</h3><p>'.htmlspecialchars($order['customer_name']).'<br>'.htmlspecialchars($order['mobile']).'<br>'.nl2br(htmlspecialchars($order['address'])).'</p>';
$html .= '<h3>Items</h3><table><thead><tr><th>Item</th><th>Size</th><th>Color</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>';
$total=0;
foreach($items as $it){
  $line = (float)$it['price'] * (int)$it['quantity']; $total += $line;
  $html .= '<tr><td>'.htmlspecialchars($it['product_name']).'</td><td>'.htmlspecialchars($it['size']).'</td><td>'.htmlspecialchars($it['color']).'</td><td>'.(int)$it['quantity'].'</td><td>₹'.number_format((float)$it['price'],0).'</td><td>₹'.number_format($line,0).'</td></tr>';
}
$html .= '</tbody></table>';
$html .= '<h3 style="text-align:right">Total: ₹'.number_format($total,0).'</h3>';
$html .= '<p>Payment UTR: '.htmlspecialchars($order['utr_number']).'</p>';
$html .= '</body></html>';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="invoice-'.preg_replace('/[^A-Za-z0-9_-]/','',$orderCode).'.html"');
echo $html; exit;
