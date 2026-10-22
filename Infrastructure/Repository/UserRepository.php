<?php

require_once ROOT_PATH . '/Infrastructure/Persistence/DbConnector.php';
require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/../Serializers/UserSerializer.php';
 
class UserRepository {

    private UserSerializer $userSerializer;
    private DbConnector $dbConnector;
    
    public function __construct() 
    {
        $this->userSerializer = new UserSerializer();
        $this->dbConnector = DbConnector::getInstance();
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
            $user = $this->userSerializer->deserialize($result);
            if($user) return $user;
        }
        return null;
    }

    public function findOneById(int $id) : ?user
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

        $statement->bindParam(':id', $id);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
            $user = $this->userSerializer->deserialize($result);
            if($user) return $user;
        }
        return null;
    }

    public function registerNewUser(User $user) 
    {
        $username = $user->getUsername();
        $password = $user->getPassword();
        $role = $user->getRole();

        $statement = $this->dbConnector->prepare("
            INSERT INTO user (username, password, role)
            VALUES (:username, :password, :role)
        ");
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $statement->bindParam(':username', $username);
        $statement->bindParam(':password', $hash);
        $statement->bindParam(':role', $role);
        $statement->execute();
    }

    public function createUser(string $username, string $password): User
    {
        $statement = $this->dbConnector->prepare("
            INSERT INTO user (username, password, role)
            VALUES (:username, :password, 'user')
        ");
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $statement->bindParam(':username', $username);
        $statement->bindParam(':password', $hash);
        $statement->execute();

        return $this->findOneByUsername($username);
    }

    public function getRoleByUserId(int $id): string
    {
        $statement = $this->dbConnector->prepare("
            SELECT role
            FROM user 
            WHERE id = :id
        ");
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetchColumn();
    }
}