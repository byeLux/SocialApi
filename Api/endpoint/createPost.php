<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/bootstrap.php';
require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once ROOT_PATH . '/Infrastructure/Repository/PostRepository.php';
require_once ROOT_PATH . '/Infrastructure/Repository/UserRepository.php';
require_once ROOT_PATH . '/Api/middleware/TokenManager.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if(empty($authHeader)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'missing_auth_headers'
    ]);
    exit;
}

if(!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'invalid_auth_format'
    ]);
    exit;
}

$token = $matches[1];
$tokenManager = new TokenManager();
if(!$tokenManager->validate($token)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'invalid_token'
    ]);
    exit;
}
         
$post = json_decode(file_get_contents('php://input'), true);
if(!is_array($post)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

$content = trim($post['content'] ?? '');
if($content === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'invalid_content'
    ]);
    exit;
}

$userId = $tokenManager->getId($token);
$userRepository = new UserRepository();
if($userRepository->findOneById($userId) === null) {
    http_response_code(404);
    echo json_encode([
        'error' => 'user_not_found'
    ]);
    exit;
}

try {
    $postRepository = new PostRepository();
    $postRepository->create($userId, $content);
    http_response_code(201);
    echo json_encode([
        'success' => true
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'server_error'
    ]);
    exit;
}