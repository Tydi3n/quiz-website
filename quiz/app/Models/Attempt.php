<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Attempt
{
    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM attempts')->fetchColumn();
    }

    public static function createFromSubmission(int $quizId, int $userId, array $submission): int
    {
        $pdo = Database::pdo();
        $questions = Question::forQuiz($quizId);
        $score = 0;
        $maxScore = 0;
        $graded = [];

        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            $points = (int) $question['points'];
            $maxScore += $points;
            $isCorrect = false;
            $selectedAnswerIds = [];
            $textAnswer = null;

            if ($question['type'] === 'single') {
                $selectedAnswerIds = !empty($submission[$questionId]) ? [(int) $submission[$questionId]] : [];
                $correctIds = self::correctAnswerIds($question);
                $isCorrect = count($selectedAnswerIds) === 1 && $selectedAnswerIds === $correctIds;
            }

            if ($question['type'] === 'multiple') {
                $selectedAnswerIds = array_map('intval', (array) ($submission[$questionId] ?? []));
                sort($selectedAnswerIds);
                $correctIds = self::correctAnswerIds($question);
                $isCorrect = $selectedAnswerIds === $correctIds && $selectedAnswerIds !== [];
            }

            if (in_array($question['type'], ['open', 'gap'], true)) {
                $textAnswer = trim((string) ($submission[$questionId] ?? ''));
                $isCorrect = normalize_text_answer($textAnswer) === normalize_text_answer((string) $question['correct_text_answer']);
            }

            if ($isCorrect) {
                $score += $points;
            }

            $graded[] = [
                'question_id' => $questionId,
                'answer_ids' => $selectedAnswerIds,
                'text_answer' => $textAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO attempts (user_id, quiz_id, score, max_score) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $quizId, $score, $maxScore]);
        $attemptId = (int) $pdo->lastInsertId();

        $answerStmt = $pdo->prepare('
            INSERT INTO attempt_answers (attempt_id, question_id, answer_id, answer_text, is_correct)
            VALUES (?, ?, ?, ?, ?)
        ');

        foreach ($graded as $item) {
            if ($item['answer_ids'] === []) {
                $answerStmt->execute([$attemptId, $item['question_id'], null, $item['text_answer'], $item['is_correct'] ? 1 : 0]);
                continue;
            }

            foreach ($item['answer_ids'] as $answerId) {
                $answerStmt->execute([$attemptId, $item['question_id'], $answerId, null, $item['is_correct'] ? 1 : 0]);
            }
        }

        $pdo->commit();

        return $attemptId;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('
            SELECT a.*, q.title AS quiz_title, u.username
            FROM attempts a
            JOIN quizzes q ON q.id = a.quiz_id
            JOIN users u ON u.id = a.user_id
            WHERE a.id = ?
        ');
        $stmt->execute([$id]);
        $attempt = $stmt->fetch();

        return $attempt ?: null;
    }

    public static function answersByQuestion(int $attemptId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM attempt_answers WHERE attempt_id = ?');
        $stmt->execute([$attemptId]);
        $grouped = [];

        foreach ($stmt->fetchAll() as $answer) {
            $grouped[(int) $answer['question_id']][] = $answer;
        }

        return $grouped;
    }

    public static function recentForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare('
            SELECT a.*, q.title AS quiz_title
            FROM attempts a
            JOIN quizzes q ON q.id = a.quiz_id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
            LIMIT 10
        ');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    private static function correctAnswerIds(array $question): array
    {
        $ids = [];
        foreach ($question['answers'] as $answer) {
            if ((int) $answer['is_correct'] === 1) {
                $ids[] = (int) $answer['id'];
            }
        }

        sort($ids);

        return $ids;
    }
}
