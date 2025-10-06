<?php
// Zoho Token Manager
function get_zoho_access_token($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT * FROM zoho_tokens WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $token_data = $stmt->fetch();

    if (!$token_data) {
        return null;
    }

    // Zoho tokens expire in 1 hour (3600 seconds)
    $expiration_time = $token_data['created_at'] + $token_data['expires_in'] - 60; // 60s buffer

    if (time() > $expiration_time) {
        // Token is expired, refresh it
        return refresh_zoho_token($pdo, $user_id, $token_data['refresh_token']);
    }

    return $token_data['access_token'];
}

function refresh_zoho_token($pdo, $user_id, $refresh_token) {
    $token_url = "https://accounts.zoho.com/oauth/v2/token";
    $post_fields = [
        'refresh_token' => $refresh_token,
        'client_id' => ZOHO_CLIENT_ID,
        'client_secret' => ZOHO_CLIENT_SECRET,
        'grant_type' => 'refresh_token'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $new_token_data = json_decode($response, true);

    if (isset($new_token_data['access_token'])) {
        // Update the new token in the database
        $stmt = $pdo->prepare(
            'UPDATE zoho_tokens SET access_token = ?, expires_in = ?, created_at = ? WHERE user_id = ?'
        );
        $stmt->execute([
            $new_token_data['access_token'],
            $new_token_data['expires_in'],
            time(), // Use the current Unix timestamp
            $user_id
        ]);
        return $new_token_data['access_token'];
    }

    // If refresh fails, you should handle the error appropriately
    // For now, we'll return null and the calling script should handle it
    error_log("Zoho token refresh failed: " . $response, 3, "error.log");
    return null;
}
?>