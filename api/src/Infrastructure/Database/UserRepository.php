<?php

namespace TodoApp\Infrastructure\Database;

use TodoApp\Domain\Entities\User;
use TodoApp\Domain\Repositories\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function register(string $name, string $email, string $password): User
    {
        if ($this->emailExists($email)) {
            throw new \InvalidArgumentException('Email already registered');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?)"
        );

        $now = new \DateTime();
        $stmt->execute([
            $name,
            $email,
            $hashedPassword,
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s')
        ]);

        $userId = (int) $this->db->lastInsertId();

        return new User(
            $name,
            $email,
            $hashedPassword,
            $userId,
            $now,
            $now
        );
    }

    public function login(string $email, string $password): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $userData = $stmt->fetch();

        if (!$userData) {
            return null;
        }

        if (!password_verify($password, $userData['password'])) {
            return null;
        }

        return User::fromArray($userData);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);

        return $stmt->fetchColumn() > 0;
    }
}
