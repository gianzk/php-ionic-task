<?php

namespace TodoApp\Application\Services;

use Firebase\JWT\JWT;
use TodoApp\Infrastructure\Database\UserRepository;
use TodoApp\Domain\Entities\User;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function registerUser(array $data): User
    {
        return $this->userRepository->register($data['name'], $data['email'], $data['password']);
    }

    public function loginUser(array $data): ?User
    {
        return $this->userRepository->login($data['email'], $data['password']);
    }

    /*     private function generateJwt(User $user): string
    {
        $issuedAt = time();
        $ttl = (int) ($_ENV['JWT_TTL'] ?? 3600);
        $expiresAt = $issuedAt + $ttl;
        $jwtSecret = $_ENV['JWT_SECRET'] ?? 'change-this-secret-in-production';

        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'todoapp-api',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
        ];

        return JWT::encode($payload, $jwtSecret, 'HS256');
    } */
}
