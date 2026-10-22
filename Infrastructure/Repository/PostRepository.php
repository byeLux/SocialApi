<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Repository;

use PDO;
use SocialApi\Domain\Models\Post;
use SocialApi\Domain\Interfaces\Repositories\IPostRepository;
use SocialApi\Domain\Interfaces\Serializers\IPostSerializer;
use SocialApi\Domain\Interfaces\Serializers\IUserSerializer;
use SocialApi\Infrastructure\Persistence\DbConnector;

class PostRepository implements IPostRepository
{
    public function __construct(
        private DbConnector $dbConnector,
        private IUserSerializer $userSerializer,
        private IPostSerializer $postSerializer
    ) {
    }

    public function getTotalPosts(): int
    {
        $statement = $this->dbConnector->prepare('
            SELECT COUNT(*)
            FROM post
            WHERE delete_date IS NULL
        ');
        $statement->execute();
        return (int) $statement->fetchColumn();
    }

    /** @return Post[] */
    public function extractPosts(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $statement = $this->dbConnector->prepare("
            SELECT
                u.id AS user_id,
                u.username,
                u.role,
                p.id AS post_id,
                p.content,
                p.creation_date,
                p.delete_date
            FROM post p JOIN user u
            ON p.user_id = u.id
            WHERE p.delete_date IS NULL
            ORDER BY p.creation_date DESC, p.id DESC
            LIMIT ? OFFSET ?
        ");

        $statement->bindParam(1, $limit, PDO::PARAM_INT);
        $statement->bindParam(2, $offset, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $posts = array();

        foreach($result as $post) {
            $user = $this->userSerializer->deserialize($post);
            $posts[] = $this->postSerializer->deserialize($post, $user);
        }

        return $posts;
    }

    public function findPostById(int $id): ?Post
    {
        $statement = $this->dbConnector->prepare("
            SELECT
                u.id AS user_id,
                u.username,
                u.role,
                p.id AS post_id,
                p.content,
                p.creation_date,
                p.delete_date
            FROM post p
            JOIN user u ON p.user_id = u.id
            WHERE p.id = :id AND p.delete_date IS NULL
        ");
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if(!$result) {
            return null;
        }

        $user = $this->userSerializer->deserialize($result);
        return $this->postSerializer->deserialize($result, $user);
    }

    public function create(int $userId, string $content): void
    {
        $statement = $this->dbConnector->prepare("
            INSERT INTO post (user_id, content)
            VALUES (:user_id, :content)
        ");
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':content', $content);
        $statement->execute();
    }

    public function delete(Post $post): bool
    {
        $id = $post->getId();

        $statement = $this->dbConnector->prepare('
            UPDATE post
            SET delete_date = NOW()
            WHERE id = :id AND delete_date IS NULL
        ');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() === 1;
    }

    public function updatePost(int $id, string $content): void
    {
        $statement = $this->dbConnector->prepare('
            UPDATE post
            SET content = :content
            WHERE id = :id AND delete_date IS NULL
        ');
        $statement->bindParam(':content', $content);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}
