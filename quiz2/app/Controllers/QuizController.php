<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Question;
use App\Models\Quiz;

class QuizController
{
    public function index(): void
    {
        $filters = [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'category_id' => (string) ($_GET['category_id'] ?? ''),
            'difficulty' => (string) ($_GET['difficulty'] ?? ''),
            'sort' => (string) ($_GET['sort'] ?? 'newest'),
        ];

        $viewer = current_user();
        $quizzes = Quiz::all($filters, $viewer);
        $categories = Quiz::categories();

        view('quizzes/index', compact('quizzes', 'categories', 'filters'));
    }

    public function show(): void
    {
        $quiz = Quiz::find((int) ($_GET['id'] ?? 0), current_user());
        if (!$quiz) {
            $this->notFound();
            return;
        }

        Quiz::incrementViews((int) $quiz['id']);
        $quiz = Quiz::find((int) $quiz['id'], current_user());
        $questions = Question::forQuiz((int) $quiz['id']);
        $canManage = Quiz::canManage($quiz, current_user());

        view('quizzes/show', compact('quiz', 'questions', 'canManage'));
    }

    public function create(): void
    {
        require_role(['author', 'admin']);

        $data = $this->defaultFormData();
        $errors = [];
        $categories = Quiz::categories();
        $selectedCategories = [];
        $mode = 'create';

        view('quizzes/form', compact('data', 'errors', 'categories', 'selectedCategories', 'mode'));
    }

    public function store(): void
    {
        require_role(['author', 'admin']);

        [$data, $selectedCategories] = $this->requestData();
        $errors = $this->validate($data, $selectedCategories);
        $imagePath = null;
        if (!$errors) {
            $imagePath = $this->uploadImage($errors);
        }

        if ($errors) {
            $categories = Quiz::categories();
            $mode = 'create';
            view('quizzes/form', compact('data', 'errors', 'categories', 'selectedCategories', 'mode'));
            return;
        }

        $data['user_id'] = current_user()['id'];
        $data['category_ids'] = $selectedCategories;
        $data['image_path'] = $imagePath;

        $quizId = Quiz::create($data);
        flash('success', 'Quiz zostal utworzony.');
        redirect_to('quizzes.show', ['id' => $quizId]);
    }

    public function edit(): void
    {
        $quiz = $this->managedQuiz();
        $data = $quiz;
        $errors = [];
        $categories = Quiz::categories();
        $selectedCategories = Quiz::categoryIds((int) $quiz['id']);
        $mode = 'edit';

        view('quizzes/form', compact('quiz', 'data', 'errors', 'categories', 'selectedCategories', 'mode'));
    }

    public function update(): void
    {
        $quiz = $this->managedQuiz();

        [$data, $selectedCategories] = $this->requestData();
        $errors = $this->validate($data, $selectedCategories);
        $newImagePath = null;
        if (!$errors) {
            $newImagePath = $this->uploadImage($errors);
        }

        if ($errors) {
            $categories = Quiz::categories();
            $selectedCategories = array_map('intval', $selectedCategories);
            $mode = 'edit';
            view('quizzes/form', compact('quiz', 'data', 'errors', 'categories', 'selectedCategories', 'mode'));
            return;
        }

        $data['category_ids'] = $selectedCategories;
        if ($newImagePath !== null) {
            $this->deleteUploadedFile($quiz['image_path'] ?? null);
            $data['image_path'] = $newImagePath;
        }

        Quiz::update((int) $quiz['id'], $data);
        flash('success', 'Quiz zostal zaktualizowany.');
        redirect_to('quizzes.show', ['id' => $quiz['id']]);
    }

    public function delete(): void
    {
        $quiz = $this->managedQuiz();
        $imagePath = Quiz::delete((int) $quiz['id']);
        $this->deleteUploadedFile($imagePath);

        flash('success', 'Quiz zostal usuniety razem z powiazanymi pytaniami i wynikami.');
        redirect_to('quizzes.index');
    }

    private function managedQuiz(): array
    {
        require_login();

        $quiz = Quiz::findAny((int) ($_GET['id'] ?? $_POST['id'] ?? 0));
        if (!$quiz) {
            $this->notFound();
            exit;
        }

        if (!Quiz::canManage($quiz, current_user())) {
            http_response_code(403);
            view('errors/403');
            exit;
        }

        return $quiz;
    }

    private function defaultFormData(): array
    {
        return [
            'title' => '',
            'description' => '',
            'difficulty' => 'easy',
            'time_limit' => 10,
            'is_hidden' => 0,
        ];
    }

    private function requestData(): array
    {
        $data = [
            'title' => (string) ($_POST['title'] ?? ''),
            'description' => (string) ($_POST['description'] ?? ''),
            'difficulty' => (string) ($_POST['difficulty'] ?? 'easy'),
            'time_limit' => (int) ($_POST['time_limit'] ?? 10),
            'is_hidden' => isset($_POST['is_hidden']) ? 1 : 0,
        ];

        return [$data, (array) ($_POST['category_ids'] ?? [])];
    }

    private function validate(array $data, array $selectedCategories): array
    {
        $errors = [];

        if (mb_strlen(trim($data['title'])) < 3) {
            $errors['title'] = 'Tytul musi miec co najmniej 3 znaki.';
        }

        if (mb_strlen(trim($data['description'])) < 10) {
            $errors['description'] = 'Opis musi miec co najmniej 10 znakow.';
        }

        if (!array_key_exists($data['difficulty'], Quiz::DIFFICULTIES)) {
            $errors['difficulty'] = 'Wybierz poprawny poziom trudnosci.';
        }

        if ($data['time_limit'] < 1 || $data['time_limit'] > 180) {
            $errors['time_limit'] = 'Limit czasu powinien byc od 1 do 180 minut.';
        }

        if (!$selectedCategories) {
            $errors['category_ids'] = 'Wybierz co najmniej jedna kategorie.';
        }

        return $errors;
    }

    private function uploadImage(array &$errors): ?string
    {
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'Nie udalo sie przeslac pliku.';
            return null;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = 'Plik moze miec maksymalnie 2 MB.';
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($extensions[$mime])) {
            $errors['image'] = 'Dozwolone sa tylko pliki graficzne JPG, PNG, WEBP lub GIF.';
            return null;
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $target = UPLOAD_PATH . '/' . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $errors['image'] = 'Nie udalo sie zapisac pliku na dysku.';
            return null;
        }

        return 'uploads/' . $fileName;
    }

    private function deleteUploadedFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $path = PUBLIC_PATH . '/' . ltrim($relativePath, '/');
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        view('errors/404');
    }
}
