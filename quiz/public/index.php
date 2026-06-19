<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AttemptController;
use App\Controllers\AuthController;
use App\Controllers\QuestionController;
use App\Controllers\QuizController;

try {
    require dirname(__DIR__) . '/app/bootstrap.php';

    verify_csrf();

    $route = $_GET['route'] ?? 'quizzes.index';

    match ($route) {
        'auth.login' => (new AuthController())->login(),
        'auth.register' => (new AuthController())->register(),
        'auth.logout' => (new AuthController())->logout(),
        'auth.settings' => (new AuthController())->settings(),

        'quizzes.index' => (new QuizController())->index(),
        'quizzes.show' => (new QuizController())->show(),
        'quizzes.create' => (new QuizController())->create(),
        'quizzes.store' => (new QuizController())->store(),
        'quizzes.edit' => (new QuizController())->edit(),
        'quizzes.update' => (new QuizController())->update(),
        'quizzes.delete' => (new QuizController())->delete(),

        'questions.create' => (new QuestionController())->create(),
        'questions.store' => (new QuestionController())->store(),
        'questions.edit' => (new QuestionController())->edit(),
        'questions.update' => (new QuestionController())->update(),
        'questions.delete' => (new QuestionController())->delete(),

        'attempts.take' => (new AttemptController())->take(),
        'attempts.submit' => (new AttemptController())->submit(),
        'attempts.result' => (new AttemptController())->result(),

        'admin.dashboard' => (new AdminController())->dashboard(),
        'admin.quiz.toggle' => (new AdminController())->toggleQuiz(),
        'admin.quiz.delete' => (new AdminController())->deleteQuiz(),
        'admin.user.role' => (new AdminController())->updateUserRole(),
        'admin.user.delete' => (new AdminController())->deleteUser(),

        default => (function (): void {
            http_response_code(404);
            view('errors/404');
        })(),
    };
} catch (Throwable $exception) {
    http_response_code(500);
    $message = $exception->getMessage();

    if (function_exists('view')) {
        view('errors/500', compact('message'));
    } else {
        echo '<h1>Blad aplikacji</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    }
}
