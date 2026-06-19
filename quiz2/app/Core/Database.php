<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $database = getenv('DB_DATABASE') ?: 'quiz_php';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
                throw new RuntimeException('Niepoprawna nazwa bazy danych.');
            }

            try {
                $serverPdo = new PDO(
                    "mysql:host={$host};port={$port};charset=utf8mb4",
                    $username,
                    $password,
                    self::options()
                );
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                self::$pdo = new PDO(
                    "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                    $username,
                    $password,
                    self::options()
                );
            } catch (PDOException $exception) {
                throw new RuntimeException(
                    'Nie mozna polaczyc sie z MySQL. Wlacz MySQL w XAMPP i sprawdz dane logowania w README.md. Szczegoly: ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        }

        return self::$pdo;
    }

    public static function initialize(): void
    {
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0777, true);
        }

        self::migrate();
        self::seed();
    }

    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private static function migrate(): void
    {
        $statements = [
            <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'user',
    preference_theme VARCHAR(20) NOT NULL DEFAULT 'light',
    preference_language VARCHAR(5) NOT NULL DEFAULT 'pl',
    oauth_provider VARCHAR(50) NULL,
    oauth_id VARCHAR(190) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NOT NULL,
    difficulty VARCHAR(20) NOT NULL,
    time_limit INT UNSIGNED NOT NULL DEFAULT 10,
    image_path VARCHAR(255) NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    is_hidden TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quizzes_user_id (user_id),
    CONSTRAINT fk_quizzes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS quiz_categories (
    quiz_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (quiz_id, category_id),
    INDEX idx_quiz_categories_category_id (category_id),
    CONSTRAINT fk_quiz_categories_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT UNSIGNED NOT NULL,
    type VARCHAR(30) NOT NULL,
    question_text TEXT NOT NULL,
    points INT UNSIGNED NOT NULL DEFAULT 1,
    correct_text_answer TEXT NULL,
    hint_text TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_questions_quiz_id (quiz_id),
    CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_answers_question_id (question_id),
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    quiz_id INT UNSIGNED NOT NULL,
    score INT UNSIGNED NOT NULL,
    max_score INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempts_user_id (user_id),
    INDEX idx_attempts_quiz_id (quiz_id),
    CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS attempt_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    answer_id INT UNSIGNED NULL,
    answer_text TEXT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_attempt_answers_attempt_id (attempt_id),
    INDEX idx_attempt_answers_question_id (question_id),
    INDEX idx_attempt_answers_answer_id (answer_id),
    CONSTRAINT fk_attempt_answers_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_answers_answer FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
            <<<SQL
CREATE TABLE IF NOT EXISTS user_badges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    badge_key VARCHAR(80) NOT NULL,
    badge_name VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_badge (user_id, badge_key),
    INDEX idx_user_badges_user_id (user_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        ];

        $pdo = self::pdo();
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }

        self::ensureExistingColumns();
    }

    private static function ensureExistingColumns(): void
    {
        $pdo = self::pdo();
        $database = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: '');

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $columns = [
            ['questions', 'hint_text', 'ALTER TABLE questions ADD COLUMN hint_text TEXT NULL AFTER correct_text_answer'],
            ['users', 'preference_language', "ALTER TABLE users ADD COLUMN preference_language VARCHAR(5) NOT NULL DEFAULT 'pl' AFTER preference_theme"],
            ['users', 'oauth_provider', 'ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) NULL AFTER preference_language'],
            ['users', 'oauth_id', 'ALTER TABLE users ADD COLUMN oauth_id VARCHAR(190) NULL AFTER oauth_provider'],
        ];

        foreach ($columns as [$table, $column, $sql]) {
            $stmt->execute([$database, $table, $column]);
            if ((int) $stmt->fetchColumn() === 0) {
                $pdo->exec($sql);
            }
        }
    }

    private static function seed(): void
    {
        $pdo = self::pdo();
        $usersCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

        if ($usersCount > 0) {
            return;
        }

        $users = [
            ['Admin', 'admin@example.com', 'admin123', 'admin'],
            ['Autor', 'author@example.com', 'author123', 'author'],
            ['Uczen', 'user@example.com', 'user123', 'user'],
        ];

        $insertUser = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        foreach ($users as [$name, $email, $password, $role]) {
            $insertUser->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        }

        $categories = ['PHP', 'HTML i CSS', 'JavaScript', 'Bazy danych', 'Wiedza ogolna'];
        $insertCategory = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        foreach ($categories as $category) {
            $insertCategory->execute([$category]);
        }

        $authorId = (int) $pdo->query("SELECT id FROM users WHERE role = 'author' LIMIT 1")->fetchColumn();
        $pdo->prepare('
            INSERT INTO quizzes (user_id, title, description, difficulty, time_limit, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ')->execute([
            $authorId,
            'Podstawy PHP',
            'Krotki quiz pokazowy z pytaniami jednokrotnego wyboru, wielokrotnego wyboru i odpowiedzia tekstowa.',
            'easy',
            12,
        ]);

        $quizId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO quiz_categories (quiz_id, category_id) VALUES (?, ?)')->execute([$quizId, 1]);
        $pdo->prepare('INSERT INTO quiz_categories (quiz_id, category_id) VALUES (?, ?)')->execute([$quizId, 4]);

        $questionInsert = $pdo->prepare('INSERT INTO questions (quiz_id, type, question_text, points, correct_text_answer, hint_text) VALUES (?, ?, ?, ?, ?, ?)');
        $answerInsert = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');

        $questionInsert->execute([$quizId, 'single', 'Ktora funkcja w PHP sluzy do bezpiecznego hashowania hasel?', 1, null, 'Ta funkcja tworzy hash hasla zgodny z aktualnymi algorytmami PHP.']);
        $questionId = (int) $pdo->lastInsertId();
        foreach ([['password_hash', 1], ['md5', 0], ['base64_encode', 0], ['crypt_random', 0]] as [$answer, $correct]) {
            $answerInsert->execute([$questionId, $answer, $correct]);
        }

        $questionInsert->execute([$quizId, 'multiple', 'Ktore elementy sa dobrymi praktykami przy formularzach?', 2, null, 'Zastanow sie nad bezpieczenstwem, utrzymaniem danych po bledzie i czytelnymi komunikatami.']);
        $questionId = (int) $pdo->lastInsertId();
        foreach ([['Walidacja po stronie serwera', 1], ['Zapisywanie hasel jawnie', 0], ['Zachowanie wartosci po bledzie', 1], ['Komunikaty o bledach', 1]] as [$answer, $correct]) {
            $answerInsert->execute([$questionId, $answer, $correct]);
        }

        $questionInsert->execute([$quizId, 'gap', 'Uzupelnij: do komunikacji z baza danych w tym projekcie uzywamy klasy ____.', 1, 'PDO', 'To rozszerzenie PHP zapewnia wspolny interfejs dostepu do baz danych.']);

        $questionInsert->execute([$quizId, 'open', 'Jak nazywa sie tablica superglobalna przechowujaca dane sesji?', 1, '$_SESSION', 'Nazwa zaczyna sie od znaku dolara i podkreslenia.']);
    }
}
