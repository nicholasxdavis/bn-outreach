<?php
require_once '../config.php';

// 1. If 'code' is not in the URL, redirect to Zoho for authorization.
if (!isset($_GET['code'])) {
    $authorization_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
        'scope' => 'ZohoMail.messages.CREATE', // Scope for sending emails
        'client_id' => ZOHO_CLIENT_ID,
        'response_type' => 'code',
        'access_type' => 'offline', // To get a refresh token
        'redirect_uri' => ZOHO_REDIRECT_URI
    ]);
    header('Location: ' . $authorization_url);
    exit();
}
// 2. If 'code' is present, exchange it for an access token.
else {
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

    // 3. If we get an access token, save it to the session.
    if (isset($token_data['access_token'])) {
        $_SESSION['zoho_access_token'] = $token_data['access_token'];

        // In a real application, you should securely store the refresh_token in your database
        // associated with the user, so you can get new access tokens without user interaction.
        if (isset($token_data['refresh_token'])) {
            $_SESSION['zoho_refresh_token'] = $token_data['refresh_token']; // Storing in session for demo
        }

        $_SESSION['zoho_auth_status'] = 'connected';
        // Redirect back to the outreach tool with a success status
        header('Location: index.php?status=success');
        exit();
    } else {
        // If there was an error, redirect with an error status
        header('Location: index.php?status=error&message=' . urlencode($token_data['error']));
        exit();
    }
}
?>