<?php
require_once __DIR__ . '/../../Entity/User.php';

interface IUserSerializer
{
    public function deserialize(array $arrayFromDb): ?User;
    public function serialize(User $user): ?array;
}