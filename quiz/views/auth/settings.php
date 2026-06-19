<section class="page-heading">
    <div>
        <p class="eyebrow">Konto</p>
        <h1><?= e($user['username']) ?></h1>
        <p class="muted"><?= e($user['email']) ?></p>
    </div>
</section>

<div class="grid two">
    <form class="panel form-stack" method="post" action="<?= e(route_url('auth.settings')) ?>">
        <?= csrf_field() ?>
        <h2>Ustawienia</h2>

        <label>
            Motyw
            <select name="preference_theme">
                <option value="light" <?= selected($user['preference_theme'], 'light') ?>>Jasny</option>
                <option value="dark" <?= selected($user['preference_theme'], 'dark') ?>>Ciemny</option>
            </select>
        </label>

        <button class="button" type="submit">Zapisz</button>
    </form>

    <section class="panel">
        <h2>Moje wyniki</h2>
        <?php if (!$attempts): ?>
            <p class="muted">Brak rozwiazanych quizow.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Wynik</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $attempt): ?>
                            <tr>
                                <td><?= e($attempt['quiz_title']) ?></td>
                                <td><?= e($attempt['score']) ?>/<?= e($attempt['max_score']) ?></td>
                                <td><?= e($attempt['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
