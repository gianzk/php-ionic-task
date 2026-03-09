<?php

namespace TodoApp\Application\Controllers;


use TodoApp\Application\Services\UserService;
use Firebase\JWT\JWT;

class UserController
{

    private UserService $userService;
    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? new UserService();
    }
    public function register(): void
    {
        $data = $this->getJsonInput();

        if (empty($data) || empty($data['email']) || empty($data['password'])) {
            $this->sendError('Email and password are required', 400);
        }

        if (empty($data['name'])) {
            $this->sendError('Name is required', 400);
        }
        $userResponse = $this->userService->registerUser($data);

        $this->sendResponse([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $userResponse->toArray()
        ], 201);
    }

    public function login(): void
    {
        $data = $this->getJsonInput();

        if (empty($data) || empty($data['email']) || empty($data['password'])) {
            $this->sendError('Email and password are required', 400);
        }

        $user = $this->userService->loginUser($data);

        if (!$user) {
            $this->sendError('Invalid credentials', 401);
        }

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
            'name' => $user->getName()
        ];

        $token = JWT::encode($payload, $jwtSecret, 'HS256');

        $this->sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $ttl,
                'user' => $user->toArray()
            ]
        ]);
    }

    private function getJsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function sendResponse($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }

    private function sendError($message, $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit();
    }
}
