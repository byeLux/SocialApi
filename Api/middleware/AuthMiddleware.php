<?php

declare(strict_types=1);

namespace SocialApi\Api\middleware;

use RuntimeException;
use SocialApi\Api\Request;
use SocialApi\Domain\Interfaces\Managers\IUserManager;
use SocialApi\Domain\Interfaces\Security\ITokenManager;

class AuthMiddleware
{
    public function __construct(
        private ITokenManager $tokenManager,
        private IUserManager $userManager
    ) {
    }

    public function authenticate(Request $request): void
    {
        $authHeader = $request->getHeader('Authorization');
        if($authHeader === null || $authHeader === '') {
            throw new RuntimeException('missing_auth_headers', 401);
        }

        if(!preg_match('/^Bearer[ \t]+([^\s]+)$/iD', $authHeader, $matches)) {
            throw new RuntimeException('invalid_auth_format', 401);
        }

        $token = $matches[1];
        $userId = $this->tokenManager->getId($token);

        if($userId === null) {
            throw new RuntimeException('invalid_token', 401);
        }

        $user = $this->userManager->getCurrentUser($userId);

        $request->authenticate($user, $token);
    }
}
