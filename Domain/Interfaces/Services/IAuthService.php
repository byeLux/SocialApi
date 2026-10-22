<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Services;

use SocialApi\Domain\Models\User;

interface IAuthService
{
    public function hashPassword(string $password): string;
    public function ensureValidCredentials(?User $user, string $password): void;
    public function ensureAuthenticatedUser(?User $user): void;
    public function ensureValidToken(bool $valid): void;
}
