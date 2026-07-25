<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDbConnection();

// Ensure we have a pending MFA session
if (empty($_SESSION['mfa_pending'])) {
    header('Location: login.php');
    exit;
}

$adminId = (int) $_SESSION['mfa_pending'];
$stmt = $pdo->prepare('SELECT id, username, mfa_secret FROM admin WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();
if (!$admin) { header('Location: login.php'); exit; }

$error = '';
// Enrollment: if no secret, create one and show QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $code = sanitizeInput($_POST['code'] ?? '');
    if (empty($admin['mfa_secret'])) {
        $error = 'MFA not configured for this account.';
    } else {
        if (verify_totp($admin['mfa_secret'], $code, 1)) {
            // success -> complete login
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            // remove pending markers
            unset($_SESSION['mfa_pending'], $_SESSION['mfa_username'], $_SESSION['mfa_created']);
            resetFailedLogin($_SERVER['REMOTE_ADDR'] ?? '');
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid code. Please try again.';
        }
    }
}

// If admin requests setup and no secret exists, create and save one
if (isset($_GET['setup']) && empty($admin['mfa_secret'])) {
    $secret = create_mfa_secret_for_admin($pdo, $admin['id']);
    $admin['mfa_secret'] = $secret;
}

include __DIR__ . '/layout.php';

?>
<div class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-bold text-[#301040] mb-4">Multi-Factor Authentication</h1>
    <?php if ($error): ?><div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <?php if (empty($admin['mfa_secret'])): ?>
        <div class="mb-4 rounded border border-gray-200 bg-white p-4">
            <p class="mb-2">MFA is not configured for your account. Click below to generate a secret and enroll using an authenticator app (Google Authenticator, Authy).</p>
            <a href="?setup=1" class="inline-block rounded-full bg-[#301040] px-4 py-2 text-white">Generate MFA Secret</a>
        </div>
    <?php else: ?>
        <?php
            $otpauth = 'otpauth://totp/' . rawurlencode('LookStylo:' . ($admin['username'] ?? 'admin')) . '?secret=' . rawurlencode($admin['mfa_secret']) . '&issuer=' . rawurlencode('LookStylo');
            $qr = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . rawurlencode($otpauth);
        ?>
        <div class="mb-4 rounded border border-gray-200 bg-white p-4">
            <p class="mb-2">Scan the QR code below with your authenticator app and then enter the 6-digit code.</p>
            <img src="<?php echo $qr; ?>" alt="MFA QR" class="mb-3" />
            <p class="text-xs text-gray-600">Or enter secret manually: <strong><?php echo htmlspecialchars($admin['mfa_secret']); ?></strong></p>
        </div>

        <form method="post" class="space-y-3 rounded bg-white border p-4">
            <input type="hidden" name="action" value="verify">
            <label class="block">
                <span class="text-sm font-medium text-gray-700">Authenticator Code</span>
                <input type="text" name="code" required class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" maxlength="8">
            </label>
            <button type="submit" class="rounded-full bg-[#301040] px-4 py-2 text-white">Verify & Sign In</button>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
