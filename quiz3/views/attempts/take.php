<section class="page-heading">
    <div>
        <p class="eyebrow"><?= e(\App\Models\Quiz::DIFFICULTIES[$quiz['difficulty']] ?? $quiz['difficulty']) ?> / <?= e($quiz['time_limit']) ?> min</p>
        <h1><?= e($quiz['title']) ?></h1>
        <p class="muted"><?= e($quiz['description']) ?></p>
    </div>
</section>

<form class="quiz-form" method="post" action="<?= e(route_url('attempts.submit')) ?>">
    <input type="hidden" name="quiz_id" value="<?= e($quiz['id']) ?>">

    <?php foreach ($questions as $index => $question): ?>
        <section class="panel question-card">
            <div class="question-head">
                <span class="number"><?= $index + 1 ?></span>
                <div>
                    <h2><?= e($question['question_text']) ?></h2>
                    <p class="muted"><?= e(\App\Models\Question::TYPES[$question['type']] ?? $question['type']) ?>, <?= e($question['points']) ?> pkt</p>
                </div>
            </div>

            <?php if (!empty($question['hint_text'])): ?>
                <details class="hint-box">
                    <summary>Pokaz podpowiedz</summary>
                    <p><?= e($question['hint_text']) ?></p>
                </details>
            <?php endif; ?>

            <?php if ($question['type'] === 'single'): ?>
                <div class="answer-list">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <label class="answer-option">
                            <input type="radio" name="answers[<?= e($question['id']) ?>]" value="<?= e($answer['id']) ?>">
                            <span><?= e($answer['answer_text']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($question['type'] === 'multiple'): ?>
                <div class="answer-list">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <label class="answer-option">
                            <input type="checkbox" name="answers[<?= e($question['id']) ?>][]" value="<?= e($answer['id']) ?>">
                            <span><?= e($answer['answer_text']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($question['type'] === 'open'): ?>
                <label>
                    Odpowiedz
                    <textarea name="answers[<?= e($question['id']) ?>]" rows="4"></textarea>
                </label>
            <?php else: ?>
                <label>
                    Brakujace slowo
                    <input type="text" name="answers[<?= e($question['id']) ?>]">
                </label>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <div class="sticky-submit">
        <button class="button" type="submit">Sprawdz</button>
    </div>
</form>
