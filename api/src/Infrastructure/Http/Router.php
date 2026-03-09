<?php

namespace TodoApp\Infrastructure\Http;

class Router
{
    private array $routes = [];

    public function register(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToPattern($path)
        ];
    }

    public function handle(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract path parameters
                $parameters = [];
                if (isset($route['paramNames'])) {
                    foreach ($route['paramNames'] as $index => $name) {
                        $parameters[$name] = $matches[$index + 1] ?? null;
                    }
                }

                Request::setPathParameters($parameters);

                // Call the handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    $controller->$method();
                } else {
                    $handler();
                }
                return;
            }
        }

        Response::error('Route not found', 404);
    }

    private function convertToPattern(string $path): string
    {
        $paramNames = [];

        // Convert {param} to regex capture groups
        $pattern = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^/]+)';
        }, $path);

        // Store parameter names for later use
        if (!empty($paramNames)) {
            $lastRoute = &$this->routes[count($this->routes) - 1];
            $lastRoute['paramNames'] = $paramNames;
        }

        return '#^' . $pattern . '$#';
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
