<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Only allow testing if logged in as admin or from localhost
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);
$isAdmin = isset($_SESSION['admin_id']);

if (!$isLocalhost && !$isAdmin) {
    die('Access denied. WhatsApp setup is only available locally or for admins.');
}

loadEnvConfig();

$accessToken = getenv('META_ACCESS_TOKEN');
$phoneNumberId = getenv('META_PHONE_NUMBER_ID');
$apiVersion = getenv('META_WHATSAPP_API_VERSION') ?: 'v18.0';
$businessAccountId = getenv('META_BUSINESS_ACCOUNT_ID');

$testResult = null;
$testMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'test_connection') {
        $testResult = testWhatsAppConnection();
        $testMessage = $testResult ? 
            '✅ WhatsApp API connection successful!' : 
            '❌ Connection failed. Check credentials in .env file.';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'send_test') {
        $testPhone = $_POST['test_phone'] ?? '';
        if (!empty($testPhone)) {
            $testMsg = "🧪 Test message from LookStylo!\n\nThis is a test notification.\nTimestamp: " . date('Y-m-d H:i:s');
            $result = sendWhatsAppMessage($testPhone, $testMsg);
            $testMessage = $result ? 
                '✅ Test message sent! Check your WhatsApp.' : 
                '❌ Failed to send test message. Check logs.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Setup & Test</title>
    <script defer src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-badge { @apply inline-block px-3 py-1 rounded-full text-sm font-bold; }
        .status-ok { @apply bg-green-100 text-green-800; }
        .status-error { @apply bg-red-100 text-red-800; }
        .status-warning { @apply bg-yellow-100 text-yellow-800; }
    </style>
</head>
<body class="bg-gray-50 py-10">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">WhatsApp Setup & Testing</h1>
            <p class="text-gray-600 mb-8">Configure and test Meta WhatsApp Cloud API integration</p>

            <?php if ($testMessage): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo strpos($testMessage, '✅') === 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
                    <p class="<?php echo strpos($testMessage, '✅') === 0 ? 'text-green-800' : 'text-red-800'; ?> font-medium">
                        <?php echo htmlspecialchars($testMessage); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Configuration Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="p-4 border rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Access Token</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded flex-1 truncate">
                            <?php echo $accessToken ? substr($accessToken, 0, 20) . '...' : 'NOT SET'; ?>
                        </code>
                        <span class="status-badge <?php echo $accessToken ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $accessToken ? '✓' : '✗'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 border rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Phone Number ID</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded flex-1 truncate">
                            <?php echo $phoneNumberId ?: 'NOT SET'; ?>
                        </code>
                        <span class="status-badge <?php echo $phoneNumberId ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $phoneNumberId ? '✓' : '✗'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 border rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Business Account ID</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded flex-1 truncate">
                            <?php echo $businessAccountId ?: 'NOT SET'; ?>
                        </code>
                        <span class="status-badge <?php echo $businessAccountId ? 'status-ok' : 'status-warning'; ?>">
                            <?php echo $businessAccountId ? '✓' : '?'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 border rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">API Version</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            <?php echo htmlspecialchars($apiVersion); ?>
                        </code>
                        <span class="status-badge status-ok">✓</span>
                    </div>
                </div>
            </div>

            <!-- Test Connection -->
            <div class="mb-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                <h2 class="text-lg font-bold text-blue-900 mb-4">Step 1: Test API Connection</h2>
                <p class="text-blue-800 text-sm mb-4">
                    This will verify that your Meta WhatsApp credentials are correctly configured.
                </p>
                <form method="post" class="flex gap-2">
                    <input type="hidden" name="action" value="test_connection">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                        Test Connection
                    </button>
                </form>
            </div>

            <!-- Send Test Message -->
            <div class="mb-8 p-6 bg-purple-50 border border-purple-200 rounded-lg">
                <h2 class="text-lg font-bold text-purple-900 mb-4">Step 2: Send Test Message</h2>
                <p class="text-purple-800 text-sm mb-4">
                    Send a test WhatsApp message to verify everything works end-to-end.
                </p>
                <form method="post" class="flex gap-2">
                    <input type="hidden" name="action" value="send_test">
                    <input 
                        type="tel" 
                        name="test_phone" 
                        placeholder="e.g., +918680857511 or 8680857511"
                        value="918680857511"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        required
                    >
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition">
                        Send Test
                    </button>
                </form>
            </div>

            <!-- Setup Instructions -->
            <div class="mb-8 p-6 bg-amber-50 border border-amber-200 rounded-lg">
                <h2 class="text-lg font-bold text-amber-900 mb-4">📋 Setup Instructions</h2>
                <ol class="list-decimal list-inside space-y-2 text-amber-900 text-sm">
                    <li>Create a Meta Business Account at <a href="https://business.facebook.com" target="_blank" class="text-blue-600 underline">business.facebook.com</a></li>
                    <li>Create a WhatsApp Business Account via WhatsApp Manager</li>
                    <li>Verify your phone number (+91 86808 57511)</li>
                    <li>Get your Phone Number ID from WhatsApp Manager</li>
                    <li>Generate Access Token with WhatsApp API permissions</li>
                    <li>Create <code class="bg-white px-2 py-1 rounded text-xs">.env</code> file with credentials</li>
                    <li>Run test above to verify</li>
                </ol>
                <p class="text-amber-900 text-sm mt-4">
                    📖 <a href="/WHATSAPP_SETUP.md" class="text-blue-600 underline">View detailed setup guide</a>
                </p>
            </div>

            <!-- Logs -->
            <div class="p-6 bg-gray-50 border rounded-lg">
                <h2 class="text-lg font-bold text-gray-900 mb-4">📊 Recent Notifications Log</h2>
                <div class="bg-white p-4 rounded font-mono text-xs text-gray-700 max-h-60 overflow-y-auto border">
                    <?php
                    $logFile = __DIR__ . '/uploads/notifications.log';
                    if (file_exists($logFile)) {
                        $lines = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES));
                        $lines = array_slice($lines, 0, 50);
                        foreach ($lines as $line) {
                            $line = htmlspecialchars($line);
                            // Color code by status
                            if (strpos($line, '✅') !== false) {
                                echo "<span class=\"text-green-600\">$line</span>\n";
                            } elseif (strpos($line, '❌') !== false) {
                                echo "<span class=\"text-red-600\">$line</span>\n";
                            } elseif (strpos($line, '⚠️') !== false) {
                                echo "<span class=\"text-yellow-600\">$line</span>\n";
                            } else {
                                echo "$line\n";
                            }
                        }
                    } else {
                        echo "No logs yet. Send a test message to create logs.";
                    }
                    ?>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    Log location: <code class="bg-gray-100 px-1 py-0.5 rounded">uploads/notifications.log</code>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
