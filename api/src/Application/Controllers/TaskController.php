<?php

namespace TodoApp\Application\Controllers;

use TodoApp\Application\Services\TaskService;

class TaskController
{
    private TaskService $taskService;

    public function __construct(?TaskService $taskService = null)
    {
        $this->taskService = $taskService ?? new TaskService();
    }

    public function getAllTasks(): void
    {
        $tasks = $this->taskService->getAllTasks();
        $this->sendResponse([
            'success' => true,
            'data' => $tasks,
            'count' => count($tasks)
        ]);
    }

    public function getTaskById(int $id): void
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            $this->sendError('Task not found', 404);
        }

        $this->sendResponse([
            'success' => true,
            'data' => $task
        ]);
    }

    public function createTask(): void
    {
        $data = $this->getJsonInput();

        if (empty($data) || empty($data['title'])) {
            $this->sendError('Title is required', 400);
        }

        $userId = $_SERVER['auth_user_id'] ?? null;
        if (!$userId) {
            $this->sendError('User authentication required', 401);
        }

        try {
            $task = $this->taskService->createTask($userId, $data);
            $this->sendResponse([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);
        } catch (\InvalidArgumentException $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    public function updateTask(int $id): void
    {
        $data = $this->getJsonInput();

        if (empty($data)) {
            $this->sendError('Request body is required', 400);
        }

        try {
            $task = $this->taskService->updateTask($id, $data);

            if (!$task) {
                $this->sendError('Task not found', 404);
            }

            $this->sendResponse([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    public function deleteTask(int $id): void
    {
        $deleted = $this->taskService->deleteTask($id);

        if (!$deleted) {
            $this->sendError('Task not found', 404);
        }

        $this->sendResponse([
            'success' => true,
            'message' => 'Task deleted successfully'
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
