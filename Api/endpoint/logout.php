<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/Api/middleware/TokenManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$post = json_decode(file_get_contents('php://input'), true);

if(!is_array($post)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

if(!isset($post['token']) || !is_string($post['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_token']);
    exit;
}

$token = $post['token'];
$tokenManager = new TokenManager();
if(!$tokenManager->validate($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_token']);
    exit;
}

$tokenManager->invalidate($token);

http_response_code(200);
echo json_encode(['logout' => true]);
exit;