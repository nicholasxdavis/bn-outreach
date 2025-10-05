<?php
require_once 'config.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $business_id = $data['business_id'] ?? null;
    $state_json = $data['state_json'] ?? null;

    if ($business_id && $state_json) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO business_states (user_id, business_id, state_json)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE state_json = VALUES(state_json)'
            );
            $stmt->execute([$_SESSION['user_id'], $business_id, $state_json]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);

        } catch (PDOException $e) {
            error_log("Error saving business state: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'A server error occurred.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data provided.']);
    }
}
?>