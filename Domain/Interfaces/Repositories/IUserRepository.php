<?php

declare(strict_types=1);

namespace SocialApi\Domain\Interfaces\Repositories;

use SocialApi\Domain\Models\User;

interface IUserRepository
{
    public function findOneByUsername(string $username): ?User;
    public function findOneById(int $id): ?User;
    public function createUser(string $username, string $password): User;
}
