<?php

declare(strict_types=1);

namespace SocialApi\Api\Controllers;

use SocialApi\Api\Request;
use SocialApi\Api\JsonResponse;
use SocialApi\Domain\Interfaces\Managers\IAuthManager;

class AuthController
{
    public function __construct(
        private IAuthManager $authManager
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $body = $request->getJson();
        $username = $request->getString($body, 'username');
        $password = $request->getString($body, 'password');

        $token = $this->authManager->login($username, $password);

        return new JsonResponse(['token' => $token]);
    }

    public function register(Request $request): JsonResponse
    {
        $body = $request->getJson();
        $username = $request->getString($body, 'username');
        $password = $request->getString($body, 'password');
        $passwordCheck = $request->getString($body, 'password-check');

        $this->authManager->register($username, $password, $passwordCheck);

        return new JsonResponse(['success' => true], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authManager->logout($request->getToken());

        return new JsonResponse(['logout' => true]);
    }
}
