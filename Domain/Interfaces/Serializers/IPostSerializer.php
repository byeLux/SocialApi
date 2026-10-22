<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Serializers;

use SocialApi\Domain\Models\Post;
use SocialApi\Domain\Models\User;

interface IPostSerializer
{
    public function deserialize(array $arrayFromDb, User $user): ?Post;
    public function serialize(Post $post): array;
}
