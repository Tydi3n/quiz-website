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
            ->query('
                SELECT
                    u.id,
                    u.username,
                    u.email,
                    u.role,
                    u.preference_theme,
                    u.preference_language,
                    u.oauth_provider,
                    u.created_at,
                    (SELECT COUNT(*) FROM attempts a WHERE a.user_id = u.id) AS attempts_count,
                    (SELECT COUNT(*) FROM user_badges b WHERE b.user_id = u.id) AS badges_count
                FROM users u
                ORDER BY u.created_at DESC
            ')
            ->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, username, email, role, preference_theme, preference_language, oauth_provider, created_at FROM users WHERE id = ?');
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

    public static function findOrCreateSocial(string $provider): int
    {
        $provider = in_array($provider, ['google', 'github', 'facebook'], true) ? $provider : 'google';
        $email = $provider . '_student@example.com';
        $existing = self::findByEmail($email);
        if ($existing) {
            return (int) $existing['id'];
        }

        $name = ucfirst($provider) . ' User';
        $stmt = Database::pdo()->prepare('
            INSERT INTO users (username, email, password_hash, role, oauth_provider, oauth_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $name,
            $email,
            password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            'user',
            $provider,
            'demo-' . $provider,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function updatePreference(int $id, string $theme, string $language = 'pl'): void
    {
        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
        $language = in_array($language, ['pl', 'en'], true) ? $language : 'pl';
        $stmt = Database::pdo()->prepare('UPDATE users SET preference_theme = ?, preference_language = ? WHERE id = ?');
        $stmt->execute([$theme, $language, $id]);
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
