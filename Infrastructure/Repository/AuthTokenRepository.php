<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Repository;

use PDO;
use PDOException;
use SocialApi\Domain\Interfaces\Repositories\IAuthTokenRepository;
use SocialApi\Infrastructure\Persistence\DbConnector;

class AuthTokenRepository implements IAuthTokenRepository
{
    public function __construct(
        private DbConnector $dbConnector
    ) {
    }

    public function register(string $tokenHash, int $userId, int $expiresAt): void
    {
        $statement = $this->dbConnector->prepare('
            INSERT INTO auth_token (token_hash, user_id, expires_at)
            VALUES (:token_hash, :user_id, :expires_at)
        ');
        $statement->bindParam(':token_hash', $tokenHash);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':expires_at', $expiresAt, PDO::PARAM_INT);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            // Token identici nello stesso secondo corrispondono alla stessa sessione.
            if(($e->errorInfo[1] ?? null) === 1062
                && $this->isValid($tokenHash, $userId, $expiresAt)) {
                return;
            }
            throw $e;
        }
    }

    public function isValid(string $tokenHash, int $userId, int $expiresAt): bool
    {
        $now = time();
        $statement = $this->dbConnector->prepare('
            SELECT token_hash
            FROM auth_token
            WHERE token_hash = :token_hash
                AND user_id = :user_id
                AND expires_at = :expires_at
                AND expires_at > :now
        ');
        $statement->bindParam(':token_hash', $tokenHash);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':expires_at', $expiresAt, PDO::PARAM_INT);
        $statement->bindParam(':now', $now, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchColumn() !== false;
    }

    public function delete(string $tokenHash): bool
    {
        $statement = $this->dbConnector->prepare('
            DELETE FROM auth_token
            WHERE token_hash = :token_hash
        ');
        $statement->bindParam(':token_hash', $tokenHash);
        $statement->execute();

        return $statement->rowCount() === 1;
    }
}
