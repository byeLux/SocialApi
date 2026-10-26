<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Serializers;

use SocialApi\Domain\Models\User;
use SocialApi\Domain\Enums\UserRole;
use ValueError;
use SocialApi\Domain\Interfaces\Serializers\IUserSerializer;

class UserSerializer implements IUserSerializer
{
    public function deserialize(array $arrayFromDb): ?User
    {
        if(!empty($arrayFromDb)) {
            try {
                $role = UserRole::from($arrayFromDb['role']);
            } catch (ValueError $e) {
                return null;
            }

            return new User(
                (int) $arrayFromDb['user_id'],
                $arrayFromDb['username'],
                $arrayFromDb['password'] ?? '',
                $role
            );
        }
        return null;
    }

    public function serialize(User $user): array
    {
        return [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "role" => $user->getRole()->value
        ];
    }
}
