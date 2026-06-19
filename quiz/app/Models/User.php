<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public const ROLES = ['user', 'author', 'admin'];

    public static function all(): array
    {
        return Database::pdo()
            ->query('SELECT id, username, email, role, preference_theme, created_at FROM users ORDER BY created_at DESC')
            ->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, username, email, role, preference_theme, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([mb_strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return self::find((int) $user['id']);
    }

    public static function create(array $data): int
    {
        $role = in_array($data['role'] ?? 'user', ['user', 'author'], true) ? $data['role'] : 'user';

        $stmt = Database::pdo()->prepare('
            INSERT INTO users (username, email, password_hash, role)
            VALUES (:username, :email, :password_hash, :role)
        ');
        $stmt->execute([
            'username' => trim($data['username']),
            'email' => mb_strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function updatePreference(int $id, string $theme): void
    {
        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
        $stmt = Database::pdo()->prepare('UPDATE users SET preference_theme = ? WHERE id = ?');
        $stmt->execute([$theme, $id]);
    }

    public static function updateRole(int $id, string $role): void
    {
        if (!in_array($role, self::ROLES, true)) {
            return;
        }

        $stmt = Database::pdo()->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
