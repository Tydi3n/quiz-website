<section class="auth-shell">
    <div class="auth-copy">
        <p class="eyebrow">Logowanie</p>
        <h1>Logowanie</h1>
        <p>Admin: admin@example.com / admin123</p>
        <p>Autor: author@example.com / author123</p>
        <p>Uczen: user@example.com / user123</p>
    </div>

    <form class="panel form-stack" method="post" action="<?= e(route_url('auth.login')) ?>">
        <label>
            Email
            <input type="email" name="email" value="<?= e($data['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
        </label>

        <label>
            Haslo
            <input type="password" name="password" required>
        </label>

        <button class="button wide" type="submit">Zaloguj</button>
        <div class="social-login">
            <p class="muted">Albo konto spolecznosciowe:</p>
            <button class="button ghost wide" type="submit" formnovalidate formaction="<?= e(route_url('auth.social', ['provider' => 'google'])) ?>">Google demo</button>
            <button class="button ghost wide" type="submit" formnovalidate formaction="<?= e(route_url('auth.social', ['provider' => 'github'])) ?>">GitHub demo</button>
        </div>
        <p class="muted">Nie masz konta? <a href="<?= e(route_url('auth.register')) ?>">Utworz je tutaj</a>.</p>
    </form>
</section>
