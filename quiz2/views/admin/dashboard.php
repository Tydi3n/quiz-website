<section class="page-heading">
    <div>
        <p class="eyebrow">Admin</p>
        <h1>Panel admina</h1>
    </div>
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
    <h2>Quizy</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tytul</th>
                    <th>Autor</th>
                    <th>Status</th>
                    <th>Podejscia</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><a href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>"><?= e($quiz['title']) ?></a></td>
                        <td><?= e($quiz['author_name']) ?></td>
                        <td><?= (int) $quiz['is_hidden'] === 1 ? 'Ukryty' : 'Widoczny' ?></td>
                        <td><?= e($quiz['attempt_count']) ?></td>
                        <td class="actions">
                            <form method="post" action="<?= e(route_url('admin.quiz.toggle')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($quiz['id']) ?>">
                                <input type="hidden" name="is_hidden" value="<?= (int) $quiz['is_hidden'] === 1 ? '0' : '1' ?>">
                                <button class="button small ghost" type="submit"><?= (int) $quiz['is_hidden'] === 1 ? 'Pokaz' : 'Ukryj' ?></button>
                            </form>
                            <form method="post" action="<?= e(route_url('admin.quiz.delete')) ?>" onsubmit="return confirm('Usunac quiz?')">
                                <?= csrf_field() ?>
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
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($userRow['id']) ?>">
                                <select name="role">
                                    <?php foreach (\App\Models\User::ROLES as $role): ?>
                                        <option value="<?= e($role) ?>" <?= selected($userRow['role'], $role) ?>><?= e($role) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button small" type="submit">Zapisz</button>
                            </form>
                        </td>
                        <td><?= e(substr($userRow['created_at'], 0, 10)) ?></td>
                        <td>
                            <form method="post" action="<?= e(route_url('admin.user.delete')) ?>" onsubmit="return confirm('Usunac uzytkownika?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($userRow['id']) ?>">
                                <button class="button small danger" type="submit">Usun</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
