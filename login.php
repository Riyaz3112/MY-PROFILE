<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Admin Login';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!isLoginAllowed($ip)) {
        $error = 'Too many failed login attempts. Try again later.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        $passwordMatches = false;

        // Recover the admin account if the expected seeded user does not exist yet.
        $defaultAccounts = [
            'Shariff' => 'Shariff@123',
            'admin' => '123',
        ];

        if (!$admin && isset($defaultAccounts[$username]) && $password === $defaultAccounts[$username]) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO admin (username, password) VALUES (?, ?)');
            $insert->execute([$username, $hash]);
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
        }

        if ($admin) {
            $storedPassword = $admin['password'];
            if (preg_match('/^\$2[ayb]\$/', $storedPassword)) {
                $passwordMatches = password_verify($password, $storedPassword);
                if ($passwordMatches && password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                    $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
                    $update->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
                }
            } else {
                $passwordMatches = hash_equals($storedPassword, $password);
                if ($passwordMatches) {
                    $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id = ?');
                    $update->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
                }
            }
        }

        if ($passwordMatches) {
            // If admin has MFA enabled, don't finalise login yet — set pending and redirect to MFA page
            if (!empty($admin['mfa_secret'])) {
                session_regenerate_id(true);
                $_SESSION['mfa_pending'] = $admin['id'];
                $_SESSION['mfa_username'] = $admin['username'] ?? $username;
                $_SESSION['mfa_created'] = time();
                header('Location: mfa.php');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'] ?? $username;
            // session security markers
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['fingerprint'] = hash('sha256', ($_SESSION['ip'] ?? '') . '|' . ($_SESSION['ua'] ?? ''));

            resetFailedLogin($ip);
            header('Location: dashboard.php');
            exit;
        }

        recordFailedLogin($ip);
        $error = 'Invalid admin credentials.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#301040] min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-soft">
        <h1 class="text-2xl font-bold text-[#301040]">Admin Login</h1>
        <p class="mt-2 text-sm text-gray-600">Secure access to the LookStylo admin dashboard.</p>
        <?php if (!empty($error)): ?><p class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form method="post" class="mt-6 space-y-4">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-gray-300 px-4 py-3">
            </div>
            <button class="w-full rounded-full bg-[#301040] px-4 py-3 font-semibold text-white">Login</button>
        </form>
    </div>
</body>
</html>