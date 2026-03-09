<?php

namespace TodoApp\Domain\Repositories;

use TodoApp\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function register(string $name, string $email, string $password): User;
    public function login(string $email, string $password): ?User;
}
