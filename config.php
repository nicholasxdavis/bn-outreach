<?php
require_once __DIR__ . '/vendor/autoload.php';

// IMPORTANT: In a production environment, you should move your .env file
// outside of the public web directory for security reasons.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_name = $_ENV['DB_NAME'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error to a file instead of displaying it to the user.
    error_log("Database connection failed: " . $e->getMessage(), 3, "error.log");
    // Show a generic error message to the user.
    die("A server error occurred. Please try again later.");
}


define('ZOHO_CLIENT_ID', $_ENV['ZOHO_CLIENT_ID']);
define('ZOHO_CLIENT_SECRET', $_ENV['ZOHO_CLIENT_SECRET']);
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>