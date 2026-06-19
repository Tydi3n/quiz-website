<?php
$user = current_user();
$roleLabels = ['user' => 'Uzytkownik', 'author' => 'Autor', 'admin' => 'Administrator'];
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quiz PHP</title>
    <link rel="stylesheet" href="<?= e(asset_path('assets/style.css')) ?>">
</head>
<body class="theme-<?= e(app_theme()) ?>">
    <header class="topbar">
        <nav class="nav">
            <a class="brand" href="<?= e(route_url('quizzes.index')) ?>">
                <span>Quiz PHP</span>
            </a>

            <div class="nav-links">
                <a href="<?= e(route_url('quizzes.index')) ?>"><?= e(t('Quizy', 'Quizzes')) ?></a>
                <a href="<?= e(route_url('rankings.index')) ?>">Ranking</a>
                <?php if (user_has_role(['author', 'admin'])): ?>
                    <a href="<?= e(route_url('quizzes.create')) ?>"><?= e(t('Dodaj', 'Add')) ?></a>
                <?php endif; ?>
                <?php if (user_has_role('admin')): ?>
                    <a href="<?= e(route_url('admin.dashboard')) ?>">Admin</a>
                <?php endif; ?>
            </div>

            <div class="account">
                <a class="button ghost small" href="<?= e(route_url('lang.set', ['lang' => current_lang() === 'pl' ? 'en' : 'pl'])) ?>"><?= current_lang() === 'pl' ? 'EN' : 'PL' ?></a>
                <?php if ($user): ?>
                    <a class="account-pill" href="<?= e(route_url('auth.settings')) ?>">
                        <?= e($user['username']) ?>
                        <span><?= e($roleLabels[$user['role']] ?? $user['role']) ?></span>
                    </a>
                    <form method="post" action="<?= e(route_url('auth.logout')) ?>">
                        <?= csrf_field() ?>
                        <button class="button ghost" type="submit"><?= e(t('Wyloguj', 'Logout')) ?></button>
                    </form>
                <?php else: ?>
                    <a class="button ghost" href="<?= e(route_url('auth.login')) ?>"><?= e(t('Logowanie', 'Login')) ?></a>
                    <a class="button" href="<?= e(route_url('auth.register')) ?>"><?= e(t('Rejestracja', 'Register')) ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="page">
        <?php foreach (consume_flashes() as $flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>
</body>
</html>
