<?php

namespace TodoApp\Application\Services;

use TodoApp\Domain\Entities\Task;
use TodoApp\Domain\Repositories\TaskRepositoryInterface;
use TodoApp\Infrastructure\Database\TaskRepository;

class TaskService
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(?TaskRepositoryInterface $taskRepository = null)
    {
        $this->taskRepository = $taskRepository ?? new TaskRepository();
    }

    public function getAllTasks(): array
    {
        return array_map(fn(Task $task) => $task->toArray(), $this->taskRepository->findAll());
    }

    public function getTaskById(int $id): ?array
    {
        $task = $this->taskRepository->findById($id);
        return $task ? $task->toArray() : null;
    }

    public function createTask(int $userId, array $data): array
    {
        $this->validateTaskData($data);

        $task = new Task(
            $userId,
            $data['title'],
            $data['description'] ?? null,
            (bool) ($data['completed'] ?? false)
        );

        $savedTask = $this->taskRepository->save($task);

        return $savedTask->toArray();
    }

    public function updateTask(int $id, array $data): ?array
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return null;
        }

        if (isset($data['title'])) {
            $this->validateTitle($data['title']);
            $task->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (isset($data['completed'])) {
            $task->setCompleted((bool) $data['completed']);
        }

        $updatedTask = $this->taskRepository->update($task);

        return $updatedTask->toArray();
    }

    public function deleteTask(int $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return false;
        }

        return $this->taskRepository->delete($id);
    }

    public function getTasksByStatus(bool $completed): array
    {
        return array_map(
            fn(Task $task) => $task->toArray(),
            $this->taskRepository->findByStatus($completed)
        );
    }

    public function toggleTaskStatus(int $id): ?array
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return null;
        }

        $task->setCompleted(!$task->isCompleted());
        $updatedTask = $this->taskRepository->update($task);

        return $updatedTask->toArray();
    }

    private function validateTaskData(array $data): void
    {
        if (!isset($data['title']) || empty(trim($data['title']))) {
            throw new \InvalidArgumentException('Title is required and cannot be empty');
        }

        $this->validateTitle($data['title']);
    }

    private function validateTitle(string $title): void
    {
        if (strlen(trim($title)) < 1) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) > 255) {
            throw new \InvalidArgumentException('Title cannot exceed 255 characters');
        }
    }
}
