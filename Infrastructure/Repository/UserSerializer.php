<?php

require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/Serializer/SerializerInterface.php';

class UserSerializer implements SerializerInterface
{
    public function deserialize(array $arrayFromDb): ?User
    {
        if(!empty($arrayFromDb)) {
            return new User(
                $arrayFromDb['id_user'], 
                $arrayFromDb['username'], 
                $arrayFromDb['password'], 
                $arrayFromDb['role']
            ); 
        }
        return null;
    }
}