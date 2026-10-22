<?php

declare(strict_types=1);

namespace SocialApi\Application\Managers;

use SocialApi\Business\Services\AuthService;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Interfaces\Repositories\IUserRepository;

class UserManager
{
    public function __construct(
        private AuthService $authService,
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
