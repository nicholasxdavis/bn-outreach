<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // This loads the .env file for local development

// Use getenv() to reliably read secrets from both .env and the hosting environment
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

try {
    if (!$db_host || !$db_name || !$db_user || !$db_pass) {
        throw new Exception("Database credentials are not fully configured.");
    }
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Configuration or Connection Error: " . $e->getMessage()); 
    http_response_code(500);
    die("A server error occurred. Please check the application logs.");
}

define('ZOHO_CLIENT_ID', getenv('ZOHO_CLIENT_ID'));
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET'));
// Note: This redirect URI needs to be updated if you switch to HTTPS
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>