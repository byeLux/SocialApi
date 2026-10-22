<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Repositories;

use SocialApi\Domain\Models\Post;

interface IPostRepository
{
    public function getTotalPosts(): int;
    /** @return Post[] */
    public function extractPosts(int $page, int $limit): array;
    public function findPostById(int $id): ?Post;
    public function create(int $userId, string $content): void;
    public function delete(Post $post): bool;
    public function updatePost(int $id, string $content): void;
}
