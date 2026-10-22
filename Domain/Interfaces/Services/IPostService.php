<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Services;

use SocialApi\Domain\Models\User;

interface IPostService
{
    public function getPostsPerPage(): int;
    public function validateContent(string $content): string;
    public function authorizeMutation(User $user): void;
    public function ensureValidPage(int $page, int $limit): void;
}
