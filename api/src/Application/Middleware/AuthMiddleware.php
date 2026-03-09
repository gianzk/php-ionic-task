<?php

namespace TodoApp\Application\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function handle(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->sendUnauthorized('Missing or invalid Authorization header');
        }

        $token = trim($matches[1]);
        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';

        if ($jwtSecret === '') {
            $this->sendUnauthorized('JWT secret is not configured');
        }

        try {
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            $_SERVER['auth_user_id'] = $decoded->sub ?? null;
            $_SERVER['auth_user_email'] = $decoded->email ?? null;
        } catch (\Throwable $e) {
            $this->sendUnauthorized('Invalid or expired token');
        }
    }

    private function sendUnauthorized(string $message): void
    {
        http_response_code(401);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit();
    }
}
