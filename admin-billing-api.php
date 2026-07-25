<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';

// Admin check
if (empty($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pdo = getDbConnection();
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $limit = (int) ($_GET['limit'] ?? 100);
    $offset = (int) ($_GET['offset'] ?? 0);
    
    $billings = $pdo->query(
        "SELECT * FROM billing_records ORDER BY bill_date DESC LIMIT $limit OFFSET $offset"
    )->fetchAll();
    
    $total = (int) $pdo->query('SELECT COUNT(*) FROM billing_records')->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'billings' => $billings,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
} elseif ($action === 'stats') {
    $stats = [
        'total_billings' => (int) $pdo->query('SELECT COUNT(*) FROM billing_records')->fetchColumn(),
        'total_amount' => (float) $pdo->query('SELECT COALESCE(SUM(total), 0) FROM billing_records')->fetchColumn(),
        'total_cash' => (float) $pdo->query('SELECT COALESCE(SUM(cash), 0) FROM billing_records')->fetchColumn(),
        'total_gpay' => (float) $pdo->query('SELECT COALESCE(SUM(gpay), 0) FROM billing_records')->fetchColumn(),
        'total_advance' => (float) $pdo->query('SELECT COALESCE(SUM(advance), 0) FROM billing_records')->fetchColumn(),
        'total_balance' => (float) $pdo->query('SELECT COALESCE(SUM(balance), 0) FROM billing_records')->fetchColumn()
    ];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
} elseif ($action === 'detail' && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];
    $billing = $pdo->prepare('SELECT * FROM billing_records WHERE id = ?')->execute([$id])->fetch();
    
    if ($billing) {
        echo json_encode(['success' => true, 'billing' => $billing]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Billing record not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
