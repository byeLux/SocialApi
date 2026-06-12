<?php

require_once __DIR__ . '/../../Domain/Entity/Post.php';;

class PostSerializer /*implements SerializerInterface*/
{
    public function deserialize(array $arrayFromDb, User $user): ?Post
    {
        if(!empty($arrayFromDb)) {
            return new Post(
                $arrayFromDb['id_post'], 
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
                "content" => $post->getContent(),
                "creation_date" => $post->getCreationDate(),
                "delete_date" => $post->isDeleted()
            ]; 
        }
        return null;
    }
}