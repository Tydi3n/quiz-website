<?php
declare(strict_types=1);

use App\Models\User;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function route_url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['route' => $route], $params));
}

function asset_path(string $path): string
{
    return ltrim($path, '/');
}

function redirect_to(string $route, array $params = []): never
{
    header('Location: ' . route_url($route, $params));
    exit;
}

function view(string $template, array $viewData = []): void
{
    extract($viewData, EXTR_SKIP);

    ob_start();
    require VIEW_PATH . '/' . $template . '.php';
    $content = ob_get_clean();

    require VIEW_PATH . '/layouts/main.php';
}

function flash(string $type, string $message): void
{
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);

    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
        http_response_code(419);
        exit('Nieprawidlowy token formularza. Wroc do poprzedniej strony i sprobuj ponownie.');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return User::find((int) $_SESSION['user_id']);
}

function logged_in(): bool
{
    return current_user() !== null;
}

function user_has_role(string|array $roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    return in_array($user['role'], (array) $roles, true);
}

function require_login(): void
{
    if (!logged_in()) {
        flash('warning', 'Zaloguj sie, aby skorzystac z tej funkcji.');
        redirect_to('auth.login');
    }
}

function require_role(string|array $roles): void
{
    require_login();

    if (!user_has_role($roles)) {
        http_response_code(403);
        view('errors/403');
        exit;
    }
}

function selected(mixed $value, mixed $expected): string
{
    return (string) $value === (string) $expected ? 'selected' : '';
}

function checked(mixed $value, mixed $expected = true): string
{
    if (is_array($expected)) {
        return in_array((string) $value, array_map('strval', $expected), true) ? 'checked' : '';
    }

    return (string) $value === (string) $expected ? 'checked' : '';
}

function normalize_text_answer(string $value): string
{
    $value = mb_strtolower(trim($value));
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

    return $value;
}


function current_lang(): string
{
    $user = current_user();
    $lang = $user['preference_language'] ?? ($_SESSION['lang'] ?? 'pl');

    return in_array($lang, ['pl', 'en'], true) ? $lang : 'pl';
}

function t(string $pl, string $en = ''): string
{
    if (current_lang() === 'en' && $en !== '') {
        return $en;
    }

    return $pl;
}

function app_theme(): string
{
    $user = current_user();

    return $user['preference_theme'] ?? ($_SESSION['guest_theme'] ?? 'light');
}
