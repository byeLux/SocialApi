<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Services;

use SocialApi\Domain\Models\User;

interface IUserService
{
    public function validateCredentials(string $username, string $password): void;
    public function ensurePasswordsMatch(string $password, string $passwordCheck): void;
    public function ensureUsernameAvailable(?User $user): void;
}
