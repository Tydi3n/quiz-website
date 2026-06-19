<section class="page-heading">
    <div>
        <p class="eyebrow">Ranking</p>
        <h1>Najlepsi uzytkownicy</h1>
        <p class="muted">Ranking liczony jest z zapisanych podejsc do quizow.</p>
    </div>
</section>

<form class="panel filters" method="get">
    <input type="hidden" name="route" value="rankings.index">
    <label>
        Quiz
        <select name="quiz_id">
            <option value="">Wszystkie quizy</option>
            <?php foreach ($quizzes as $quiz): ?>
                <option value="<?= e($quiz['id']) ?>" <?= selected($quizId, $quiz['id']) ?>><?= e($quiz['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="button" type="submit">Pokaz ranking</button>
</form>

<section class="panel">
    <div class="section-title">
        <h2><?= $selectedQuiz ? 'Ranking quizu: ' . e($selectedQuiz['title']) : 'Ranking globalny' ?></h2>
        <a class="button small ghost" href="<?= e(route_url('attempts.export', $quizId ? ['quiz_id' => $quizId] : [])) ?>">Eksport CSV</a>
    </div>

    <?php if (!$ranking): ?>
        <p class="muted">Nie ma jeszcze wynikow do pokazania.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Uzytkownik</th>
                        <th>Punkty razem</th>
                        <th>Sredni %</th>
                        <th>Podejscia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $i => $row): ?>
                        <tr>
                            <td><?= e($i + 1) ?></td>
                            <td><strong><?= e($row['username']) ?></strong></td>
                            <td><?= e($row['total_score']) ?>/<?= e($row['total_max']) ?></td>
                            <td><?= e($row['avg_percent']) ?>%</td>
                            <td><?= e($row['attempts_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
