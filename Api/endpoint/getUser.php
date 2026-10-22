<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/bootstrap.php';
require_once ROOT_PATH . '/Infrastructure/Repository/PostRepository.php';
require_once ROOT_PATH . '/Infrastructure/Repository/UserRepository.php';
require_once ROOT_PATH . '/Infrastructure/Serializers/UserSerializer.php';
require_once ROOT_PATH . '/Api/middleware/TokenManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
$serializer = new UserSerializer();
$user = $serializer->serialize($userRepository->findOneById($userId));

http_response_code(200);
echo json_encode([
    'user' => $user
]);
exit;