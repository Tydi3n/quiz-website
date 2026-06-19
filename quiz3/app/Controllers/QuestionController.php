<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Question;
use App\Models\Quiz;

class QuestionController
{
    public function create(): void
    {
        $quiz = $this->managedQuiz();
        $data = $this->defaultFormData();
        $errors = [];
        $mode = 'create';

        view('questions/form', compact('quiz', 'data', 'errors', 'mode'));
    }

    public function store(): void
    {
        $quiz = $this->managedQuiz();
        $data = $this->requestData();
        $errors = $this->validate($data);

        if ($errors) {
            $mode = 'create';
            view('questions/form', compact('quiz', 'data', 'errors', 'mode'));
            return;
        }

        Question::create((int) $quiz['id'], $data);
        flash('success', 'Pytanie zostalo dodane.');
        redirect_to('quizzes.show', ['id' => $quiz['id']]);
    }

    public function edit(): void
    {
        [$quiz, $question] = $this->managedQuestion();
        $data = $this->questionToFormData($question);
        $errors = [];
        $mode = 'edit';

        view('questions/form', compact('quiz', 'question', 'data', 'errors', 'mode'));
    }

    public function update(): void
    {
        [$quiz, $question] = $this->managedQuestion();
        $data = $this->requestData();
        $errors = $this->validate($data);

        if ($errors) {
            $mode = 'edit';
            view('questions/form', compact('quiz', 'question', 'data', 'errors', 'mode'));
            return;
        }

        Question::update((int) $question['id'], $data);
        flash('success', 'Pytanie zostalo zaktualizowane.');
        redirect_to('quizzes.show', ['id' => $quiz['id']]);
    }

    public function delete(): void
    {
        [$quiz, $question] = $this->managedQuestion();
        Question::delete((int) $question['id']);

        flash('success', 'Pytanie zostalo usuniete.');
        redirect_to('quizzes.show', ['id' => $quiz['id']]);
    }

    private function managedQuiz(): array
    {
        require_login();

        $quiz = Quiz::findAny((int) ($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0));
        if (!$quiz) {
            http_response_code(404);
            view('errors/404');
            exit;
        }

        if (!Quiz::canManage($quiz, current_user())) {
            http_response_code(403);
            view('errors/403');
            exit;
        }

        return $quiz;
    }

    private function managedQuestion(): array
    {
        require_login();

        $question = Question::find((int) ($_GET['id'] ?? $_POST['id'] ?? 0));
        if (!$question) {
            http_response_code(404);
            view('errors/404');
            exit;
        }

        $quiz = Quiz::findAny((int) $question['quiz_id']);
        if (!$quiz || !Quiz::canManage($quiz, current_user())) {
            http_response_code(403);
            view('errors/403');
            exit;
        }

        return [$quiz, $question];
    }

    private function defaultFormData(): array
    {
        return [
            'type' => 'single',
            'question_text' => '',
            'points' => 1,
            'answers' => ['', '', '', ''],
            'correct_answers' => [],
            'correct_text_answer' => '',
            'hint_text' => '',
        ];
    }

    private function requestData(): array
    {
        $correct = $_POST['correct_answers'] ?? [];
        if (!is_array($correct)) {
            $correct = [$correct];
        }

        return [
            'type' => (string) ($_POST['type'] ?? 'single'),
            'question_text' => (string) ($_POST['question_text'] ?? ''),
            'points' => (int) ($_POST['points'] ?? 1),
            'answers' => array_values((array) ($_POST['answers'] ?? [])),
            'correct_answers' => array_map('strval', $correct),
            'correct_text_answer' => (string) ($_POST['correct_text_answer'] ?? ''),
            'hint_text' => (string) ($_POST['hint_text'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (!array_key_exists($data['type'], Question::TYPES)) {
            $errors['type'] = 'Wybierz poprawny typ pytania.';
        }

        if (mb_strlen(trim($data['question_text'])) < 5) {
            $errors['question_text'] = 'Tresc pytania musi miec co najmniej 5 znakow.';
        }

        if ($data['points'] < 1 || $data['points'] > 20) {
            $errors['points'] = 'Punkty musza byc od 1 do 20.';
        }

        if (in_array($data['type'], ['single', 'multiple'], true)) {
            $filledAnswers = array_filter($data['answers'], fn ($answer) => trim((string) $answer) !== '');
            if (count($filledAnswers) < 2) {
                $errors['answers'] = 'Dodaj co najmniej dwie odpowiedzi.';
            }

            if (!$data['correct_answers']) {
                $errors['correct_answers'] = 'Zaznacz poprawna odpowiedz.';
            }

            if ($data['type'] === 'single' && count($data['correct_answers']) !== 1) {
                $errors['correct_answers'] = 'Dla pytania jednokrotnego wyboru zaznacz dokladnie jedna odpowiedz.';
            }

            foreach ($data['correct_answers'] as $index) {
                if (trim((string) ($data['answers'][(int) $index] ?? '')) === '') {
                    $errors['correct_answers'] = 'Poprawna odpowiedz nie moze byc pusta.';
                }
            }
        }

        if (in_array($data['type'], ['open', 'gap'], true) && trim($data['correct_text_answer']) === '') {
            $errors['correct_text_answer'] = 'Podaj poprawna odpowiedz tekstowa.';
        }

        return $errors;
    }

    private function questionToFormData(array $question): array
    {
        $answers = array_pad(array_column($question['answers'], 'answer_text'), 4, '');
        $correct = [];

        foreach ($question['answers'] as $index => $answer) {
            if ((int) $answer['is_correct'] === 1) {
                $correct[] = (string) $index;
            }
        }

        return [
            'type' => $question['type'],
            'question_text' => $question['question_text'],
            'points' => $question['points'],
            'answers' => $answers,
            'correct_answers' => $correct,
            'correct_text_answer' => $question['correct_text_answer'] ?? '',
            'hint_text' => $question['hint_text'] ?? '',
        ];
    }
}
