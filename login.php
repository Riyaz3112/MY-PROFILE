<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDbConnection();
$pageTitle = 'Customer Login - LookStylo Clothing';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = sanitizeInput($_POST['identifier'] ?? ''); // mobile or email
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $errors[] = 'Please enter mobile/email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash, full_name FROM users WHERE mobile = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            $_SESSION['customer_id'] = (int)$user['id'];
            $_SESSION['customer_name'] = $user['full_name'] ?? '';
            header('Location: index.html');
            exit;
        }

        $errors[] = 'Invalid credentials. If you placed an order using the checkout form, set a password from the account recovery flow.';
        recordFailedLogin($_SERVER['REMOTE_ADDR'] ?? '');
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-[#301040] mb-4">Customer Sign In</h1>
    <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6">
        <label class="block">
            <span class="text-sm font-medium text-gray-700">Mobile number or Email</span>
            <input type="text" name="identifier" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" required>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Password</span>
            <input type="password" name="password" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" required>
        </label>

        <div>
            <button type="submit" class="w-full rounded-full bg-[#301040] px-4 py-3 font-semibold text-white">Sign In</button>
        </div>

        <p class="text-sm text-gray-600">Don't have a password? Place an order via checkout to create an account, then use the password reset option (not implemented) or ask me to set a test password.</p>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
