<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? route_url('questions.update', ['id' => $question['id']]) : route_url('questions.store', ['quiz_id' => $quiz['id']]);
$answers = array_pad($data['answers'] ?? [], 4, '');
$correctAnswers = $data['correct_answers'] ?? [];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow"><?= e($quiz['title']) ?></p>
        <h1><?= $isEdit ? 'Edytuj pytanie' : 'Dodaj pytanie' ?></h1>
    </div>
    <a class="button ghost" href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>">Wroc</a>
</section>

<form class="panel form-stack" method="post" action="<?= e($action) ?>" id="question-form">
    <?= csrf_field() ?>

    <div class="grid two">
        <label>
            Typ pytania
            <select name="type" id="question-type">
                <?php foreach (\App\Models\Question::TYPES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= selected($data['type'], $key) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['type'])): ?><span class="field-error"><?= e($errors['type']) ?></span><?php endif; ?>
        </label>

        <label>
            Punkty
            <input type="number" name="points" min="1" max="20" value="<?= e($data['points']) ?>">
            <?php if (!empty($errors['points'])): ?><span class="field-error"><?= e($errors['points']) ?></span><?php endif; ?>
        </label>
    </div>

    <label>
        Tresc pytania
        <textarea name="question_text" rows="4" required><?= e($data['question_text']) ?></textarea>
        <?php if (!empty($errors['question_text'])): ?><span class="field-error"><?= e($errors['question_text']) ?></span><?php endif; ?>
    </label>

    <div class="choice-fields">
        <h2>Odpowiedzi</h2>
        <?php if (!empty($errors['answers'])): ?><span class="field-error"><?= e($errors['answers']) ?></span><?php endif; ?>
        <?php if (!empty($errors['correct_answers'])): ?><span class="field-error"><?= e($errors['correct_answers']) ?></span><?php endif; ?>

        <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="answer-row">
                <label class="correct-marker">
                    <input type="checkbox" name="correct_answers[]" value="<?= e($i) ?>" <?= checked((string) $i, $correctAnswers) ?>>
                    <span>OK</span>
                </label>
                <label>
                    Odpowiedz <?= $i + 1 ?>
                    <input type="text" name="answers[]" value="<?= e($answers[$i] ?? '') ?>">
                </label>
            </div>
        <?php endfor; ?>
    </div>

    <label class="text-answer-field">
        Poprawna odpowiedz
        <input type="text" name="correct_text_answer" value="<?= e($data['correct_text_answer'] ?? '') ?>">
        <?php if (!empty($errors['correct_text_answer'])): ?><span class="field-error"><?= e($errors['correct_text_answer']) ?></span><?php endif; ?>
    </label>

    <div class="form-actions">
        <button class="button" type="submit"><?= $isEdit ? 'Zapisz' : 'Dodaj' ?></button>
        <a class="button ghost" href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>">Anuluj</a>
    </div>
</form>

<script>
    const typeSelect = document.querySelector('#question-type');
    const choiceFields = document.querySelector('.choice-fields');
    const textAnswer = document.querySelector('.text-answer-field');

    function syncQuestionFields() {
        const choice = ['single', 'multiple'].includes(typeSelect.value);
        choiceFields.hidden = !choice;
        textAnswer.hidden = choice;
    }

    typeSelect.addEventListener('change', syncQuestionFields);
    syncQuestionFields();
</script>
