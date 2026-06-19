<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;

class RankingController
{
    public function index(): void
    {
        $quizId = isset($_GET['quiz_id']) && $_GET['quiz_id'] !== '' ? (int) $_GET['quiz_id'] : null;
        $ranking = Attempt::leaderboard($quizId);
        $quizzes = Quiz::all([], current_user());
        $selectedQuiz = $quizId ? Quiz::findAny($quizId) : null;

        view('rankings/index', compact('ranking', 'quizzes', 'quizId', 'selectedQuiz'));
    }
}
