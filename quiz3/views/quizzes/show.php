<section class="quiz-hero">
    <?php if (!empty($quiz['image_path'])): ?>
        <img src="<?= e(asset_path($quiz['image_path'])) ?>" alt="">
    <?php endif; ?>
    <div>
        <p class="eyebrow"><?= e(str_replace(',', ', ', $quiz['category_names'] ?? 'Quiz')) ?></p>
        <h1><?= e($quiz['title']) ?></h1>
        <p><?= e($quiz['description']) ?></p>
        <div class="meta-row">
            <span>Trudnosc: <?= e(\App\Models\Quiz::DIFFICULTIES[$quiz['difficulty']] ?? $quiz['difficulty']) ?></span>
            <span><?= e($quiz['question_count']) ?> pytan</span>
            <span><?= e($quiz['time_limit']) ?> min</span>
            <span><?= e($quiz['attempt_count']) ?> podejsc</span>
            <?php if ((int) $quiz['is_hidden'] === 1): ?><span class="badge warning">Ukryty</span><?php endif; ?>
        </div>
        <div class="form-actions">
            <?php if (logged_in()): ?>
                <a class="button" href="<?= e(route_url('attempts.take', ['id' => $quiz['id']])) ?>">Start</a>
            <?php else: ?>
                <a class="button" href="<?= e(route_url('auth.login')) ?>">Zaloguj sie</a>
            <?php endif; ?>
            <?php if ($canManage): ?>
                <a class="button ghost" href="<?= e(route_url('quizzes.edit', ['id' => $quiz['id']])) ?>">Edytuj</a>
                <a class="button ghost" href="<?= e(route_url('questions.create', ['quiz_id' => $quiz['id']])) ?>">Dodaj pytanie</a>
                <a class="button ghost" href="<?= e(route_url('rankings.index', ['quiz_id' => $quiz['id']])) ?>">Ranking</a>
                <a class="button ghost" href="<?= e(route_url('attempts.export', ['quiz_id' => $quiz['id']])) ?>">Eksport CSV</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Pytania</h2>
        <?php if ($canManage): ?>
            <a class="button small" href="<?= e(route_url('questions.create', ['quiz_id' => $quiz['id']])) ?>">Dodaj</a>
        <?php endif; ?>
    </div>

    <?php if (!$questions): ?>
        <div class="empty-inline">
            <h3>Brak pytan</h3>
            <p class="muted">Nie dodano jeszcze pytan.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tresc</th>
                        <th>Typ</th>
                        <th>Punkty</th>
                        <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <td>
                                <strong><?= e($question['question_text']) ?></strong>
                                <?php if ($canManage): ?>
                                    <?php if (in_array($question['type'], ['single', 'multiple'], true)): ?>
                                        <span class="muted block">
                                            <?= e(implode(', ', array_map(fn ($answer) => ((int) $answer['is_correct'] === 1 ? '[OK] ' : '') . $answer['answer_text'], $question['answers']))) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="muted block">Odpowiedz: <?= e($question['correct_text_answer']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($question['hint_text'])): ?>
                                        <span class="muted block">Podpowiedz: <?= e($question['hint_text']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= e(\App\Models\Question::TYPES[$question['type']] ?? $question['type']) ?></td>
                            <td><?= e($question['points']) ?></td>
                            <?php if ($canManage): ?>
                                <td class="actions">
                                    <a class="button small ghost" href="<?= e(route_url('questions.edit', ['id' => $question['id']])) ?>">Edytuj</a>
                                    <form method="post" action="<?= e(route_url('questions.delete', ['id' => $question['id']])) ?>" onsubmit="return confirm('Usunac pytanie?')">
                                        <button class="button small danger" type="submit">Usun</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($canManage): ?>
    <form class="danger-zone" method="post" action="<?= e(route_url('quizzes.delete', ['id' => $quiz['id']])) ?>" onsubmit="return confirm('Usunac caly quiz?')">
        <button class="button danger" type="submit">Usun quiz</button>
    </form>
<?php endif; ?>
