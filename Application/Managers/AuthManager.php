<?php

declare(strict_types=1);

namespace SocialApi\Application\Managers;

use SocialApi\Business\Services\AuthService;
use SocialApi\Business\Services\UserService;
use SocialApi\Domain\Interfaces\Repositories\IUserRepository;
use SocialApi\Domain\Interfaces\Security\ITokenManager;

class AuthManager
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
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

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepository->createUser($username, $hash);
    }

    public function logout(string $token): void
    {
        $valid = $this->tokenManager->invalidate($token);
        $this->authService->ensureValidToken($valid);
    }
}
