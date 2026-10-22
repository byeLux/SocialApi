<?php

declare(strict_types=1);

use SocialApi\Api\Request;
use SocialApi\Api\JsonResponse;
use SocialApi\Api\Router;
use SocialApi\Api\Controllers\AuthController;
use SocialApi\Api\Controllers\UserController;
use SocialApi\Api\Controllers\PostController;
use SocialApi\Api\middleware\AuthMiddleware;
use SocialApi\Application\Managers\AuthManager;
use SocialApi\Application\Managers\UserManager;
use SocialApi\Application\Managers\PostManager;
use SocialApi\Business\Services\AuthService;
use SocialApi\Business\Services\UserService;
use SocialApi\Business\Services\PostService;
use SocialApi\Infrastructure\Persistence\DbConnector;
use SocialApi\Infrastructure\Repository\UserRepository;
use SocialApi\Infrastructure\Repository\PostRepository;
use SocialApi\Infrastructure\Repository\AuthTokenRepository;
use SocialApi\Infrastructure\Serializers\UserSerializer;
use SocialApi\Infrastructure\Serializers\PostSerializer;
use SocialApi\Infrastructure\Security\TokenManager;

ini_set('display_errors', '0');

try {
    require_once dirname(__DIR__) . '/bootstrap.php';

    // Ogni closure costruisce soltanto il grafo della route eseguita.
    $createAuthController = function (Request $request, bool $protected = false): AuthController {
        $dbConnector = DbConnector::getInstance();
        $userSerializer = new UserSerializer();
        $userRepository = new UserRepository($dbConnector, $userSerializer);
        $authService = new AuthService();
        $userService = new UserService();
        $authTokenRepository = new AuthTokenRepository($dbConnector);
        $tokenManager = new TokenManager($authTokenRepository, $_ENV['JWT_KEY'] ?? '');

        if($protected) {
            $userManager = new UserManager($authService, $userRepository);
            $authMiddleware = new AuthMiddleware($tokenManager, $userManager);
            $authMiddleware->authenticate($request);
        }

        $authManager = new AuthManager(
            $authService,
            $userService,
            $userRepository,
            $tokenManager
        );

        return new AuthController($authManager);
    };

    $createUserController = function (Request $request): UserController {
        $dbConnector = DbConnector::getInstance();
        $userSerializer = new UserSerializer();
        $userRepository = new UserRepository($dbConnector, $userSerializer);
        $authService = new AuthService();
        $authTokenRepository = new AuthTokenRepository($dbConnector);
        $tokenManager = new TokenManager($authTokenRepository, $_ENV['JWT_KEY'] ?? '');
        $userManager = new UserManager($authService, $userRepository);

        $authMiddleware = new AuthMiddleware($tokenManager, $userManager);
        $authMiddleware->authenticate($request);

        return new UserController($userManager, $userSerializer);
    };

    $createPostController = function (Request $request): PostController {
        $dbConnector = DbConnector::getInstance();
        $userSerializer = new UserSerializer();
        $userRepository = new UserRepository($dbConnector, $userSerializer);
        $authService = new AuthService();
        $authTokenRepository = new AuthTokenRepository($dbConnector);
        $tokenManager = new TokenManager($authTokenRepository, $_ENV['JWT_KEY'] ?? '');
        $userManager = new UserManager($authService, $userRepository);

        $authMiddleware = new AuthMiddleware($tokenManager, $userManager);
        $authMiddleware->authenticate($request);

        $postSerializer = new PostSerializer();
        $postRepository = new PostRepository($dbConnector, $userSerializer, $postSerializer);
        $postService = new PostService();
        $postManager = new PostManager($postService, $postRepository);

        return new PostController($postManager, $postSerializer);
    };

    $router = new Router();

    $router->addRoute('POST', '/auth/login', function (Request $request) use ($createAuthController): JsonResponse {
        $authController = $createAuthController($request);
        return $authController->login($request);
    });

    $router->addRoute('POST', '/auth/register', function (Request $request) use ($createAuthController): JsonResponse {
        $authController = $createAuthController($request);
        return $authController->register($request);
    });

    $router->addRoute('POST', '/auth/logout', function (Request $request) use ($createAuthController): JsonResponse {
        $authController = $createAuthController($request, true);
        return $authController->logout($request);
    });

    $router->addRoute('GET', '/users/me', function (Request $request) use ($createUserController): JsonResponse {
        $userController = $createUserController($request);
        return $userController->getCurrentUser($request);
    });

    $router->addRoute('GET', '/posts', function (Request $request) use ($createPostController): JsonResponse {
        $postController = $createPostController($request);
        return $postController->getPosts($request);
    });

    $router->addRoute('POST', '/posts', function (Request $request) use ($createPostController): JsonResponse {
        $postController = $createPostController($request);
        return $postController->createPost($request);
    });

    $router->addRoute('PUT', '/posts/{id}', function (Request $request, string $id) use ($createPostController): JsonResponse {
        $postController = $createPostController($request);
        return $postController->updatePost($request, $id);
    });

    $router->addRoute('DELETE', '/posts/{id}', function (Request $request, string $id) use ($createPostController): JsonResponse {
        $postController = $createPostController($request);
        return $postController->deletePost($request, $id);
    });

    $request = Request::fromGlobals();
    $response = $router->dispatch($request);
    $response->send();
} catch (Throwable $error) {
    if(class_exists(JsonResponse::class)) {
        $response = JsonResponse::fromException($error);
        $response->send();
    } else {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo '{"error":"server_error"}';
    }
}
