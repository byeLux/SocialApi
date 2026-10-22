<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Repositories;

interface IAuthTokenRepository
{
    public function register(string $tokenHash, int $userId, int $expiresAt): void;
    public function isValid(string $tokenHash, int $userId, int $expiresAt): bool;
    public function delete(string $tokenHash): bool;
}
