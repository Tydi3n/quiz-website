<?php
$percent = $attempt['max_score'] > 0 ? round($attempt['score'] / $attempt['max_score'] * 100) : 0;
?>
<section class="result-hero">
    <p class="eyebrow">Koniec quizu</p>
    <h1>Wynik: <?= e($attempt['score']) ?>/<?= e($attempt['max_score']) ?> pkt</h1>
    <div class="score-bar"><span style="width: <?= e($percent) ?>%"></span></div>
    <p><?= e($percent) ?>% - <?= e($attempt['quiz_title']) ?></p>
    <a class="button" href="<?= e(route_url('quizzes.show', ['id' => $attempt['quiz_id']])) ?>">Wroc do quizu</a>
    <a class="button ghost" href="<?= e(route_url('rankings.index', ['quiz_id' => $attempt['quiz_id']])) ?>">Zobacz ranking</a>
    <a class="button ghost" href="<?= e(route_url('attempts.export')) ?>">Eksport CSV</a>
</section>

<section class="panel">
    <h2>Odpowiedzi</h2>
    <div class="result-list">
        <?php foreach ($questions as $question): ?>
            <?php
            $rows = $attemptAnswers[(int) $question['id']] ?? [];
            $isCorrect = $rows ? (int) $rows[0]['is_correct'] === 1 : false;
            $selectedIds = array_filter(array_map(fn ($row) => $row['answer_id'] ? (int) $row['answer_id'] : null, $rows));
            $textAnswer = $rows[0]['answer_text'] ?? '';
            $selectedTexts = [];
            $correctTexts = [];
            foreach ($question['answers'] as $answer) {
                if (in_array((int) $answer['id'], $selectedIds, true)) {
                    $selectedTexts[] = $answer['answer_text'];
                }
                if ((int) $answer['is_correct'] === 1) {
                    $correctTexts[] = $answer['answer_text'];
                }
            }
            ?>
            <article class="result-item <?= $isCorrect ? 'ok' : 'bad' ?>">
                <div>
                    <h3><?= e($question['question_text']) ?></h3>
                    <?php if (in_array($question['type'], ['single', 'multiple'], true)): ?>
                        <p>Twoja odpowiedz: <strong><?= e($selectedTexts ? implode(', ', $selectedTexts) : 'brak') ?></strong></p>
                        <p>Poprawna odpowiedz: <strong><?= e(implode(', ', $correctTexts)) ?></strong></p>
                    <?php else: ?>
                        <p>Twoja odpowiedz: <strong><?= e($textAnswer ?: 'brak') ?></strong></p>
                        <p>Poprawna odpowiedz: <strong><?= e($question['correct_text_answer']) ?></strong></p>
                    <?php endif; ?>
                </div>
                <span class="badge <?= $isCorrect ? 'success' : 'danger' ?>"><?= $isCorrect ? 'OK' : 'Blad' ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>
