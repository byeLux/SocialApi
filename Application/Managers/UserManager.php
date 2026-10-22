<?php

declare(strict_types=1);

namespace SocialApi\Application\Managers;

use SocialApi\Domain\Interfaces\Managers\IUserManager;
use SocialApi\Domain\Interfaces\Services\IAuthService;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Interfaces\Repositories\IUserRepository;

class UserManager implements IUserManager
{
    public function __construct(
        private IAuthService $authService,
        private IUserRepository $userRepository
    ) {
    }

    public function getCurrentUser(int $userId): User
    {
        $user = $this->userRepository->findOneById($userId);
        $this->authService->ensureAuthenticatedUser($user);

        return $user;
    }
}
