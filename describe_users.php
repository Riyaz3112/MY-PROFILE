<?php
require_once __DIR__ . '/config/db.php';
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo $r['Field'] . ' | ' . $r['Type'] . ' | ' . $r['Null'] . ' | ' . ($r['Key'] ?? '') . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
