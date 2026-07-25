<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, order_id, customer_name, mobile, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "NO_ORDERS\n";
        exit(0);
    }
    foreach ($rows as $r) {
        echo implode(' | ', [$r['id'],$r['order_id'],$r['customer_name'],$r['mobile'],$r['total_amount'],$r['status'],$r['created_at']]) . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
