<?php
require_once __DIR__ . '/vendor/autoload.php';

// This loads the .env file for local development but won't crash in production
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Use getenv() with the CORRECT variable names provided by the Coolify MariaDB service
$db_host = getenv('MARIADB_HOST');
$db_name = getenv('MARIADB_DATABASE');
$db_user = getenv('MARIADB_USER');
$db_pass = getenv('MARIADB_PASSWORD');

try {
    // Check if the variables were loaded correctly
    if (!$db_host || !$db_name || !$db_user || !$db_pass) {
        throw new Exception("Database credentials are not fully configured. Please ensure the database service is linked correctly in your hosting environment.");
    }
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log errors to the standard error stream for Coolify to capture
    error_log("Configuration or Connection Error: " . $e->getMessage()); 
    http_response_code(500);
    die("A server error occurred. Please check the application logs for more details.");
}

// Use getenv() for your other secrets as well
define('ZOHO_CLIENT_ID', getenv('ZOHO_CLIENT_ID'));
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET'));
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
