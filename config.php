<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load .env file for local development, but don't crash if it's missing (for production)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Use getenv() to reliably read environment variables from both .env and Coolify
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

try {
    // Check if the variables were loaded correctly
    if (!$db_host || !$db_name || !$db_user || !$db_pass) {
        throw new Exception("Database credentials are not fully configured.");
    }
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log to stderr for containerized environments like Coolify
    error_log("Configuration or Connection Error: " . $e->getMessage()); 
    // Show a generic error message to the user
    http_response_code(500);
    die("A server error occurred. Please check the application logs for more details.");
}

// Use getenv() for other environment variables as well
define('ZOHO_CLIENT_ID', getenv('ZOHO_CLIENT_ID'));
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET'));
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
