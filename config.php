<?php
// Suppress all output before JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Read environment variables directly from Coolify
$db_host = getenv('MARIADB_HOST');
$db_name = getenv('MARIADB_DATABASE');
$db_user = getenv('MARIADB_USER');
$db_pass = getenv('MARIADB_PASSWORD');

// Log what we're getting for debugging
error_log("DB Connection Attempt - Host: " . ($db_host ?: 'NOT SET') . ", Database: " . ($db_name ?: 'NOT SET') . ", User: " . ($db_user ?: 'NOT SET'));

try {
    // This check is crucial. It will throw a clear error if the variables are missing.
    if (!$db_host || !$db_name || !$db_user) {
        $missing = [];
        if (!$db_host) $missing[] = 'MARIADB_HOST';
        if (!$db_name) $missing[] = 'MARIADB_DATABASE';
        if (!$db_user) $missing[] = 'MARIADB_USER';
        if (!$db_pass) $missing[] = 'MARIADB_PASSWORD';
        throw new Exception("Missing environment variables: " . implode(', ', $missing));
    }

    error_log("Attempting PDO connection to: mysql:host=$db_host;dbname=$db_name");
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connection successful!");

} catch (Exception $e) {
    // Log detailed error to help diagnose
    error_log("DATABASE CONNECTION FAILED: " . $e->getMessage());
    error_log("Error Code: " . $e->getCode());
    http_response_code(500);
    // Send a JSON error back to the frontend to prevent the SyntaxError.
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Database connection error. Check server logs.']));
}

// Use getenv() for your other secrets as well.
define('ZOHO_CLIENT_ID', getenv('ZOHO_API') ?: getenv('ZOHO_CLIENT_ID'));
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET'));
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/outreach/zoho_oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
