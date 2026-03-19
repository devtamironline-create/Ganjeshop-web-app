<?php
/**
 * SMS Proxy for Kavenegar
 *
 * Deploy this file on api.ganjemarket.com
 * URL: https://api.ganjemarket.com/sms-proxy.php
 *
 * This script receives SMS requests from your main server
 * and forwards them to Kavenegar API.
 */

// ========================================
// CONFIG - Set your secret key here
// ========================================
define('PROXY_SECRET', 'YOUR_SECRET_KEY_HERE'); // همین کلید را در تنظیمات پیامک قالب هم وارد کنید

// ========================================
// CORS & Headers
// ========================================
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ========================================
// Authentication
// ========================================
$headers = getallheaders();
$received_secret = isset($headers['X-Proxy-Secret']) ? $headers['X-Proxy-Secret'] : '';

// Case-insensitive header check
if (empty($received_secret)) {
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-proxy-secret') {
            $received_secret = $value;
            break;
        }
    }
}

if (empty(PROXY_SECRET) || PROXY_SECRET === 'YOUR_SECRET_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Proxy secret not configured']);
    exit;
}

if (!hash_equals(PROXY_SECRET, $received_secret)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid secret']);
    exit;
}

// ========================================
// Parse Request
// ========================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action']) || !isset($input['params'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request format']);
    exit;
}

$action = $input['action'];
$params = $input['params'];

// Validate required params
if (empty($params['api_key']) || empty($params['receptor'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$api_key = $params['api_key'];

// ========================================
// Forward to Kavenegar
// ========================================
switch ($action) {
    case 'verify':
        // OTP Verify Lookup
        if (empty($params['token']) || empty($params['template'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing token or template']);
            exit;
        }

        $url = "https://api.kavenegar.com/v1/{$api_key}/verify/lookup.json";
        $post_data = [
            'receptor' => $params['receptor'],
            'token'    => $params['token'],
            'template' => $params['template'],
        ];
        break;

    case 'send':
        // Simple SMS Send
        if (empty($params['message'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing message']);
            exit;
        }

        $url = "https://api.kavenegar.com/v1/{$api_key}/sms/send.json";
        $post_data = [
            'receptor' => $params['receptor'],
            'message'  => $params['message'],
        ];
        if (!empty($params['sender'])) {
            $post_data['sender'] = $params['sender'];
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        exit;
}

// Send request to Kavenegar
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($post_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curl_error]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['return']['status']) && $result['return']['status'] == 200) {
    echo json_encode(['success' => true, 'message' => 'OK']);
} else {
    $error = isset($result['return']['message']) ? $result['return']['message'] : 'Unknown Kavenegar error';
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => $error]);
}
