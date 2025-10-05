<?php
require_once 'config.php';

$response = ['success' => false, 'message' => 'Invalid credentials. Please try again.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $passcode = $data['passcode'] ?? '';

    if (!empty($username) && !empty($passcode)) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($passcode, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['isAuthenticated'] = true;
                $_SESSION['user_id'] = $user['id'];
                $response['success'] = true;
                $response['message'] = 'Login successful!';
            } else {
                $response['message'] = 'Invalid username or passcode.';
            }
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage(), 3, "error.log");
            $response['message'] = 'A server error occurred. Please try again later.';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>