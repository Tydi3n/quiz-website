<section class="auth-shell">
    <div class="auth-copy">
        <p class="eyebrow">Rejestracja</p>
        <h1>Nowe konto</h1>
        <p>Autor moze dodawac quizy. Zwykly uzytkownik moze je rozwiazywac.</p>
    </div>

    <form class="panel form-stack" method="post" action="<?= e(route_url('auth.register')) ?>">
        <?= csrf_field() ?>

        <label>
            Nazwa uzytkownika
            <input type="text" name="username" value="<?= e($data['username']) ?>" required>
            <?php if (!empty($errors['username'])): ?><span class="field-error"><?= e($errors['username']) ?></span><?php endif; ?>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= e($data['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
        </label>

        <label>
            Rola
            <select name="role">
                <option value="user" <?= selected($data['role'], 'user') ?>>Uzytkownik</option>
                <option value="author" <?= selected($data['role'], 'author') ?>>Autor quizow</option>
            </select>
            <?php if (!empty($errors['role'])): ?><span class="field-error"><?= e($errors['role']) ?></span><?php endif; ?>
        </label>

        <div class="grid two">
            <label>
                Haslo
                <input type="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>
            </label>

            <label>
                Powtorz haslo
                <input type="password" name="password_confirm" required>
                <?php if (!empty($errors['password_confirm'])): ?><span class="field-error"><?= e($errors['password_confirm']) ?></span><?php endif; ?>
            </label>
        </div>

        <button class="button wide" type="submit">Utworz konto</button>
    </form>
</section>
