<?php

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $password,
        private string $role
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string 
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
