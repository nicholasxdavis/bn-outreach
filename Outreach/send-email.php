<?php
require_once '../config.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['isAuthenticated']) || $_SESSION['isAuthenticated'] !== true) {
    $response['message'] = 'Authentication required.';
    header('Content-Type: application/json', true, 401);
    echo json_encode($response);
    exit();
}

if (!isset($_SESSION['zoho_access_token'])) {
    $response['message'] = 'Zoho Mail account is not connected.';
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
        'Authorization: Zoho-oauthtoken ' . $_SESSION['zoho_access_token']
    ]);
    curl_setopt($ch_accounts, CURLOPT_RETURNTRANSFER, true);
    $accounts_response = curl_exec($ch_accounts);
    $http_code = curl_getinfo($ch_accounts, CURLINFO_HTTP_CODE);
    curl_close($ch_accounts);

    if ($http_code == 200) {
        $accounts_data = json_decode($accounts_response, true);
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
                'Authorization: Zoho-oauthtoken ' . $_SESSION['zoho_access_token'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch_send, CURLOPT_RETURNTRANSFER, true);
            $send_response_body = curl_exec($ch_send);
            $send_http__code = curl_getinfo($ch_send, CURLINFO_HTTP_CODE);
            curl_close($ch_send);

            if ($send_http_code == 200) {
                 $response['success'] = true;
                 $response['message'] = 'Email sent successfully via Zoho!';
            } else {
                 $response['message'] = 'Failed to send email. Zoho API Error: ' . $send_response_body;
            }
        } else {
            $response['message'] = 'Could not retrieve Zoho Account ID. The API response was invalid.';
        }
    } else {
        $response['message'] = 'Could not retrieve Zoho accounts. Your access token might be expired or invalid.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>