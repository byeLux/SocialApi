<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Security;

interface ITokenManager
{
    public function generate(int $userId): string;
    public function validate(string $token): bool;
    public function invalidate(string $token): bool;
    public function getId(string $token): ?int;
}
