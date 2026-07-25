<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

// Restrict this helper to local requests only to avoid exposure.
$remote = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
if (!in_array($remote, ['127.0.0.1', '::1']) && strpos($remote, '192.168.') !== 0 && strpos($remote, '10.') !== 0) {
    http_response_code(403);
    echo "This utility can only be run from the local network. Your IP: " . htmlspecialchars($remote);
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $msg = 'Username and password are required.';
    } else {
        // avoid duplicate
        $stmt = $pdo->prepare('SELECT id FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $msg = 'An admin with that username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO admin (username, password) VALUES (?, ?)');
            $ins->execute([$username, $hash]);
            $msg = 'Admin created successfully. You can now log in at admin/login.php';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-xl font-bold">Create Admin Account (local only)</h1>
    <?php if ($msg): ?><p class="mt-3 p-3 rounded bg-gray-50 border"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
    <form method="post" class="mt-4 space-y-3">
      <div>
        <label class="block text-sm font-medium">Username</label>
        <input name="username" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm font-medium">Password</label>
        <input name="password" type="password" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <button class="px-4 py-2 bg-[#301040] text-white rounded">Create Admin</button>
      </div>
    </form>
    <p class="mt-4 text-xs text-gray-600">After creating the account, remove this file for security: <strong>admin/create_admin.php</strong></p>
  </div>
</body>
</html>
