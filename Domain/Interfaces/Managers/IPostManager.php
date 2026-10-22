<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Managers;

use SocialApi\Domain\Models\User;

interface IPostManager
{
    public function getPosts(int $page): array;
    public function createPost(int $userId, string $content): void;
    public function updatePost(User $user, int $postId, string $content): void;
    public function deletePost(User $user, int $postId): void;
}
