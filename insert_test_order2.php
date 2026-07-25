<?php
require_once __DIR__ . '/includes/functions.php';
try {
    $pdo = getDbConnection();
    // create or find test user
    $mobile = '9999999999';
    $email = 'test@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE mobile = ? OR email = ? LIMIT 1');
    $stmt->execute([$mobile, $email]);
    $u = $stmt->fetch();
    if ($u) {
        $userId = (int)$u['id'];
    } else {
        $pdo->prepare('INSERT INTO users (full_name,mobile,email,address,city,state,pincode) VALUES(?,?,?,?,?,?,?)')
            ->execute(['Test User', $mobile, $email, 'Test address', 'City', 'State', '000000']);
        $userId = (int)$pdo->lastInsertId();
    }
    $pdo->beginTransaction();
    $orderId = createOrderId($pdo);
    $pdo->prepare('INSERT INTO orders (order_id,user_id,customer_name,mobile,email,address,city,state,pincode,order_notes,total_amount,utr_number,payment_screenshot,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$orderId, $userId, 'Test User', $mobile, $email, 'Test address', 'City', 'State', '000000', 'Test order note', 123.45, 'UTRTEST123', null, 'Payment Verification Pending']);
    $orderIdInt = (int)$pdo->lastInsertId();
    // use existing product id
    $productId = 8;
    $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,size,color,quantity,price) VALUES (?,?,?,?,?,?,?)')
        ->execute([$orderIdInt, $productId, 'Sample Product', 'M', 'Black', 1, 123.45]);
    $pdo->prepare('INSERT INTO payments (order_id,amount,utr_number,screenshot,status) VALUES (?,?,?,NULL,?)')
        ->execute([$orderIdInt, 123.45, 'UTRTEST123', 'Pending Verification']);
    $pdo->prepare('INSERT INTO order_tracking (order_id,status,note) VALUES (?,?,?)')
        ->execute([$orderIdInt, 'Payment Verification Pending', 'Inserted test order']);
    $pdo->commit();
    echo "INSERTED: $orderId\n";
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
