<?php
require_once '../config.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

if (!isset($_GET['code'])) {
    $authorization_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
        'scope' => 'ZohoMail.messages.CREATE',
        'client_id' => ZOHO_CLIENT_ID,
        'response_type' => 'code',
        'access_type' => 'offline',
        'redirect_uri' => ZOHO_REDIRECT_URI
    ]);
    header('Location: ' . $authorization_url);
    exit();
} else {
    $code = $_GET['code'];
    $token_url = "https://accounts.zoho.com/oauth/v2/token";

    $post_fields = [
        'code' => $code,
        'client_id' => ZOHO_CLIENT_ID,
        'client_secret' => ZOHO_CLIENT_SECRET,
        'redirect_uri' => ZOHO_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token']) && isset($token_data['refresh_token'])) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO zoho_tokens (user_id, access_token, refresh_token, expires_in) 
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 access_token = VALUES(access_token), 
                 refresh_token = VALUES(refresh_token), 
                 expires_in = VALUES(expires_in)'
            );
            $stmt->execute([
                $_SESSION['user_id'],
                $token_data['access_token'],
                $token_data['refresh_token'],
                $token_data['expires_in']
            ]);
            $_SESSION['zoho_auth_status'] = 'connected';
            header('Location: index.php?status=success');
            exit();
        } catch (PDOException $e) {
            error_log("Zoho token storage error: " . $e->getMessage(), 3, "error.log");
            header('Location: index.php?status=error&message=dberror');
            exit();
        }
    } else {
        header('Location: index.php?status=error&message=' . urlencode($token_data['error']));
        exit();
    }
}
?>