<?php

require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/Serializer/SerializerInterface.php';

class UserSerializer implements SerializerInterface
{
    public function deserialize(array $arrayFromDb): ?User
    {
        if(!empty($arrayFromDb)) {
            return new User(
                $arrayFromDb['user_id'], 
                $arrayFromDb['username'], 
                $arrayFromDb['password'], 
                $arrayFromDb['role']
            ); 
        }
        return null;
    }
    
    public function serialize(User $user): ?array
    {
        if($user !== null) {
            return [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "password" => $user->getPassword(),
                "role" => $user->getRole()
            ];
        }
        return null;
    }
}