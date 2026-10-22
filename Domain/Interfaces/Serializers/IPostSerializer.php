<?php
require_once __DIR__ . '/../../Entity/User.php';
require_once __DIR__ . '/../../Entity/Post.php';

interface IPostSerializer
{
    public function deserialize(array $arrayFromDb, User $user): ?Post;
    public function serialize(Post $post): ?array;
}