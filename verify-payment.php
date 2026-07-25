<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: orders.php'); exit; }
if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) { die('Invalid CSRF token'); }
$orderCode = sanitizeInput($_POST['order_id'] ?? '');
if ($orderCode) {
    // find internal order id
    $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_id = ?');
    $stmt->execute([$orderCode]);
    $ord = $stmt->fetch();
    if ($ord) {
        $orderId = (int)$ord['id'];
        // mark payments row as Verified
        $p = $pdo->prepare('UPDATE payments SET status = ? WHERE order_id = ?');
        $p->execute(['Verified', $orderId]);
        // update order status to Processing
        $o = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $o->execute(['Processing', $orderId]);
        // add tracking
        $t = $pdo->prepare('INSERT INTO order_tracking (order_id, status, note) VALUES (?, ?, ?)');
        $t->execute([$orderId, 'Payment Verified', 'Payment verified by admin.']);
    }
}
header('Location: order-view.php?order=' . urlencode($orderCode)); exit;
