<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$pageTitle = 'Checkout | LookStylo Clothing';

$items = getCartItems($pdo);
if (empty($items)) {
    header('Location: shop.php');
    exit;
}

$total = getCartTotal($items);
$upiLink = buildUpiQrData($total, 'Order payment');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        die('Invalid CSRF token.');
    }

    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $pincode = sanitizeInput($_POST['pincode'] ?? '');
    $orderNotes = sanitizeInput($_POST['order_notes'] ?? '');
    $utr = sanitizeInput($_POST['utr_number'] ?? '');

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($mobile === '') $errors[] = 'Mobile number is required.';
    if ($email === '') $errors[] = 'Email address is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($state === '') $errors[] = 'State is required.';
    if ($pincode === '') $errors[] = 'Pincode is required.';
    if ($utr === '') $errors[] = 'UTR / Transaction ID is required.';

    $paymentFileName = null;
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['payment_screenshot'];
        if (!isValidImageUpload($file, 5 * 1024 * 1024)) {
            $errors[] = 'Only JPG, PNG, or WEBP files are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Payment screenshot must be 5 MB or less.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = 'payment_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
            $target = __DIR__ . '/uploads/payments/' . $safeName;
            if (!move_uploaded_file($file['tmp_name'], $target)) {
                $errors[] = 'Unable to upload payment screenshot.';
            } else {
                $paymentFileName = $safeName;
            }
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $userStmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE mobile = ? OR email = ? LIMIT 1');
            $userStmt->execute([$mobile, $email]);
            $existingUser = $userStmt->fetch();

            if ($existingUser) {
                $userId = (int) $existingUser['id'];
                if (empty($existingUser['password_hash']) || !password_verify($password, $existingUser['password_hash'])) {
                    $updateUser = $pdo->prepare('UPDATE users SET password_hash = ?, full_name = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?');
                    $updateUser->execute([$passwordHash, $fullName, $address, $city, $state, $pincode, $userId]);
                }
            } else {
                $insertUser = $pdo->prepare('INSERT INTO users (full_name, mobile, email, password_hash, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $insertUser->execute([$fullName, $mobile, $email, $passwordHash, $address, $city, $state, $pincode]);
                $userId = (int) $pdo->lastInsertId();
            }

            $_SESSION['customer_id'] = $userId;
            $orderId = createOrderId($pdo);
            $orderStmt = $pdo->prepare('INSERT INTO orders (order_id, user_id, customer_name, mobile, email, address, city, state, pincode, order_notes, total_amount, utr_number, payment_screenshot, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $orderStmt->execute([$orderId, $userId, $fullName, $mobile, $email, $address, $city, $state, $pincode, $orderNotes, $total, $utr, $paymentFileName, 'Payment Verification Pending']);
            $orderIdInt = (int) $pdo->lastInsertId();

            foreach ($items as $item) {
                $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, size, color, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $itemStmt->execute([$orderIdInt, $item['product_id'], $item['name'], $item['size'], $item['color'], $item['quantity'], $item['price']]);
            }

            $paymentStmt = $pdo->prepare('INSERT INTO payments (order_id, amount, utr_number, screenshot, status) VALUES (?, ?, ?, ?, ?)');
            $paymentStmt->execute([$orderIdInt, $total, $utr, $paymentFileName, 'Pending Verification']);

            $trackingStmt = $pdo->prepare('INSERT INTO order_tracking (order_id, status, note) VALUES (?, ?, ?)');
            $trackingStmt->execute([$orderIdInt, 'Payment Verification Pending', 'Order placed and payment under verification.']);

            clearCart($pdo);
            $pdo->commit();
            
            // Send notifications to owner and customer
            sendOrderNotifications($pdo, $orderIdInt, $orderId, $fullName, $mobile, $email, $total);
            
            header('Location: order-success.php?order=' . urlencode($orderId));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Unable to place order right now. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-purple-700">Secure Checkout</p>
        <h1 class="mt-2 text-3xl font-bold text-[#301040]">Checkout & UPI Payment</h1>
    </div>
    <?php if (!empty($errors)): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
        <form method="post" enctype="multipart/form-data" class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="tel" name="mobile" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" minlength="6" required class="w-full rounded-xl border border-gray-300 px-4 py-3" placeholder="Create a secure password">
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Full Address</label>
                <textarea name="address" required class="min-h-[100px] w-full rounded-xl border border-gray-300 px-4 py-3"></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">State</label>
                    <input type="text" name="state" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Pincode</label>
                    <input type="text" name="pincode" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Order Notes</label>
                <textarea name="order_notes" class="min-h-[80px] w-full rounded-xl border border-gray-300 px-4 py-3"></textarea>
            </div>
            <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5">
                <h3 class="text-lg font-semibold text-[#301040]">Free UPI Payment</h3>
                <p class="mt-2 text-sm text-gray-600">Scan the QR Code or use the UPI ID below to complete your payment. Supported by Google Pay, PhonePe, Paytm, BHIM, Amazon Pay UPI, and all UPI apps.</p>
                <div class="mt-4 flex flex-col items-start gap-4 md:flex-row md:items-center">
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                        <img id="upiQr" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($upiLink); ?>" alt="UPI QR Code" class="h-40 w-40">
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700">UPI ID</p>
                        <div class="mt-2 flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                            <a id="upiLink" href="upi://pay?pa=9360232991@okbizaxis&am=<?php echo number_format($total,2,'.',''); ?>" class="underline">9360232991@okbizaxis</a>
                            <button type="button" class="rounded-full bg-[#301040] px-3 py-1 text-xs font-semibold text-white" data-copy="9360232991@okbizaxis" data-target="copyMessage">Copy</button>
                        </div>
                        <p id="copyMessage" class="mt-2 text-sm text-green-600"></p>
                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">UTR / Transaction ID</label>
                            <input id="utrInput" type="text" name="utr_number" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        </div>
                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Payment Screenshot (Optional)</label>
                            <input type="file" name="payment_screenshot" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full rounded-full bg-[#301040] px-4 py-3 font-semibold text-white hover:bg-purple-900">Place Order</button>
        </form>
        <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
            <h2 class="text-xl font-semibold text-gray-900">Order Summary</h2>
            <div class="mt-6 space-y-3">
                <?php foreach ($items as $item): ?>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo (int) $item['quantity']; ?></span>
                        <span>₹<?php echo number_format((float) $item['price'] * (int) $item['quantity'], 0); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 border-t border-gray-200 pt-4 text-sm text-gray-600">
                <div class="flex justify-between"><span>Subtotal</span><span>₹<?php echo number_format($total, 0); ?></span></div>
                <div class="flex justify-between"><span>Shipping</span><span>Free</span></div>
                <div class="mt-3 flex justify-between border-t border-gray-200 pt-3 text-base font-semibold text-gray-900"><span>Total Amount</span><span>₹<?php echo number_format($total, 0); ?></span></div>
            </div>
        </aside>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>