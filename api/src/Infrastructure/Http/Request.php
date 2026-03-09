<?php
namespace TodoApp\Infrastructure\Http;

class Request
{
    private static array $pathParameters = [];

    public static function getJsonBody(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    public static function getQueryParameter(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function getPathParameter(string $key)
    {
        return self::$pathParameters[$key] ?? null;
    }

    public static function setPathParameters(array $parameters): void
    {
        self::$pathParameters = $parameters;
    }

    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function getHeaders(): array
    {
        return getallheaders() ?: [];
    }

    public static function getHeader(string $name, $default = null)
    {
        $headers = self::getHeaders();
        return $headers[$name] ?? $default;
    }
}