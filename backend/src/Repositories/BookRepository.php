<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BookRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(?string $query = null): array
    {
        $query = trim((string)$query);
        if ($query !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT id, title, author, year, genre, created_by, created_at, updated_at
                 FROM books
                 WHERE title LIKE :q OR author LIKE :q
                 ORDER BY id'
            );
            $stmt->execute([':q' => '%' . $query . '%']);

            return $stmt->fetchAll();
        }

        return $this->pdo
            ->query('SELECT id, title, author, year, genre, created_by, created_at, updated_at FROM books ORDER BY id')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, author, year, genre, created_by, created_at, updated_at FROM books WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data, int $createdBy): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO books (title, author, year, genre, created_by)
             VALUES (:title, :author, :year, :genre, :created_by)'
        );
        $stmt->execute([
            ':title' => trim((string)$data['title']),
            ':author' => trim((string)$data['author']),
            ':year' => (int)$data['year'],
            ':genre' => trim((string)($data['genre'] ?? 'Uncategorised')),
            ':created_by' => $createdBy,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE books SET title = :title, author = :author, year = :year, genre = :genre WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':title' => trim((string)$data['title']),
            ':author' => trim((string)$data['author']),
            ':year' => (int)$data['year'],
            ':genre' => trim((string)($data['genre'] ?? 'Uncategorised')),
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
