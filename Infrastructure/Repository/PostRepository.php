<?php

require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once __DIR__ . '/../../Domain/Entity/Post.php';
require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/PostSerializer.php';
require_once __DIR__ . '/UserSerializer.php';
 
class PostRepository 
{
    private UserSerializer $userSerializer;
    private PostSerializer $postSerializer;
    private DbConnector $dbConnector;

    public function __construct() 
    {
        $this->userSerializer = new UserSerializer();
        $this->postSerializer = new PostSerializer();
        $this->dbConnector = DbConnector::getInstance();
    }

    public function getLimit(): int 
    { 
        return 10; 
    }

    public function getOffset(int $page): int
    {
        return $this->getLimit() * ($page - 1);
    }

    public function getTotalPosts(): int 
    {
        $statement = $this->dbConnector->prepare('
            SELECT COUNT(*) 
            FROM post 
            WHERE delete_date IS NULL
        ');
        $statement->execute();
        return $statement->fetchColumn();
    }

    public function extractPages(): int
    {
        return (int) ceil($this->getTotalPosts() / $this->getLimit());
    }
 
    public function extractPosts(int $page): ?array
    {
        $pages  = $this->extractPages();
        $limit  = $this->getLimit();
        $offset = $this->getOffset($page);

        $statement = $this->dbConnector->prepare("
            SELECT
                u.id AS user_id,
                u.username,
                u.password,
                u.role,
                p.id AS post_id,
                p.user_id AS post_user_id,
                p.content,
                p.creation_date,
                p.delete_date
            FROM post p JOIN user u 
            ON p.user_id = u.id
            WHERE p.delete_date is NULL
            ORDER BY p.creation_date DESC
            LIMIT ? OFFSET ?
        ");

        $statement->bindParam(1, $limit, PDO::PARAM_INT);
        $statement->bindParam(2, $offset, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $posts = array();

        $deleted = null;

        foreach($result as $post) {
            if($post['delete_date'] === null) {
                $post['delete_date'] = false;
            }
            $post['user'] = $this->userSerializer->deserialize($post);
            array_push($posts, $this->postSerializer->deserialize($post, $this->userSerializer->deserialize($post)));
            $deleted = true;
        }
        
        if($posts != null) {
            return $posts;
        }
        return null;
    }

    public function findPostById(int $id): ?Post
    {
        $statement = $this->dbConnector->prepare("

            SELECT 
                u.id AS user_id,
                u.username,
                u.password,
                u.role,
                p.id AS post_id,
                p.user_id AS post_user_id,
                p.content,
                p.creation_date,
                p.delete_date
            FROM post p 
            JOIN user u ON p.user_id = u.id
            WHERE p.id = :id
        ");
        $statement->bindParam(':id', $id);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        $result['delete_date'] = $result['delete_date'] ?? false;
        $user = $this->userSerializer->deserialize($result);
 
        $post = $this->postSerializer->deserialize($result, $user);
        if($post != null) {
            return $post;
        }
        return null;
    }

    public function create(int $userId, string $content): void
    {
        $statement = $this->dbConnector->prepare("
            INSERT INTO `post` (`user_id`, `content`) 
            VALUES (:user_id, :content);
        ");
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':content', $content);
        $statement->execute(); 
    }

    public function delete(Post $post) 
    {
        $id = $post->getId();
    
        $statement = $this->dbConnector->prepare('
            UPDATE post
            SET delete_date = NOW()
            WHERE id = :id
        ');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
                 
        // get del modello aggiornato

        $statement = $this->dbConnector->prepare('
            SELECT 
                u.id AS user_id,
                u.username,
                u.password,
                u.role,
                p.id AS post_id,
                p.user_id AS post_user_id,
                p.content,
                p.creation_date,
                p.delete_date
            FROM post p 
            JOIN user u ON p.user_id = u.id
            WHERE p.id = :id
        ');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $result['delete_date'] = $result['delete_date'] ?? false;
        $user = $this->userSerializer->deserialize($result);

        // return modello
        return $this->postSerializer->deserialize(
            $result,
            $user
        );
    }

    public function updatePost(int $id, string $content): void
    {
        $statement = $this->dbConnector->prepare('
            UPDATE post
            SET content = :content
            WHERE id = :id
        ');
        $statement->bindParam(':content', $content);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}