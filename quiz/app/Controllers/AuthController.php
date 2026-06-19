<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attempt;
use App\Models\User;

class AuthController
{
    public function login(): void
    {
        $errors = [];
        $data = ['email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = ['email' => trim((string) ($_POST['email'] ?? ''))];
            $password = (string) ($_POST['password'] ?? '');

            $user = User::authenticate($data['email'], $password);
            if (!$user) {
                $errors['email'] = 'Nieprawidlowy email lub haslo.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                flash('success', 'Zalogowano pomyslnie.');
                redirect_to('quizzes.index');
            }
        }

        view('auth/login', compact('errors', 'data'));
    }

    public function register(): void
    {
        $errors = [];
        $data = [
            'username' => '',
            'email' => '',
            'role' => 'user',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim((string) ($_POST['username'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'role' => (string) ($_POST['role'] ?? 'user'),
            ];
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

            if (mb_strlen($data['username']) < 3) {
                $errors['username'] = 'Nazwa uzytkownika musi miec co najmniej 3 znaki.';
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Podaj poprawny adres email.';
            } elseif (User::findByEmail($data['email'])) {
                $errors['email'] = 'Konto z tym adresem email juz istnieje.';
            }

            if (mb_strlen($password) < 6) {
                $errors['password'] = 'Haslo musi miec co najmniej 6 znakow.';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Powtorzone haslo nie jest takie samo.';
            }

            if (!in_array($data['role'], ['user', 'author'], true)) {
                $errors['role'] = 'Wybierz poprawna role.';
            }

            if (!$errors) {
                $userId = User::create([
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => $password,
                    'role' => $data['role'],
                ]);
                $_SESSION['user_id'] = $userId;
                flash('success', 'Konto zostalo utworzone.');
                redirect_to('quizzes.index');
            }
        }

        view('auth/register', compact('errors', 'data'));
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        flash('success', 'Wylogowano.');
        redirect_to('quizzes.index');
    }

    public function settings(): void
    {
        require_login();

        $user = current_user();
        $attempts = Attempt::recentForUser((int) $user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $theme = (string) ($_POST['preference_theme'] ?? 'light');
            User::updatePreference((int) $user['id'], $theme);
            flash('success', 'Preferencje zostaly zapisane.');
            redirect_to('auth.settings');
        }

        view('auth/settings', compact('user', 'attempts'));
    }
}
