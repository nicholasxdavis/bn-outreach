<?php
require_once '../config.php';
require_once 'zoho_token_manager.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required.';
    header('Content-Type: application/json', true, 401);
    echo json_encode($response);
    exit();
}

$access_token = get_zoho_access_token($pdo, $_SESSION['user_id']);

if (!$access_token) {
    $response['message'] = 'Zoho Mail account is not connected or the token is invalid.';
    header('Content-Type: application/json', true, 400);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $recipientEmail = filter_var($data['recipient_email'] ?? '', FILTER_SANITIZE_EMAIL);
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid recipient email address.';
        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }
    
    $subject = htmlspecialchars($data['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars($data['body'] ?? '', ENT_QUOTES, 'UTF-8');

    $accounts_url = "https://mail.zoho.com/api/accounts";
    $ch_accounts = curl_init();
    curl_setopt($ch_accounts, CURLOPT_URL, $accounts_url);
    curl_setopt($ch_accounts, CURLOPT_HTTPHEADER, [
        'Authorization: Zoho-oauthtoken ' . $access_token
    ]);
    curl_setopt($ch_accounts, CURLOPT_RETURNTRANSFER, true);
    $accounts_response = curl_exec($ch_accounts);
    $http_code = curl_getinfo($ch_accounts, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch_accounts);
    curl_close($ch_accounts);

    // Log the API response for debugging
    error_log("Zoho Accounts API Response (HTTP $http_code): " . $accounts_response);
    if ($curl_error) {
        error_log("Zoho Accounts API cURL Error: " . $curl_error);
    }

    if ($http_code == 200) {
        $accounts_data = json_decode($accounts_response, true);

        // Log the decoded data structure
        error_log("Zoho Accounts Data Structure: " . print_r($accounts_data, true));

        if (isset($accounts_data['data'][0]['accountId']) && isset($accounts_data['data'][0]['fromAddress'])) {
            $accountId = $accounts_data['data'][0]['accountId'];
            $fromAddress = $accounts_data['data'][0]['fromAddress'];

            $send_url = "https://mail.zoho.com/api/accounts/{$accountId}/messages";

            $email_payload = json_encode([
                "fromAddress" => $fromAddress,
                "toAddress" => $recipientEmail,
                "subject" => $subject,
                "content" => $body,
                "askReceipt" => "no"
            ]);

            $ch_send = curl_init();
            curl_setopt($ch_send, CURLOPT_URL, $send_url);
            curl_setopt($ch_send, CURLOPT_POST, 1);
            curl_setopt($ch_send, CURLOPT_POSTFIELDS, $email_payload);
            curl_setopt($ch_send, CURLOPT_HTTPHEADER, [
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch_send, CURLOPT_RETURNTRANSFER, true);
            $send_response_body = curl_exec($ch_send);
            $send_http_code = curl_getinfo($ch_send, CURLINFO_HTTP_CODE);
            curl_close($ch_send);

            if ($send_http_code == 200) {
                 $response['success'] = true;
                 $response['message'] = 'Email sent successfully via Zoho!';
            } else {
                 error_log("Zoho Send Email API Error (HTTP $send_http_code): " . $send_response_body);
                 $response['message'] = 'Failed to send email. Zoho API Error: ' . $send_response_body;
            }
        } else {
            // Provide more detailed error information
            $response['message'] = 'Could not retrieve Zoho Account ID. The API response was invalid. Check server logs for details.';
            $response['debug_info'] = [
                'has_data' => isset($accounts_data['data']),
                'data_count' => isset($accounts_data['data']) ? count($accounts_data['data']) : 0,
                'has_accountId' => isset($accounts_data['data'][0]['accountId']),
                'has_fromAddress' => isset($accounts_data['data'][0]['fromAddress']),
                'response_keys' => isset($accounts_data) ? array_keys($accounts_data) : []
            ];
        }
    } else {
        error_log("Zoho Accounts API failed with HTTP code: $http_code");
        $response['message'] = 'Could not retrieve Zoho accounts. Your access token might be expired or invalid. (HTTP ' . $http_code . ')';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>