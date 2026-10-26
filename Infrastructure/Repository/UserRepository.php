<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Repository;

use PDO;
use PDOException;
use OverflowException;
use RuntimeException;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Enums\UserRole;
use SocialApi\Domain\Interfaces\Repositories\IUserRepository;
use SocialApi\Domain\Interfaces\Serializers\IUserSerializer;
use SocialApi\Infrastructure\Persistence\DbConnector;

class UserRepository implements IUserRepository
{
    public function __construct(
        private DbConnector $dbConnector,
        private IUserSerializer $userSerializer
    ) {
    }

    public function findOneByUsername(string $username): ?User
    {
        $statement = $this->dbConnector->prepare("
            SELECT
                id AS user_id,
                password,
                role,
                username
            FROM user
            WHERE username = :username
        ");

        $statement->bindParam(':username', $username);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
            return $this->userSerializer->deserialize($result);
        }
        return null;
    }

    public function findOneById(int $id): ?User
    {
        $statement = $this->dbConnector->prepare("
            SELECT
                id AS user_id,
                password,
                role,
                username
            FROM user
            WHERE id = :id
        ");

        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
            return $this->userSerializer->deserialize($result);
        }
        return null;
    }

    public function createUser(string $username, string $password): User
    {
        $statement = $this->dbConnector->prepare("
            INSERT INTO user (username, password)
            VALUES (:username, :password)
        ");

        $statement->bindParam(':username', $username);
        $statement->bindParam(':password', $password);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            // Il vincolo UNIQUE esistente protegge anche le registrazioni concorrenti.
            if(($e->errorInfo[1] ?? null) === 1062) {
                throw new OverflowException('user_already_exists');
            }
            throw $e;
        }

        $user = $this->findOneByUsername($username);
        if($user === null) {
            throw new RuntimeException('User could not be retrieved.');
        }
        return $user;
    }
}
