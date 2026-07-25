<?php
require_once __DIR__ . '/includes/functions.php';
try {
    $pdo = getDbConnection();
    // pick latest order
    $row = $pdo->query('SELECT id, order_id, customer_name, mobile, email, total_amount FROM orders ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "NO_ORDER\n"; exit; }
    sendOrderNotifications($pdo, (int)$row['id'], $row['order_id'], $row['customer_name'], $row['mobile'], $row['email'], (float)$row['total_amount']);
    echo "NOTIFICATIONS_ATTEMPTED\n";
} catch (Throwable $e) { echo 'ERROR: ' . $e->getMessage() . "\n"; }
