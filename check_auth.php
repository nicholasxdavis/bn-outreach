<?php
require_once 'config.php';

header('Content-Type: application/json');

echo json_encode([
    'isAuthenticated' => isset($_SESSION['isAuthenticated']) && $_SESSION['isAuthenticated'] === true
]);
?>