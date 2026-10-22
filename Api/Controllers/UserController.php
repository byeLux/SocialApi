<?php

declare(strict_types=1);

namespace SocialApi\Api\Controllers;

use SocialApi\Api\Request;
use SocialApi\Api\JsonResponse;
use SocialApi\Application\Managers\UserManager;
use SocialApi\Domain\Interfaces\Serializers\IUserSerializer;

class UserController
{
    public function __construct(
        private UserManager $userManager,
        private IUserSerializer $userSerializer
    ) {
    }

    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $this->userManager->getCurrentUser($request->getUser()->getId());

        return new JsonResponse(['user' => $this->userSerializer->serialize($user)]);
    }
}
