<?php

declare(strict_types=1);

namespace SocialApi\Application\Managers;

use SocialApi\Domain\Interfaces\Managers\IAuthManager;
use SocialApi\Domain\Interfaces\Services\IAuthService;
use SocialApi\Domain\Interfaces\Services\IUserService;
use SocialApi\Domain\Interfaces\Repositories\IUserRepository;
use SocialApi\Domain\Interfaces\Security\ITokenManager;

class AuthManager implements IAuthManager
{
    public function __construct(
        private IAuthService $authService,
        private IUserService $userService,
        private IUserRepository $userRepository,
        private ITokenManager $tokenManager
    ) {
    }

    public function login(string $username, string $password): string
    {
        $username = trim($username);

        $this->userService->validateCredentials($username, $password);

        $user = $this->userRepository->findOneByUsername($username);
        $this->authService->ensureValidCredentials($user, $password);

        return $this->tokenManager->generate($user->getId());
    }

    public function register(string $username, string $password, string $passwordCheck): void
    {
        $username = trim($username);

        $this->userService->validateCredentials($username, $password);
        $this->userService->ensurePasswordsMatch($password, $passwordCheck);

        $user = $this->userRepository->findOneByUsername($username);
        $this->userService->ensureUsernameAvailable($user);

        $hash = $this->authService->hashPassword($password);
        $this->userRepository->createUser($username, $hash);
    }

    public function logout(string $token): void
    {
        $valid = $this->tokenManager->invalidate($token);
        $this->authService->ensureValidToken($valid);
    }
}
