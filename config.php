<?php
require_once __DIR__ . '/vendor/autoload.php';

// This loads a .env file if it exists (for local development)
// but will NOT crash if it's missing (for production on Coolify).
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Use getenv() to reliably read the standard environment variables 
// provided by the Coolify MariaDB service.
$db_host = getenv('MARIADB_HOST');
$db_name = getenv('MARIADB_DATABASE');
$db_user = getenv('MARIADB_USER');
$db_pass = getenv('MARIADB_PASSWORD');

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
