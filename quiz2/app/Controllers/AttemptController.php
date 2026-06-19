<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;

class AttemptController
{
    public function take(): void
    {
        require_login();

        $quiz = Quiz::find((int) ($_GET['id'] ?? 0), current_user());
        if (!$quiz) {
            http_response_code(404);
            view('errors/404');
            return;
        }

        $questions = Question::forQuiz((int) $quiz['id']);
        if (!$questions) {
            flash('warning', 'Ten quiz nie ma jeszcze pytan.');
            redirect_to('quizzes.show', ['id' => $quiz['id']]);
        }

        view('attempts/take', compact('quiz', 'questions'));
    }

    public function submit(): void
    {
        require_login();

        $quiz = Quiz::find((int) ($_POST['quiz_id'] ?? 0), current_user());
        if (!$quiz) {
            http_response_code(404);
            view('errors/404');
            return;
        }

        $answers = (array) ($_POST['answers'] ?? []);
        $attemptId = Attempt::createFromSubmission((int) $quiz['id'], (int) current_user()['id'], $answers);

        flash('success', 'Quiz zostal sprawdzony.');
        redirect_to('attempts.result', ['id' => $attemptId]);
    }

    public function export(): void
    {
        require_login();

        $user = current_user();
        $quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : null;
        $exportUserId = $user['role'] === 'admin' || user_has_role('author') ? null : (int) $user['id'];
        $rows = Attempt::exportRows($exportUserId, $quizId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=wyniki_quizow.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Uzytkownik', 'Email', 'Quiz', 'Punkty', 'Max', 'Procent', 'Data']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['username'],
                $row['email'],
                $row['quiz_title'],
                $row['score'],
                $row['max_score'],
                $row['percent_result'],
                $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function result(): void
    {
        require_login();

        $attempt = Attempt::find((int) ($_GET['id'] ?? 0));
        if (!$attempt) {
            http_response_code(404);
            view('errors/404');
            return;
        }

        $user = current_user();
        if ((int) $attempt['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin') {
            http_response_code(403);
            view('errors/403');
            return;
        }

        $quiz = Quiz::findAny((int) $attempt['quiz_id']);
        $questions = Question::forQuiz((int) $attempt['quiz_id']);
        $attemptAnswers = Attempt::answersByQuestion((int) $attempt['id']);

        view('attempts/result', compact('attempt', 'quiz', 'questions', 'attemptAnswers'));
    }
}
