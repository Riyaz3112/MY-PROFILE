<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo = getDbConnection();
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'")->fetch();
    if ($cols) {
        echo "password_hash exists\n";
        exit;
    }
    $pdo->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER email");
    echo "password_hash added\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
