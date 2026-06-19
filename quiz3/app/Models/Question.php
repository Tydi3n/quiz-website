<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Question
{
    public const TYPES = [
        'single' => 'Jednokrotny wybor',
        'multiple' => 'Wielokrotny wybor',
        'open' => 'Pytanie otwarte',
        'gap' => 'Uzupelnianie luki',
    ];

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM questions WHERE id = ?');
        $stmt->execute([$id]);
        $question = $stmt->fetch();

        if (!$question) {
            return null;
        }

        $question['answers'] = self::answers((int) $question['id']);

        return $question;
    }

    public static function forQuiz(int $quizId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC');
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$question) {
            $question['answers'] = self::answers((int) $question['id']);
        }

        return $questions;
    }

    public static function answers(int $questionId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM answers WHERE question_id = ? ORDER BY id ASC');
        $stmt->execute([$questionId]);

        return $stmt->fetchAll();
    }

    public static function create(int $quizId, array $data): int
    {
        $stmt = Database::pdo()->prepare('
            INSERT INTO questions (quiz_id, type, question_text, points, correct_text_answer, hint_text)
            VALUES (:quiz_id, :type, :question_text, :points, :correct_text_answer, :hint_text)
        ');
        $stmt->execute([
            'quiz_id' => $quizId,
            'type' => $data['type'],
            'question_text' => trim($data['question_text']),
            'points' => (int) $data['points'],
            'correct_text_answer' => in_array($data['type'], ['open', 'gap'], true) ? trim($data['correct_text_answer']) : null,
            'hint_text' => self::nullableTrimmed($data['hint_text'] ?? ''),
        ]);

        $questionId = (int) Database::pdo()->lastInsertId();
        self::replaceAnswers($questionId, $data);

        return $questionId;
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare('
            UPDATE questions
            SET type = :type, question_text = :question_text, points = :points, correct_text_answer = :correct_text_answer, hint_text = :hint_text
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $id,
            'type' => $data['type'],
            'question_text' => trim($data['question_text']),
            'points' => (int) $data['points'],
            'correct_text_answer' => in_array($data['type'], ['open', 'gap'], true) ? trim($data['correct_text_answer']) : null,
            'hint_text' => self::nullableTrimmed($data['hint_text'] ?? ''),
        ]);

        self::replaceAnswers($id, $data);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM questions WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function nullableTrimmed(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function replaceAnswers(int $questionId, array $data): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM answers WHERE question_id = ?')->execute([$questionId]);

        if (!in_array($data['type'], ['single', 'multiple'], true)) {
            return;
        }

        $answerTexts = $data['answers'] ?? [];
        $correct = $data['correct_answers'] ?? [];
        if (!is_array($correct)) {
            $correct = [$correct];
        }
        $correct = array_map('strval', $correct);

        $stmt = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');
        foreach ($answerTexts as $index => $answerText) {
            $answerText = trim((string) $answerText);
            if ($answerText === '') {
                continue;
            }

            $stmt->execute([$questionId, $answerText, in_array((string) $index, $correct, true) ? 1 : 0]);
        }
    }
}
