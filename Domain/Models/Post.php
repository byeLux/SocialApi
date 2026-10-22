<?php

declare(strict_types=1);

namespace SocialApi\Domain\Models;

class Post
{
    public function __construct(
        private int $id,
        private User $user,
        private string $content,
        private string $creationDate,
        private bool $deleted
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreationDate(): string
    {
        return $this->creationDate;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setContent(string $updatedContent): void
    {
        $this->content = $updatedContent;
    }
}
