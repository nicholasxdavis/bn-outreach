<?php
require_once 'config.php';

// Default response
$response = ['success' => false, 'message' => 'Invalid credentials. Please try again.'];

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $passcode = $data['passcode'] ?? '';

    // --- IMPORTANT ---
    // In a real application, you should verify these credentials against a database.
    // For this implementation, we are using the hardcoded values from the original JavaScript.
    if (strtolower($username) === 'nic@blacnova.net' && $passcode === '2900') {
        // If credentials are correct, set a session variable
        $_SESSION['isAuthenticated'] = true;
        $response['success'] = true;
        $response['message'] = 'Login successful!';
    }
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>