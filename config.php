<?php
ini_set('display_errors', 0);
error_reporting(0);
// require_once __DIR__ . '/vendor/autoload.php';

// This will load a .env file if it exists (for local development)
// but will NOT crash if it's missing (for production on Coolify).
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->safeLoad();

// Use getenv() to reliably read environment variables from BOTH .env and Coolify.
// Coolify provides database credentials with the "MARIADB_" prefix when you link the service.
$db_host = getenv('MARIADB_HOST') ?: getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('MARIADB_DATABASE') ?: getenv('DB_NAME') ?: 'outreach';
$db_user = getenv('MARIADB_USER') ?: getenv('DB_USER') ?: 'root';
$db_pass = getenv('MARIADB_PASSWORD') ?: getenv('DB_PASS') ?: '';

try {
    // This check is crucial. It will throw a clear error if the variables are missing.
    if (!$db_host || !$db_name || !$db_user || !$db_pass) {
        throw new Exception("Database credentials are not fully configured. Please ensure the database service is linked in Coolify and the application is redeployed.");
    }
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log errors to the standard output so Coolify can display them in the logs.
    error_log("Configuration or Connection Error: " . $e->getMessage()); 
    http_response_code(500);
    // Send a JSON error back to the frontend to prevent the SyntaxError.
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Server configuration error.']));
}

// Use getenv() for your other secrets as well.
define('ZOHO_CLIENT_ID', getenv('ZOHO_CLIENT_ID'));
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET'));
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>