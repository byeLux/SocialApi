<?php

declare(strict_types=1);

namespace SocialApi\Api\Controllers;

use SocialApi\Api\Request;
use SocialApi\Api\JsonResponse;
use SocialApi\Domain\Interfaces\Managers\IUserManager;
use SocialApi\Domain\Interfaces\Serializers\IUserSerializer;

class UserController
{
    public function __construct(
        private IUserManager $userManager,
        private IUserSerializer $userSerializer
    ) {
    }

    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $this->userManager->getCurrentUser($request->getUser()->getId());

        return new JsonResponse(['user' => $this->userSerializer->serialize($user)]);
    }
}
