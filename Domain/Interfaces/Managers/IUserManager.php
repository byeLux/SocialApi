<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Managers;

use SocialApi\Domain\Models\User;

interface IUserManager
{
    public function getCurrentUser(int $userId): User;
}
