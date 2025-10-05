<?php
// Database Configuration using Environment Variables
$host_eco = getenv('DB_HOST');
$dbname_eco = getenv('DB_NAME');
$user_eco = 'mariadb'; // As specified, the user is not from an environment variable
$pass_eco = getenv('DB_PASS');

// Zoho API Configuration using Environment Variables
// The ZOHO_API variable holds the Client ID
define('ZOHO_CLIENT_ID', getenv('ZOHO_API')); 
// The Client Secret should also be an environment variable for security
define('ZOHO_CLIENT_SECRET', getenv('ZOHO_CLIENT_SECRET')); 

// This URL must be the publicly accessible URL of your zoho-oauth.php file
// It dynamically creates the URL based on your server's domain
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

// Start PHP Session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>