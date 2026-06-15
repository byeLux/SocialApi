<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/bootstrap.php';
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

$userId = $tokenManager->getId($token);
$userRepository = new UserRepository();
$role = $userRepository->getRoleByUserId($userId);

if($role !== 'admin' && $role !== 'mod') {
    http_response_code(403);
    echo json_encode([
        'error' => 'forbidden'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if(!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

$postId = $input['post_id'] ?? null;
if($postId === null) {
    http_response_code(400);
    echo json_encode([
        'error' => 'missing_post_id'
    ]);
    exit;
}

$postRepository = new PostRepository();
$post = $postRepository->findPostById($postId);
if($post === null) {
    http_response_code(404);
    echo json_encode([
        'error' => 'post_not_found'
    ]);
    exit;
}

try {
    $postRepository->delete($post);
    http_response_code(200);
    echo json_encode([
        'success' => true
    ]);
    exit;
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'server_error'
    ]);
    exit;
}

