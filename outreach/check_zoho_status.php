<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['connected' => false, 'error' => 'Not authenticated']);
    exit();
}

try {
    // Check if user has a valid token in the database
    $stmt = $pdo->prepare('SELECT access_token, refresh_token, created_at, expires_in FROM zoho_tokens WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $token_data = $stmt->fetch();

    if ($token_data) {
        // Token exists, check if it's expired
        $expiration_time = $token_data['created_at'] + $token_data['expires_in'];
        $is_expired = time() > $expiration_time;

        // Update session status
        $_SESSION['zoho_auth_status'] = 'connected';

        echo json_encode([
            'connected' => true,
            'expired' => $is_expired,
            'expires_at' => date('Y-m-d H:i:s', $expiration_time)
        ]);
    } else {
        // No token found, clear session status
        unset($_SESSION['zoho_auth_status']);

        echo json_encode([
            'connected' => false
        ]);
    }

} catch (PDOException $e) {
    error_log("Zoho status check error: " . $e->getMessage());
    echo json_encode([
        'connected' => false,
        'error' => 'Database error'
    ]);
}
?>
