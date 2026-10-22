<?php

declare(strict_types=1);

namespace SocialApi\Business\Services;

use SocialApi\Domain\Interfaces\Services\IAuthService;
use UnexpectedValueException;
use SocialApi\Domain\Models\User;

class AuthService implements IAuthService
{
    public function ensureValidCredentials(?User $user, string $password): void
    {
        // Eseguire bcrypt anche per uno username inesistente.
        $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if($user !== null) {
            $hash = $user->getPassword();
        }

        if(!password_verify($password, $hash) || $user === null
            || !in_array($user->getRole(), ['user', 'mod', 'admin'], true)) {
            throw new UnexpectedValueException('invalid_credentials');
        }
    }

    public function ensureAuthenticatedUser(?User $user): void
    {
        if($user === null || !in_array($user->getRole(), ['user', 'mod', 'admin'], true)) {
            throw new UnexpectedValueException('invalid_token');
        }
    }

    public function ensureValidToken(bool $valid): void
    {
        if(!$valid) {
            throw new UnexpectedValueException('invalid_token');
        }
    }
}
