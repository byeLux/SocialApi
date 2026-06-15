<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once ROOT_PATH . '/Infrastructure/Repository/UserRepository.php';
require_once ROOT_PATH . '/Infrastructure/Repository/UserSerializer.php';
require_once ROOT_PATH . '/Domain/Entity/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$post = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || empty($post)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'invalid_json',
        'code' => 0
    ]);
    exit;
}

$username = trim($post['username'] ?? '');
$password = $post['password'] ?? '';
$passwordCheck = $post['password-check'] ?? '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_username', 'code' => 1]);
    exit;
}

if (empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_password', 'code' => 2]);
    exit;
}

if ($password !== $passwordCheck) {
    http_response_code(400);
    echo json_encode(['error' => 'password_mismatch', 'code' => 3]);
    exit;
}

$userRepository = new UserRepository();

$user = $userRepository->findOneByUsername($username);

if ($user !== null) {
    http_response_code(409);
    echo json_encode([
        'error' => 'user_already_exists',
        'code' => 4
    ]);
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$userRepository->createUser($username, $hash);

http_response_code(200);
echo json_encode([
    'success' => true
]);
exit;