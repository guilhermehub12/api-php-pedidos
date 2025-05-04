<?php

namespace Src\Models;

use PDO;

class Carro
{
    private PDO $pdo;
    private string $table = 'carros';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $carro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $carro ?: null;
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $sets = [];
        foreach(array_keys($data) as $field) {
            $sets[] = "$field = :$field";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function delete(int $id): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}