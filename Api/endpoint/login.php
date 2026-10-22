<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once __DIR__ . '/../../Infrastructure/Repository/UserRepository.php';
require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/../middleware/TokenManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$post = json_decode(file_get_contents('php://input'), true);
if(!is_array($post)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

$username = trim($post['username'] ?? '');
$password = $post['password'] ?? '';

if (!$username || $username === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_username']);
    exit;
}

if (!$password || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_password']);
    exit;
}

$userRepository = new UserRepository();
$user = $userRepository->findOneByUsername($username);
$hash = $user ? $user->getPassword() : null;
if (!password_verify($password, $hash) || $user === null) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_credentials']);
    exit;
}

$tokenManager = new TokenManager();
$token = $tokenManager->generate($user->getId());
http_response_code(200);
echo json_encode([
    'token' => $token
]);
exit;