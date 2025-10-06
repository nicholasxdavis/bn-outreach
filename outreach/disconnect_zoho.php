<?php
require_once '../config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated
if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Delete the Zoho token from database
    $stmt = $pdo->prepare('DELETE FROM zoho_tokens WHERE user_id = ?');
    $result = $stmt->execute([$user_id]);

    if ($result) {
        // Clear session status
        unset($_SESSION['zoho_auth_status']);

        // Log the disconnection
        error_log("Zoho disconnected successfully for user $user_id");

        // Redirect back to outreach page with success message
        header('Location: index.php?status=disconnected');
        exit();
    } else {
        error_log("Failed to delete Zoho token for user $user_id");
        header('Location: index.php?status=error&message=Failed to disconnect Zoho account');
        exit();
    }

} catch (PDOException $e) {
    error_log("Zoho disconnect error: " . $e->getMessage());
    header('Location: index.php?status=error&message=Database error during disconnection');
    exit();
}
?>