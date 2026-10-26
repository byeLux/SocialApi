<?php

declare(strict_types=1);

namespace SocialApi\Domain\Enums;

enum UserRole: string
{
    case User = 'user';
    case Mod = 'mod';
    case Admin = 'admin';
}
