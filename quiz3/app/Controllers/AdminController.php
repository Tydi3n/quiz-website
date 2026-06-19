<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;

class AdminController
{
    public function dashboard(): void
    {
        require_role('admin');

        $stats = [
            'users' => User::count(),
            'quizzes' => Quiz::count(),
            'attempts' => Attempt::count(),
        ];
        $quizzes = Quiz::all(['sort' => 'newest'], current_user());
        $users = User::all();

        view('admin/dashboard', compact('stats', 'quizzes', 'users'));
    }

    public function toggleQuiz(): void
    {
        require_role('admin');

        $id = (int) ($_POST['id'] ?? 0);
        Quiz::setHidden($id, !empty($_POST['is_hidden']));
        flash('success', 'Status quizu zostal zmieniony.');
        redirect_to('admin.dashboard');
    }

    public function deleteQuiz(): void
    {
        require_role('admin');

        $imagePath = Quiz::delete((int) ($_POST['id'] ?? 0));
        if ($imagePath) {
            $path = PUBLIC_PATH . '/' . ltrim($imagePath, '/');
            if (is_file($path)) {
                unlink($path);
            }
        }

        flash('success', 'Quiz zostal usuniety.');
        redirect_to('admin.dashboard');
    }

    public function updateUserRole(): void
    {
        require_role('admin');

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) current_user()['id']) {
            flash('warning', 'Nie zmieniaj roli aktualnie zalogowanego administratora.');
            redirect_to('admin.dashboard');
        }

        User::updateRole($id, (string) ($_POST['role'] ?? 'user'));
        flash('success', 'Rola uzytkownika zostala zmieniona.');
        redirect_to('admin.dashboard');
    }

    public function deleteUser(): void
    {
        require_role('admin');

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) current_user()['id']) {
            flash('warning', 'Nie mozna usunac aktualnie zalogowanego konta.');
            redirect_to('admin.dashboard');
        }

        User::delete($id);
        flash('success', 'Uzytkownik zostal usuniety.');
        redirect_to('admin.dashboard');
    }
}
