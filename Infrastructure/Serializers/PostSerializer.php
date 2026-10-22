<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Serializers;

use SocialApi\Domain\Models\Post;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Interfaces\Serializers\IPostSerializer;

class PostSerializer implements IPostSerializer
{
    public function deserialize(array $arrayFromDb, User $user): ?Post
    {
        if(!empty($arrayFromDb)) {
            return new Post(
                (int) $arrayFromDb['post_id'],
                $user,
                $arrayFromDb['content'],
                $arrayFromDb['creation_date'],
                $arrayFromDb['delete_date'] !== null
            );
        }
        return null;
    }

    public function serialize(Post $post): array
    {
        return [
            "id" => $post->getId(),
            "user_id" => $post->getUser()->getId(),
            "username" => $post->getUser()->getUsername(),
            "role" => $post->getUser()->getRole(),
            "content" => $post->getContent(),
            "creation_date" => $post->getCreationDate(),
            "delete_date" => $post->isDeleted()
        ];
    }
}
