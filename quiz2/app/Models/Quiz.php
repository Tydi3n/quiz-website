<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Quiz
{
    public const DIFFICULTIES = [
        'easy' => 'Latwy',
        'medium' => 'Sredni',
        'hard' => 'Trudny',
    ];

    public static function all(array $filters = [], ?array $viewer = null): array
    {
        $params = [];
        $where = [];

        if (($viewer['role'] ?? null) === 'admin') {
            $where[] = '1 = 1';
        } elseif ($viewer) {
            $where[] = '(q.is_hidden = 0 OR q.user_id = :viewer_id)';
            $params['viewer_id'] = $viewer['id'];
        } else {
            $where[] = 'q.is_hidden = 0';
        }

        if (!empty($filters['search'])) {
            $where[] = '(q.title LIKE :search OR q.description LIKE :search)';
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['difficulty'])) {
            $where[] = 'q.difficulty = :difficulty';
            $params['difficulty'] = $filters['difficulty'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'q.id IN (SELECT quiz_id FROM quiz_categories WHERE category_id = :category_id)';
            $params['category_id'] = (int) $filters['category_id'];
        }

        $orderBy = match ($filters['sort'] ?? 'newest') {
            'popular' => 'attempt_count DESC, q.views DESC, q.created_at DESC',
            'title' => 'q.title ASC',
            'difficulty' => "CASE q.difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END ASC, q.title ASC",
            default => 'q.created_at DESC',
        };

        $sql = '
            SELECT
                q.*,
                u.username AS author_name,
                GROUP_CONCAT(DISTINCT c.name) AS category_names,
                (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS question_count,
                (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id) AS attempt_count
            FROM quizzes q
            JOIN users u ON u.id = q.user_id
            LEFT JOIN quiz_categories qc ON qc.quiz_id = q.id
            LEFT JOIN categories c ON c.id = qc.category_id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY q.id
            ORDER BY ' . $orderBy;

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();
    }

    public static function find(int $id, ?array $viewer = null): ?array
    {
        $quiz = self::findAny($id);
        if (!$quiz) {
            return null;
        }

        if ((int) $quiz['is_hidden'] === 0) {
            return $quiz;
        }

        if (($viewer['role'] ?? null) === 'admin' || (($viewer['id'] ?? null) && (int) $viewer['id'] === (int) $quiz['user_id'])) {
            return $quiz;
        }

        return null;
    }

    public static function findAny(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('
            SELECT
                q.*,
                u.username AS author_name,
                GROUP_CONCAT(DISTINCT c.name) AS category_names,
                (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS question_count,
                (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id) AS attempt_count
            FROM quizzes q
            JOIN users u ON u.id = q.user_id
            LEFT JOIN quiz_categories qc ON qc.quiz_id = q.id
            LEFT JOIN categories c ON c.id = qc.category_id
            WHERE q.id = ?
            GROUP BY q.id
        ');
        $stmt->execute([$id]);
        $quiz = $stmt->fetch();

        return $quiz ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::pdo()->prepare('
            INSERT INTO quizzes (user_id, title, description, difficulty, time_limit, image_path, is_hidden, created_at, updated_at)
            VALUES (:user_id, :title, :description, :difficulty, :time_limit, :image_path, :is_hidden, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'difficulty' => $data['difficulty'],
            'time_limit' => (int) $data['time_limit'],
            'image_path' => $data['image_path'] ?? null,
            'is_hidden' => !empty($data['is_hidden']) ? 1 : 0,
        ]);

        $quizId = (int) Database::pdo()->lastInsertId();
        self::syncCategories($quizId, $data['category_ids'] ?? []);

        return $quizId;
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'title = :title',
            'description = :description',
            'difficulty = :difficulty',
            'time_limit = :time_limit',
            'is_hidden = :is_hidden',
            'updated_at = CURRENT_TIMESTAMP',
        ];
        $params = [
            'id' => $id,
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'difficulty' => $data['difficulty'],
            'time_limit' => (int) $data['time_limit'],
            'is_hidden' => !empty($data['is_hidden']) ? 1 : 0,
        ];

        if (array_key_exists('image_path', $data)) {
            $fields[] = 'image_path = :image_path';
            $params['image_path'] = $data['image_path'];
        }

        $stmt = Database::pdo()->prepare('UPDATE quizzes SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $stmt->execute($params);

        self::syncCategories($id, $data['category_ids'] ?? []);
    }

    public static function delete(int $id): ?string
    {
        $quiz = self::findAny($id);
        if (!$quiz) {
            return null;
        }

        $stmt = Database::pdo()->prepare('DELETE FROM quizzes WHERE id = ?');
        $stmt->execute([$id]);

        return $quiz['image_path'] ?: null;
    }

    public static function setHidden(int $id, bool $hidden): void
    {
        $stmt = Database::pdo()->prepare('UPDATE quizzes SET is_hidden = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$hidden ? 1 : 0, $id]);
    }

    public static function incrementViews(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE quizzes SET views = views + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function categories(): array
    {
        return Database::pdo()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }

    public static function categoryIds(int $quizId): array
    {
        $stmt = Database::pdo()->prepare('SELECT category_id FROM quiz_categories WHERE quiz_id = ?');
        $stmt->execute([$quizId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'category_id'));
    }

    public static function canManage(array $quiz, ?array $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user['role'] === 'admin' || ((int) $quiz['user_id'] === (int) $user['id'] && $user['role'] === 'author');
    }

    private static function syncCategories(int $quizId, array $categoryIds): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM quiz_categories WHERE quiz_id = ?')->execute([$quizId]);

        $categoryIds = array_unique(array_map('intval', $categoryIds));
        $stmt = $pdo->prepare('INSERT INTO quiz_categories (quiz_id, category_id) VALUES (?, ?)');

        foreach ($categoryIds as $categoryId) {
            if ($categoryId > 0) {
                $stmt->execute([$quizId, $categoryId]);
            }
        }
    }
}
