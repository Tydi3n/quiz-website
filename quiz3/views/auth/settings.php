<section class="page-heading">
    <div>
        <p class="eyebrow">Konto</p>
        <h1><?= e($user['username']) ?></h1>
        <p class="muted"><?= e($user['email']) ?></p>
    </div>
</section>

<div class="grid two">
    <form class="panel form-stack" method="post" action="<?= e(route_url('auth.settings')) ?>">
        <h2>Ustawienia</h2>

        <label>
            Motyw
            <select name="preference_theme">
                <option value="light" <?= selected($user['preference_theme'], 'light') ?>>Jasny</option>
                <option value="dark" <?= selected($user['preference_theme'], 'dark') ?>>Ciemny</option>
            </select>
        </label>

        <label>
            Jezyk
            <select name="preference_language">
                <option value="pl" <?= selected($user['preference_language'] ?? 'pl', 'pl') ?>>Polski</option>
                <option value="en" <?= selected($user['preference_language'] ?? 'pl', 'en') ?>>English</option>
            </select>
        </label>

        <button class="button" type="submit">Zapisz</button>
    </form>

    <section class="panel">
        <div class="section-title"><h2>Moje wyniki</h2><a class="button small ghost" href="<?= e(route_url('attempts.export')) ?>">Pobierz CSV</a></div>
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


<section class="panel badges-panel">
    <h2>Moje odznaki</h2>
    <?php if (!$badges): ?>
        <p class="muted">Brak odznak. Rozwiaz quiz, zeby cos zdobyc.</p>
    <?php else: ?>
        <div class="badge-list">
            <?php foreach ($badges as $badge): ?>
                <span class="badge success">🏅 <?= e($badge['badge_name']) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
