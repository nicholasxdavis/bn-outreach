<?php
require_once '../config.php';

// Log the start of OAuth process
error_log("Zoho OAuth process started");

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    error_log("Zoho OAuth: User not authenticated, redirecting to index.html");
    header('Location: ../index.html');
    exit();
}

// Log environment variables for debugging (without exposing secrets)
error_log("Zoho OAuth: Client ID is " . (ZOHO_CLIENT_ID ? 'SET (length: ' . strlen(ZOHO_CLIENT_ID) . ')' : 'NOT SET'));
error_log("Zoho OAuth: Client Secret is " . (ZOHO_CLIENT_SECRET ? 'SET (length: ' . strlen(ZOHO_CLIENT_SECRET) . ')' : 'NOT SET'));
error_log("Zoho OAuth: Redirect URI is " . ZOHO_REDIRECT_URI);

// Check if credentials are actually set
if (!ZOHO_CLIENT_ID || !ZOHO_CLIENT_SECRET) {
    error_log("Zoho OAuth: CRITICAL - Missing Zoho credentials!");
    header('Location: index.php?status=error&message=' . urlencode('Zoho credentials not configured. Check ZOHO_CLIENT_ID and ZOHO_CLIENT_SECRET environment variables.'));
    exit();
}

if (!isset($_GET['code'])) {
    error_log("Zoho OAuth: No authorization code, redirecting to Zoho for authorization");
    $authorization_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
        'scope' => 'ZohoMail.accounts.READ ZohoMail.messages.CREATE',
        'client_id' => ZOHO_CLIENT_ID,
        'response_type' => 'code',
        'access_type' => 'offline',
        'redirect_uri' => ZOHO_REDIRECT_URI
    ]);
    error_log("Zoho OAuth: Authorization URL: " . $authorization_url);
    header('Location: ' . $authorization_url);
    exit();
} else {
    $code = $_GET['code'];
    error_log("Zoho OAuth: Received authorization code, exchanging for tokens");

    $token_url = "https://accounts.zoho.com/oauth/v2/token";

    $post_fields = [
        'code' => $code,
        'client_id' => ZOHO_CLIENT_ID,
        'client_secret' => ZOHO_CLIENT_SECRET,
        'redirect_uri' => ZOHO_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    error_log("Zoho OAuth: Token exchange request data: " . print_r([
        'code' => substr($code, 0, 10) . '...', // Log only first 10 chars for security
        'client_id' => ZOHO_CLIENT_ID,
        'redirect_uri' => ZOHO_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ], true));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    error_log("Zoho OAuth: Token exchange response (HTTP $http_code): " . $response);
    if ($curl_error) {
        error_log("Zoho OAuth: cURL error: " . $curl_error);
    }

    $token_data = json_decode($response, true);

    // Log the full decoded response for debugging
    error_log("Zoho OAuth: Decoded token data: " . print_r($token_data, true));

    if (isset($token_data['access_token']) && isset($token_data['refresh_token'])) {
        error_log("Zoho OAuth: Successfully received tokens, storing in database");
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO zoho_tokens (user_id, access_token, refresh_token, expires_in, created_at)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 access_token = VALUES(access_token),
                 refresh_token = VALUES(refresh_token),
                 expires_in = VALUES(expires_in),
                 created_at = VALUES(created_at)'
            );
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $token_data['access_token'],
                $token_data['refresh_token'],
                $token_data['expires_in'],
                time() // Store the current timestamp
            ]);

            if ($result) {
                error_log("Zoho OAuth: Tokens stored successfully for user " . $_SESSION['user_id']);
                $_SESSION['zoho_auth_status'] = 'connected';
                header('Location: index.php?status=success');
                exit();
            } else {
                error_log("Zoho OAuth: Failed to store tokens in database");
                header('Location: index.php?status=error&message=dberror');
                exit();
            }
        } catch (PDOException $e) {
            error_log("Zoho token storage error: " . $e->getMessage());
            error_log("Zoho PDO Error Code: " . $e->getCode());
            header('Location: index.php?status=error&message=dberror');
            exit();
        }
    } else {
        error_log("Zoho OAuth: Token exchange failed. Response: " . $response);

        // Check if response is empty or null
        if (empty($response)) {
            error_log("Zoho OAuth Error: Empty response from Zoho API");
            header('Location: index.php?status=error&message=' . urlencode('Empty response from Zoho. Check your credentials and network connection.'));
            exit();
        }

        // Check if JSON decode failed
        if ($token_data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("Zoho OAuth Error: JSON decode failed - " . json_last_error_msg());
            header('Location: index.php?status=error&message=' . urlencode('Invalid response format from Zoho: ' . json_last_error_msg()));
            exit();
        }

        $error_message = isset($token_data['error']) ? $token_data['error'] : 'unknown_error';
        $error_description = isset($token_data['error_description']) ? $token_data['error_description'] : 'No description provided';

        // Check for common Zoho error scenarios
        if ($http_code == 400) {
            error_log("Zoho OAuth Error: Bad Request (400) - likely invalid credentials or redirect URI mismatch");
            $error_description = "Bad Request - Check your Zoho Client ID, Secret, and Redirect URI configuration";
        } elseif ($http_code == 401) {
            error_log("Zoho OAuth Error: Unauthorized (401) - invalid client credentials");
            $error_description = "Unauthorized - Your Zoho Client ID or Secret is invalid";
        } elseif ($http_code == 0) {
            error_log("Zoho OAuth Error: No response (0) - network or SSL issue");
            $error_description = "Network error - Unable to connect to Zoho servers";
        }

        error_log("Zoho OAuth Error: $error_message - $error_description");
        header('Location: index.php?status=error&message=' . urlencode($error_message . ': ' . $error_description));
        exit();
    }
}

?>
