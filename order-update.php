<?php
require_once __DIR__ . '/../includes/functions.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = getDbConnection();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: orders.php'); exit; }
if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) { die('Invalid CSRF token'); }
$orderCode = sanitizeInput($_POST['order_id'] ?? '');
$status = sanitizeInput($_POST['status'] ?? '');
$paymentStatus = sanitizeInput($_POST['payment_status'] ?? '');

if ($orderCode) {
    $stmt = $pdo->prepare('SELECT id, status FROM orders WHERE order_id = ? LIMIT 1');
    $stmt->execute([$orderCode]);
    $ord = $stmt->fetch();

    if ($ord) {
        $orderId = (int)$ord['id'];

        if ($status !== '' && $status !== $ord['status']) {
            $upd = $pdo->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
            $upd->execute([$status, $orderCode]);

            $t = $pdo->prepare('INSERT INTO order_tracking (order_id, status, note) VALUES (?, ?, ?)');
            $t->execute([$orderId, $status, 'Order status changed to ' . $status . '.']);
        }

        if ($paymentStatus !== ''){
            $p = $pdo->prepare('UPDATE payments SET status = ? WHERE order_id = ?');
            $p->execute([$paymentStatus, $orderId]);

            $trackingStatus = 'Payment ' . $paymentStatus;
            $trackingNote = 'Payment status updated to ' . $paymentStatus . '.';
            $t = $pdo->prepare('INSERT INTO order_tracking (order_id, status, note) VALUES (?, ?, ?)');
            $t->execute([$orderId, $trackingStatus, $trackingNote]);
        }
    }
}
header('Location: order-view.php?order=' . urlencode($orderCode)); exit;
