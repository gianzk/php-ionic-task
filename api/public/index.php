<?php
require_once __DIR__ . '/../vendor/autoload.php';

use TodoApp\Application\Controllers\TaskController;
use TodoApp\Application\Controllers\UserController;
use TodoApp\Application\Middleware\AuthMiddleware;
use Bramus\Router\Router;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $taskController = new TaskController();
    $userController = new UserController();
    $authMiddleware = new AuthMiddleware();

    $router = new Router();
    $router->setBasePath('/api');

    $router->post('/register', [$userController, 'register']);
    $router->post('/login', [$userController, 'login']);

    $router->before('GET', '/tasks', [$authMiddleware, 'handle']);
    $router->get('/tasks', [$taskController, 'getAllTasks']);
    $router->before('POST', '/tasks', [$authMiddleware, 'handle']);
    $router->post('/tasks', [$taskController, 'createTask']);
    $router->before('PUT', '/tasks/(\d+)', [$authMiddleware, 'handle']);
    $router->put('/tasks/(\d+)', [$taskController, 'updateTask']);
    $router->before('DELETE', '/tasks/(\d+)', [$authMiddleware, 'handle']);
    $router->delete('/tasks/(\d+)', [$taskController, 'deleteTask']);

    $router->set404(function () {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Route not found',
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
    });

    $router->run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal server error',
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
