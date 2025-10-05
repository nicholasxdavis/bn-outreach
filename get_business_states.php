<?php
require_once 'config.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT business_id, state_json FROM business_states WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $states = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    header('Content-Type: application/json');
    echo json_encode($states);

} catch (PDOException $e) {
    error_log("Error fetching business states: " . $e->getMessage(), 3, "error.log");
    http_response_code(500);
    echo json_encode(['error' => 'A server error occurred.']);
}
?>