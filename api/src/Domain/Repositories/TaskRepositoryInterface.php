<?php
namespace TodoApp\Domain\Repositories;

use TodoApp\Domain\Entities\Task;

interface TaskRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Task;
    public function save(Task $task): Task;
    public function update(Task $task): Task;
    public function delete(int $id): bool;
    public function findByStatus(bool $completed): array;
}