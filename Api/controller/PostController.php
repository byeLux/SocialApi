<?php
define('ROOT_PATH', dirname(__DIR__, 2)); // Api/controller → Api → SocialApi/

require_once ROOT_PATH . '/bootstrap.php';
require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once ROOT_PATH . '/Api/middleware/TokenManager.php';  // ← nota: 'middleware' minuscolo
require_once ROOT_PATH . '/Infrastructure/Repository/PostRepository.php';
require_once ROOT_PATH . '/Infrastructure/Serializers/PostSerializer.php';
require_once ROOT_PATH . '/Domain/Entity/Post.php';

header('Content-Type: application/json');

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

$page = (int)($_GET['page'] ?? 1);

if($page < 1) {
    $page = 1;
}

$postRepository = new PostRepository();
$postSerializer = new PostSerializer();

try {
    $posts = ($postRepository->extractPosts($page) ?? []);
    $postsToEncode = [];
    foreach($posts as $post) {
        $postsToEncode[] = $postSerializer->serialize($post);
    }
    $pagination = [
        'total_posts' => $postRepository->getTotalPosts(),
        'total_pages' => $postRepository->extractPages(),
        'posts_per_page' => $postRepository->getLimit(),
        'current_page' => $page
    ];
    $response = [
        "pagination" => $pagination,
        "posts" => $postsToEncode
    ];
    http_response_code(200);
    echo json_encode($response);  
    exit; 
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'server_error'
    ]);
    exit;
}