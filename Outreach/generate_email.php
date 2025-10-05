<?php
require_once '../config.php'; // Includes the API key definitions

// Securely get API keys from the server environment
$openRouterApiKey = getenv('OPENROUTER_API');
$perplexityApiKey = getenv('PERP_API');

header('Content-Type: application/json');

// Security check: ensure user is authenticated in the session
if (!isset($_SESSION['isAuthenticated']) || $_SESSION['isAuthenticated'] !== true) {
    echo json_encode(['error' => 'Authentication required.']);
    http_response_code(401);
    exit();
}

// Get the POST data sent from the frontend JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$businessName = $input['businessName'] ?? '';
$websiteStatus = $input['websiteStatus'] ?? '';
$reason = $input['reason'] ?? '';

if (empty($businessName) || empty($websiteStatus)) {
    echo json_encode(['error' => 'Business name and website status are required.']);
    http_response_code(400);
    exit();
}

// --- AI Prompt and Configuration ---
$emailExamples = '{"outreach_messages":[{"business":"La Nueva Casita Café","subject":"Website for La Nueva Casita Café","body":"Hi La Nueva Casita Café Team,...","close_off":"Thanks again for all you do for Las Cruces!\\n\\nBest,\\nNicholas","website_status":"No website","reason_for_contact":"Build a simple, modern, and easy-to-manage website"}]}'; // Abridged for brevity

$systemPrompt = "You are an expert copywriter for Blacnova (www.blacnova.net), a web development agency in Las Cruces, NM. Your task is to generate a single, concise, professional, and friendly outreach email object within a JSON structure. The output MUST be a valid JSON object matching this schema and contain only one message in the array: \n" .
'{ "outreach_messages": [ { "business": "string", "subject": "string", "body": "string", "close_off": "string", "website_status": "string", "reason_for_contact": "string" } ] }' .
"\nBase your response on the following examples for tone and content: {$emailExamples}\nOnly return the raw JSON object and nothing else.";

$userQuery = "Generate one outreach message for:\nBusiness Name: {$businessName}\nWebsite Status: {$websiteStatus}\nPrimary Reason for Contact: " . ($reason ?: 'Not provided');

// --- Function to call an API using cURL ---
function callApi($url, $apiKey, $payload, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $defaultHeaders = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => 'cURL Error: ' . $error];
    }

    $result = json_decode($response, true);
    $rawContent = $result['choices'][0]['message']['content'] ?? null;
    if (!$rawContent) {
        return ['error' => 'Invalid API response structure.'];
    }

    // Extract the JSON part of the response
    preg_match('/\{[\s\S]*\}/', $rawContent, $matches);
    if (!isset($matches[0])) {
        return ['error' => 'API did not return a valid JSON object.'];
    }

    $parsedResult = json_decode($matches[0], true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($parsedResult['outreach_messages'][0])) {
        return ['error' => 'API returned invalid JSON structure.'];
    }

    return $parsedResult['outreach_messages'][0];
}

// --- Main Logic: Try OpenRouter, then Perplexity ---
$emailData = null;

// 1. Try OpenRouter
if ($openRouterApiKey) {
    $openRouterPayload = [
        "model" => "deepseek/deepseek-chat-v3.1:free",
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userQuery]
        ]
    ];
    $openRouterHeaders = [
        'HTTP-Referer: https://blacnova.net',
        'X-Title: Blacnova Reacher AI'
    ];
    $emailData = callApi('https://openrouter.ai/api/v1/chat/completions', $openRouterApiKey, $openRouterPayload, $openRouterHeaders);
}

// 2. Fallback to Perplexity if OpenRouter fails or key is missing
if (isset($emailData['error']) && $perplexityApiKey) {
    $perplexityPayload = [
        "model" => "llama-3-sonar-large-32k-online",
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userQuery]
        ]
    ];
    $emailData = callApi('https://api.perplexity.ai/chat/completions', $perplexityApiKey, $perplexityPayload);
}

// --- Final Response ---
if (isset($emailData['error'])) {
    echo json_encode(['error' => 'Failed to generate email from all available AI sources.', 'details' => $emailData['error']]);
    http_response_code(500);
} else {
    echo json_encode($emailData);
}
?>