<?php

namespace TodoApp\Infrastructure\Database;

use TodoApp\Domain\Entities\Task;
use TodoApp\Domain\Repositories\TaskRepositoryInterface;
use PDO;

class TaskRepository implements TaskRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks ORDER BY created_at DESC");
        $stmt->execute();

        $tasks = [];
        while ($row = $stmt->fetch()) {
            $tasks[] = Task::fromArray($row);
        }

        return $tasks;
    }

    public function findById(int $id): ?Task
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch();

        return $row ? Task::fromArray($row) : null;
    }

    public function save(Task $task): Task
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tasks (user_id, title, description, completed, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $task->getUserId(),
            $task->getTitle(),
            $task->getDescription(),
            $task->isCompleted() ? 1 : 0,
            $task->getCreatedAt()->format('Y-m-d H:i:s'),
            $task->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $task->setId((int) $this->db->lastInsertId());

        return $task;
    }

    public function update(Task $task): Task
    {
        $stmt = $this->db->prepare(
            "UPDATE tasks 
             SET title = ?, description = ?, completed = ?, updated_at = ? 
             WHERE id = ?"
        );

        $stmt->execute([
            $task->getTitle(),
            $task->getDescription(),
            $task->isCompleted() ? 1 : 0,
            $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            $task->getId()
        ]);

        return $task;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByStatus(bool $completed): array
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE completed = ? ORDER BY created_at DESC");
        $stmt->execute([$completed ? 1 : 0]);

        $tasks = [];
        while ($row = $stmt->fetch()) {
            $tasks[] = Task::fromArray($row);
        }

        return $tasks;
    }
}
