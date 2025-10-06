<?php
require_once '../config.php';

// Check authentication
if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    die("Not authenticated");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Zoho Configuration Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4ade80; }
        .error { color: #ef4444; }
        .warning { color: #fbbf24; }
        pre { background: #0a0a0a; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Zoho Configuration Test</h1>

    <h2>Environment Variables:</h2>
    <pre>
ZOHO_CLIENT_ID: <?php
    if (ZOHO_CLIENT_ID) {
        echo '<span class="success">✓ SET (length: ' . strlen(ZOHO_CLIENT_ID) . ')</span>';
        echo "\nFirst 10 chars: " . substr(ZOHO_CLIENT_ID, 0, 10) . '...';
    } else {
        echo '<span class="error">✗ NOT SET</span>';
    }
?>

ZOHO_CLIENT_SECRET: <?php
    if (ZOHO_CLIENT_SECRET) {
        echo '<span class="success">✓ SET (length: ' . strlen(ZOHO_CLIENT_SECRET) . ')</span>';
        echo "\nFirst 10 chars: " . substr(ZOHO_CLIENT_SECRET, 0, 10) . '...';
    } else {
        echo '<span class="error">✗ NOT SET</span>';
    }
?>

ZOHO_REDIRECT_URI: <?php echo ZOHO_REDIRECT_URI; ?>
    </pre>

    <h2>Server Information:</h2>
    <pre>
HTTP_HOST: <?php echo $_SERVER['HTTP_HOST']; ?>
HTTPS: <?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '<span class="success">Yes (using HTTPS)</span>' : '<span class="warning">No (using HTTP)</span>'; ?>
REQUEST_SCHEME: <?php echo $_SERVER['REQUEST_SCHEME'] ?? 'not set'; ?>
    </pre>

    <h2>Zoho Authorization URL:</h2>
    <?php
    if (ZOHO_CLIENT_ID && ZOHO_CLIENT_SECRET) {
        $auth_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
            'scope' => 'ZohoMail.accounts.READ ZohoMail.messages.CREATE',
            'client_id' => ZOHO_CLIENT_ID,
            'response_type' => 'code',
            'access_type' => 'offline',
            'redirect_uri' => ZOHO_REDIRECT_URI
        ]);
        echo '<pre>' . htmlspecialchars($auth_url) . '</pre>';
        echo '<p><a href="' . htmlspecialchars($auth_url) . '" style="color: #4ade80;">Click here to test authorization</a></p>';
    } else {
        echo '<p class="error">Cannot generate authorization URL - credentials missing</p>';
    }
    ?>

    <h2>Database Check:</h2>
    <pre>
<?php
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'zoho_tokens'");
    $table_exists = $stmt->rowCount() > 0;

    if ($table_exists) {
        echo '<span class="success">✓ zoho_tokens table exists</span>';

        // Check if user has a token
        $stmt = $pdo->prepare('SELECT id, created_at, expires_in FROM zoho_tokens WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $token = $stmt->fetch();

        if ($token) {
            echo "\n<span class='warning'>⚠ User already has a token (ID: {$token['id']})</span>";
            echo "\n  Created: " . date('Y-m-d H:i:s', $token['created_at']);
            echo "\n  Expires in: {$token['expires_in']} seconds";
        } else {
            echo "\n<span class='success'>✓ No existing token for this user</span>";
        }
    } else {
        echo '<span class="error">✗ zoho_tokens table does NOT exist</span>';
        echo "\n<span class='warning'>Run database_setup.php to create tables</span>";
    }
} catch (Exception $e) {
    echo '<span class="error">✗ Database error: ' . htmlspecialchars($e->getMessage()) . '</span>';
}
?>
    </pre>

    <p><a href="index.php" style="color: #4ade80;">← Back to Outreach</a></p>
</body>
</html>
