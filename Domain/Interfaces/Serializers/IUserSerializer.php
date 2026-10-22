<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Serializers;

use SocialApi\Domain\Models\User;

interface IUserSerializer
{
    public function deserialize(array $arrayFromDb): ?User;
    public function serialize(User $user): array;
}
