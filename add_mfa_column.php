<?php
require_once __DIR__ . '/../config/db.php';
try {
    $pdo = getDbConnection();
    $col = $pdo->query("SHOW COLUMNS FROM admin LIKE 'mfa_secret'")->fetch();
    if ($col) {
        echo "mfa_secret exists\n";
        exit;
    }
    $pdo->exec("ALTER TABLE admin ADD COLUMN mfa_secret VARCHAR(255) DEFAULT NULL AFTER password");
    echo "mfa_secret added\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
