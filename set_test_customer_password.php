<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo = getDbConnection();
    $mobile = '9999999999';
    $newPass = 'testpass123';
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE mobile = ?');
    $stmt->execute([$hash, $mobile]);
    if ($stmt->rowCount() > 0) {
        echo "PASSWORD_SET\n";
    } else {
        echo "NO_USER_FOUND\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
