<?php

declare(strict_types=1);

namespace SocialApi\Business\Services;

use InvalidArgumentException;
use OverflowException;
use SocialApi\Domain\Models\User;

class UserService
{
    public function validateCredentials(string $username, string $password): void
    {
        if($username === '') {
            throw new InvalidArgumentException('missing_username');
        }

        if(mb_strlen($username, 'UTF-8') > 25) {
            throw new InvalidArgumentException('invalid_username');
        }

        if($password === '') {
            throw new InvalidArgumentException('missing_password');
        }

        // bcrypt ignora i byte oltre il limite di 72.
        if(strlen($password) > 72 || str_contains($password, "\0")) {
            throw new InvalidArgumentException('invalid_password');
        }
    }

    public function ensurePasswordsMatch(string $password, string $passwordCheck): void
    {
        if($password !== $passwordCheck) {
            throw new InvalidArgumentException('password_mismatch');
        }
    }

    public function ensureUsernameAvailable(?User $user): void
    {
        if($user !== null) {
            throw new OverflowException('user_already_exists');
        }
    }
}
