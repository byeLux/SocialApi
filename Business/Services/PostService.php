<?php

declare(strict_types=1);

namespace SocialApi\Business\Services;

use SocialApi\Domain\Interfaces\Services\IPostService;
use DomainException;
use InvalidArgumentException;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Enums\UserRole;

class PostService implements IPostService
{
    private const POSTS_PER_PAGE = 10;

    public function getPostsPerPage(): int
    {
        return self::POSTS_PER_PAGE;
    }

    public function validateContent(string $content): string
    {
        $content = trim($content);

        if($content === '' || mb_strlen($content, 'UTF-8') > 300) {
            throw new InvalidArgumentException('invalid_content');
        }

        return $content;
    }

    public function ensureCanUpdate(User $user): void
    {
        if(!in_array($user->getRole(), [UserRole::Mod, UserRole::Admin], true)) {
            throw new DomainException('forbidden');
        }
    }

    public function ensureCanDelete(User $user): void
    {
        if($user->getRole() !== UserRole::Admin) {
            throw new DomainException('forbidden');
        }
    }

    public function ensureValidPage(int $page, int $limit): void
    {
        if($page < 1 || $limit < 1 || $page > intdiv(PHP_INT_MAX, $limit)) {
            throw new InvalidArgumentException('invalid_page');
        }
    }
}
