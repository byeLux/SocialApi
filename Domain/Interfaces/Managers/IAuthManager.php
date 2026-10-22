<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Managers;

interface IAuthManager
{
    public function login(string $username, string $password): string;
    public function register(string $username, string $password, string $passwordCheck): void;
    public function logout(string $token): void;
}
