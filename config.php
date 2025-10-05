<?php
// Database Configuration
$host_eco = 'f08cwk48kso8wo84wk0ow840';
$dbname_eco = 'default';
$user_eco = 'mariadb';
$pass_eco = 'k8VUnt2oZhIgKebpi226TaRT9nwJN7B9kKGvhXTdqBNdzfnLe5r3hPmgLIVPZLYm';

// Zoho API Configuration
define('ZOHO_CLIENT_ID', '1000.TTUGKWF61IBWXX9XZ0AZ8EO0NDYE2O');
define('ZOHO_CLIENT_SECRET', 'd9df0a116dc44b7a8786baaef163b5e77c496e1cbd');
// This URL must be the publicly accessible URL of your zoho-oauth.php file
define('ZOHO_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/Outreach/zoho-oauth.php');

// Start PHP Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>