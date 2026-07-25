<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDbConnection();
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $ex->getMessage()]);
    exit;
}

if ($method === 'GET') {
    $invoiceId = trim((string)($_GET['invoice_id'] ?? ''));
    if ($invoiceId !== '') {
        $stmt = $pdo->prepare('SELECT * FROM billing_records WHERE invoice_id = ? LIMIT 1');
        $stmt->execute([$invoiceId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            $record['items'] = json_decode($record['items'] ?? '[]', true);
            echo json_encode(['success' => true, 'record' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        }
        exit;
    }

    $stmt = $pdo->query('SELECT * FROM billing_records ORDER BY bill_date DESC, id DESC');
    $records = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['items'] = json_decode($row['items'] ?? '[]', true);
        $records[] = $row;
    }
    echo json_encode(['success' => true, 'records' => $records]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$invoiceId = trim((string)($data['invoice_id'] ?? $data['id'] ?? ''));
if ($invoiceId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing invoice_id']);
    exit;
}

$billDate = $data['bill_date'] ?? $data['date'] ?? null;
if ($billDate !== null && $billDate !== '') {
    $billDate = trim((string)$billDate);
} else {
    $billDate = null;
}
$billType = trim((string)($data['bill_type'] ?? 'Non-GST'));
$customerName = trim((string)($data['customer_name'] ?? $data['customer'] ?? ''));
$customerPhone = trim((string)($data['customer_phone'] ?? $data['phone'] ?? ''));
$customerAddress = trim((string)($data['customer_address'] ?? $data['address'] ?? ''));
$deliveryMode = trim((string)($data['delivery_mode'] ?? $data['delivery'] ?? ''));
$courier = trim((string)($data['courier'] ?? ''));
$tracking = trim((string)($data['tracking'] ?? ''));
$discount = is_numeric($data['discount'] ?? null) ? (float)$data['discount'] : 0.0;
$promoCode = trim((string)($data['promo_code'] ?? $data['promoCode'] ?? ''));
$items = $data['items'] ?? [];
$total = is_numeric($data['total'] ?? null) ? (float)$data['total'] : 0.0;
$cash = is_numeric($data['cash'] ?? null) ? (float)$data['cash'] : 0.0;
$gpay = is_numeric($data['gpay'] ?? null) ? (float)$data['gpay'] : 0.0;
$advance = is_numeric($data['advance'] ?? null) ? (float)$data['advance'] : 0.0;
$balance = is_numeric($data['balance'] ?? null) ? (float)$data['balance'] : 0.0;
$description = trim((string)($data['description'] ?? ''));

$itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
if ($itemsJson === false) {
    $itemsJson = json_encode([]);
}

try {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDbConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO billing_records
            (invoice_id, bill_date, bill_type, customer_name, customer_phone, customer_address,
             delivery_mode, courier, tracking, discount, promo_code, items, total, cash, gpay, advance, balance, description)
         VALUES
            (:invoice_id, :bill_date, :bill_type, :customer_name, :customer_phone, :customer_address,
             :delivery_mode, :courier, :tracking, :discount, :promo_code, :items, :total, :cash, :gpay, :advance, :balance, :description)
         ON DUPLICATE KEY UPDATE
            bill_date = VALUES(bill_date),
            bill_type = VALUES(bill_type),
            customer_name = VALUES(customer_name),
            customer_phone = VALUES(customer_phone),
            customer_address = VALUES(customer_address),
            delivery_mode = VALUES(delivery_mode),
            courier = VALUES(courier),
            tracking = VALUES(tracking),
            discount = VALUES(discount),
            promo_code = VALUES(promo_code),
            items = VALUES(items),
            total = VALUES(total),
            cash = VALUES(cash),
            gpay = VALUES(gpay),
            advance = VALUES(advance),
            balance = VALUES(balance),
            description = VALUES(description),
            updated_at = CURRENT_TIMESTAMP'
    );

    $stmt->execute([
        ':invoice_id' => $invoiceId,
        ':bill_date' => $billDate,
        ':bill_type' => $billType,
        ':customer_name' => $customerName,
        ':customer_phone' => $customerPhone,
        ':customer_address' => $customerAddress,
        ':delivery_mode' => $deliveryMode,
        ':courier' => $courier,
        ':tracking' => $tracking,
        ':discount' => $discount,
        ':promo_code' => $promoCode,
        ':items' => $itemsJson,
        ':total' => $total,
        ':cash' => $cash,
        ':gpay' => $gpay,
        ':advance' => $advance,
        ':balance' => $balance,
        ':description' => $description,
    ]);

    echo json_encode(['success' => true, 'invoice_id' => $invoiceId]);
    exit;
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error saving billing record', 'error' => $ex->getMessage()]);
    exit;
}
