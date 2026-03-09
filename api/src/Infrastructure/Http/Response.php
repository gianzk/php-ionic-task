<?php
namespace TodoApp\Infrastructure\Http;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'error' => [
                'message' => $message,
                'status' => $statusCode,
                'timestamp' => date('c')
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(string $message = 'Success', array $data = []): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        self::json($response);
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    public static function badRequest(string $message = 'Bad request'): void
    {
        self::error($message, 400);
    }

    public static function internalServerError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }
}