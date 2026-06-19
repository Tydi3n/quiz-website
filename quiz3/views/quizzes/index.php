<section class="page-heading">
    <div>
        <p class="eyebrow">Strona glowna</p>
        <h1>Lista quizow</h1>
        <p class="muted">Tutaj sa quizy dodane w aplikacji.</p>
    </div>
    <?php if (user_has_role(['author', 'admin'])): ?>
        <a class="button" href="<?= e(route_url('quizzes.create')) ?>">Dodaj nowy</a>
    <?php endif; ?>
</section>

<form class="panel filters" method="get">
    <input type="hidden" name="route" value="quizzes.index">

    <label>
        Szukaj
        <input type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Tytul, opis, autor lub kategoria">
    </label>

    <label>
        Kategoria
        <select name="category_id">
            <option value="">Wszystkie</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= selected($filters['category_id'], $category['id']) ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Trudnosc
        <select name="difficulty">
            <option value="">Wszystkie</option>
            <?php foreach (\App\Models\Quiz::DIFFICULTIES as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= selected($filters['difficulty'], $key) ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Sortowanie
        <select name="sort">
            <option value="newest" <?= selected($filters['sort'], 'newest') ?>>Najnowsze</option>
            <option value="popular" <?= selected($filters['sort'], 'popular') ?>>Popularnosc</option>
            <option value="difficulty" <?= selected($filters['sort'], 'difficulty') ?>>Trudnosc</option>
            <option value="title" <?= selected($filters['sort'], 'title') ?>>Tytul</option>
        </select>
    </label>

    <button class="button" type="submit">Filtruj</button>
</form>

<section class="panel">
    <?php if (!$quizzes): ?>
        <div class="empty-inline">
            <h2>Brak quizow</h2>
            <p class="muted">Zmien filtry albo dodaj pierwszy quiz jako autor.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tytul</th>
                        <th>Kategorie</th>
                        <th>Trudnosc</th>
                        <th>Pytania</th>
                        <th>Podejscia</th>
                        <th>Data</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td>
                                <strong><?= e($quiz['title']) ?></strong>
                                <?php if ((int) $quiz['is_hidden'] === 1): ?>
                                    <span class="badge warning">Ukryty</span>
                                <?php endif; ?>
                                <span class="muted block">Autor: <?= e($quiz['author_name']) ?></span>
                            </td>
                            <td><?= e(str_replace(',', ', ', $quiz['category_names'] ?? 'Brak')) ?></td>
                            <td><?= e(\App\Models\Quiz::DIFFICULTIES[$quiz['difficulty']] ?? $quiz['difficulty']) ?></td>
                            <td><?= e($quiz['question_count']) ?></td>
                            <td><?= e($quiz['attempt_count']) ?></td>
                            <td><?= e(substr($quiz['created_at'], 0, 10)) ?></td>
                            <td class="actions">
                                <a class="button small ghost" href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>">Otworz</a>
                                <?php if (\App\Models\Quiz::canManage($quiz, current_user())): ?>
                                    <a class="button small" href="<?= e(route_url('quizzes.edit', ['id' => $quiz['id']])) ?>">Edytuj</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
