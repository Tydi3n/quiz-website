<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? route_url('quizzes.update', ['id' => $quiz['id']]) : route_url('quizzes.store');
?>
<section class="page-heading">
    <div>
        <p class="eyebrow"><?= $isEdit ? 'Edycja quizu' : 'Nowy quiz' ?></p>
        <h1><?= $isEdit ? e($quiz['title']) : 'Formularz quizu' ?></h1>
    </div>
    <?php if ($isEdit): ?>
        <a class="button ghost" href="<?= e(route_url('quizzes.show', ['id' => $quiz['id']])) ?>">Wroc</a>
    <?php endif; ?>
</section>

<form class="panel form-stack" method="post" enctype="multipart/form-data" action="<?= e($action) ?>">
    <div class="grid two">
        <label>
            Tytul
            <input type="text" name="title" value="<?= e($data['title'] ?? '') ?>" required>
            <?php if (!empty($errors['title'])): ?><span class="field-error"><?= e($errors['title']) ?></span><?php endif; ?>
        </label>

        <label>
            Trudnosc
            <select name="difficulty">
                <?php foreach (\App\Models\Quiz::DIFFICULTIES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= selected($data['difficulty'] ?? 'easy', $key) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['difficulty'])): ?><span class="field-error"><?= e($errors['difficulty']) ?></span><?php endif; ?>
        </label>
    </div>

    <label>
        Opis
        <textarea name="description" rows="5" required><?= e($data['description'] ?? '') ?></textarea>
        <?php if (!empty($errors['description'])): ?><span class="field-error"><?= e($errors['description']) ?></span><?php endif; ?>
    </label>

    <div class="grid two">
        <label>
            Czas w minutach
            <input type="number" name="time_limit" min="1" max="180" value="<?= e($data['time_limit'] ?? 10) ?>" required>
            <?php if (!empty($errors['time_limit'])): ?><span class="field-error"><?= e($errors['time_limit']) ?></span><?php endif; ?>
        </label>

        <label>
            Obrazek
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif">
            <?php if (!empty($errors['image'])): ?><span class="field-error"><?= e($errors['image']) ?></span><?php endif; ?>
        </label>
    </div>

    <?php if ($isEdit && !empty($quiz['image_path'])): ?>
        <div class="current-image">
            <img src="<?= e(asset_path($quiz['image_path'])) ?>" alt="">
            <span>Aktualny obrazek</span>
        </div>
    <?php endif; ?>

    <fieldset>
        <legend>Kategorie</legend>
        <div class="checkbox-grid">
            <?php foreach ($categories as $category): ?>
                <label class="check-row">
                    <input type="checkbox" name="category_ids[]" value="<?= e($category['id']) ?>" <?= checked($category['id'], $selectedCategories) ?>>
                    <span><?= e($category['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($errors['category_ids'])): ?><span class="field-error"><?= e($errors['category_ids']) ?></span><?php endif; ?>
    </fieldset>

    <label class="check-row inline">
        <input type="checkbox" name="is_hidden" value="1" <?= checked((int) ($data['is_hidden'] ?? 0), 1) ?>>
        <span>Quiz ukryty</span>
    </label>

    <div class="form-actions">
        <button class="button" type="submit"><?= $isEdit ? 'Zapisz' : 'Dodaj' ?></button>
        <a class="button ghost" href="<?= e(route_url('quizzes.index')) ?>">Anuluj</a>
    </div>
</form>
