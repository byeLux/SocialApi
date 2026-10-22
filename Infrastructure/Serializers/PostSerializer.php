<?php

require_once __DIR__ . '/../../Domain/Entity/Post.php';
require_once __DIR__ . '/../../Domain/Entity/User.php';
require_once __DIR__ . '/../../Domain/Interfaces/Serializers/IPostSerializer.php';

class PostSerializer implements IPostSerializer
{
    public function deserialize(array $arrayFromDb, User $user): ?Post
    {
        if(!empty($arrayFromDb)) {
            return new Post(
                $arrayFromDb['post_id'], 
                $user, 
                $arrayFromDb['content'], 
                $arrayFromDb['creation_date'], 
                $arrayFromDb['delete_date']
            ); 
        }
        return null;
    }

    public function serialize(Post $post): ?array
    {
        if($post !== null) {
            return [
                "id" => $post->getId(),
                "user_id" => $post->getUser()->getId(),
                "username" => $post->getUser()->getUsername(),
                "role" => $post->getUser()->getRole(),
                "content" => $post->getContent(),
                "creation_date" => $post->getCreationDate(),
                "delete_date" => $post->isDeleted()
            ]; 
        }
        return null;
    }
}