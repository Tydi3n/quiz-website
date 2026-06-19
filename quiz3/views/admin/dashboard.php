<section class="page-heading">
    <div>
        <p class="eyebrow">Admin</p>
        <h1>Panel admina</h1>
        <p class="muted">Pelna kontrola nad quizami, kontami, rolami i widocznoscia tresci.</p>
    </div>
    <a class="button ghost" href="<?= e(route_url('attempts.export')) ?>">Eksport wynikow CSV</a>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <span>Uzytkownicy</span>
        <strong><?= e($stats['users']) ?></strong>
    </div>
    <div class="stat-card">
        <span>Quizy</span>
        <strong><?= e($stats['quizzes']) ?></strong>
    </div>
    <div class="stat-card">
        <span>Podejscia</span>
        <strong><?= e($stats['attempts']) ?></strong>
    </div>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Quizy</h2>
        <a class="button small" href="<?= e(route_url('quizzes.create')) ?>">Dodaj quiz</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tytul</th>
                    <th>Autor</th>
                    <th>Kategorie</th>
                    <th>Trudnosc</th>
                    <th>Status</th>
                    <th>Pytania</th>
                    <th>Podejscia</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><a href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>"><?= e($quiz['title']) ?></a></td>
                        <td><?= e($quiz['author_name']) ?></td>
                        <td><?= e(str_replace(',', ', ', $quiz['category_names'] ?? 'Brak')) ?></td>
                        <td><?= e(\App\Models\Quiz::DIFFICULTIES[$quiz['difficulty']] ?? $quiz['difficulty']) ?></td>
                        <td>
                            <span class="badge <?= (int) $quiz['is_hidden'] === 1 ? 'warning' : 'success' ?>">
                                <?= (int) $quiz['is_hidden'] === 1 ? 'Ukryty' : 'Widoczny' ?>
                            </span>
                        </td>
                        <td><?= e($quiz['question_count']) ?></td>
                        <td><?= e($quiz['attempt_count']) ?></td>
                        <td class="actions">
                            <a class="button small ghost" href="<?= e(route_url('quizzes.edit', ['id' => $quiz['id']])) ?>">Edytuj</a>
                            <a class="button small ghost" href="<?= e(route_url('questions.create', ['quiz_id' => $quiz['id']])) ?>">Pytanie</a>
                            <a class="button small ghost" href="<?= e(route_url('rankings.index', ['quiz_id' => $quiz['id']])) ?>">Ranking</a>
                            <a class="button small ghost" href="<?= e(route_url('attempts.export', ['quiz_id' => $quiz['id']])) ?>">CSV</a>
                            <form method="post" action="<?= e(route_url('admin.quiz.toggle')) ?>">
                                <input type="hidden" name="id" value="<?= e($quiz['id']) ?>">
                                <input type="hidden" name="is_hidden" value="<?= (int) $quiz['is_hidden'] === 1 ? '0' : '1' ?>">
                                <button class="button small ghost" type="submit"><?= (int) $quiz['is_hidden'] === 1 ? 'Pokaz' : 'Ukryj' ?></button>
                            </form>
                            <form method="post" action="<?= e(route_url('admin.quiz.delete')) ?>" onsubmit="return confirm('Usunac quiz?')">
                                <input type="hidden" name="id" value="<?= e($quiz['id']) ?>">
                                <button class="button small danger" type="submit">Usun</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Uzytkownicy</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Email</th>
                    <th>Rola</th>
                    <th>Logowanie</th>
                    <th>Preferencje</th>
                    <th>Wyniki</th>
                    <th>Odznaki</th>
                    <th>Data</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $userRow): ?>
                    <tr>
                        <td><?= e($userRow['username']) ?></td>
                        <td><?= e($userRow['email']) ?></td>
                        <td>
                            <form class="inline-form" method="post" action="<?= e(route_url('admin.user.role')) ?>">
                                <input type="hidden" name="id" value="<?= e($userRow['id']) ?>">
                                <select name="role">
                                    <?php foreach (\App\Models\User::ROLES as $role): ?>
                                        <option value="<?= e($role) ?>" <?= selected($userRow['role'], $role) ?>><?= e($role) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button small" type="submit">Zapisz</button>
                            </form>
                        </td>
                        <td><?= e($userRow['oauth_provider'] ? ucfirst($userRow['oauth_provider']) . ' demo' : 'Email i haslo') ?></td>
                        <td><?= e($userRow['preference_theme']) ?> / <?= e($userRow['preference_language']) ?></td>
                        <td><?= e($userRow['attempts_count']) ?></td>
                        <td><?= e($userRow['badges_count']) ?></td>
                        <td><?= e(substr($userRow['created_at'], 0, 10)) ?></td>
                        <td>
                            <?php if ((int) $userRow['id'] === (int) current_user()['id']): ?>
                                <span class="badge warning">Aktywne konto</span>
                            <?php else: ?>
                                <form method="post" action="<?= e(route_url('admin.user.delete')) ?>" onsubmit="return confirm('Usunac uzytkownika?')">
                                    <input type="hidden" name="id" value="<?= e($userRow['id']) ?>">
                                    <button class="button small danger" type="submit">Usun</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
