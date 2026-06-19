<section class="empty-state">
    <h1>Blad aplikacji</h1>
    <p>Wystapil problem podczas obslugi zadania.</p>
    <p class="muted"><?= e($message ?? 'Brak szczegolow bledu.') ?></p>
    <a class="button" href="<?= e(route_url('quizzes.index')) ?>">Wroc do quizow</a>
</section>
