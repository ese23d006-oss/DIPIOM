<?php

namespace App\Http\Controllers\Japanese;

use App\Http\Controllers\Controller;
use App\Models\Japanese\Level;
use App\Models\Japanese\Quiz;
use App\Models\Japanese\TestResult;
use App\Support\Japanese\QuizGrader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request, Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.quizzes', [
            'level' => $level,
            'quizzes' => $level->quizzes()->published()->withCount('questions')->orderBy('position')->get(),
            'best' => TestResult::where('user_id', $request->user()->id)
                ->selectRaw('quiz_id, max(percent) as best')
                ->groupBy('quiz_id')
                ->pluck('best', 'quiz_id'),
        ]);
    }

    public function show(Request $request, Level $level, Quiz $quiz): View
    {
        $this->ensureVisible($quiz, $level);

        return view('japanese.quiz', [
            'level' => $level,
            'quiz' => $quiz->load(['questions.answers', 'audio']),
            'canTake' => ! $request->user()->isAdmin(),
        ]);
    }

    /**
     * Нэг асуултыг шалгаж үр дүнг буцаана. Хадгалахгүй: дүн дуусгах үед бүртгэгдэнэ.
     */
    public function check(Request $request, Quiz $quiz, QuizGrader $grader): JsonResponse
    {
        abort_unless($quiz->isPublished(), 404);

        $question = $quiz->questions()->with('answers')->whereKey($request->integer('question_id'))->firstOrFail();
        $given = $request->input("answers.{$question->id}");

        if (blank($given) || (is_array($given) && in_array('', $given, true))) {
            return response()->json(['answered' => false, 'message' => 'Хариултаа сонгоно уу.']);
        }

        return response()->json([
            'answered' => true,
            'correct' => $grader->isCorrect($question, $given),
            'correct_answer' => $grader->correctText($question),
            'explanation' => $question->explanation_mn,
            'points' => $question->points,
        ]);
    }

    public function submit(Request $request, Level $level, Quiz $quiz, QuizGrader $grader): RedirectResponse
    {
        $this->ensureVisible($quiz, $level);

        $questions = $quiz->questions()->with('answers')->get();
        $given = $request->input('answers', []);
        $given = is_array($given) ? $given : [];

        $score = 0;
        $max = 0;
        $recorded = [];

        foreach ($questions as $question) {
            $answer = $given[$question->id] ?? null;
            $max += $question->points;

            if ($grader->isCorrect($question, $answer)) {
                $score += $question->points;
            }

            $recorded[$question->id] = $answer;
        }

        $percent = $max > 0 ? (int) floor($score * 100 / $max) : 0;

        $result = TestResult::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'max_score' => $max,
            'percent' => $percent,
            'passed' => $percent >= $quiz->pass_percent,
            'answers' => $recorded,
            'submitted_at' => now(),
        ]);

        return redirect()->route('japanese.quizzes.result', [$level, $result]);
    }

    public function result(Request $request, Level $level, TestResult $result, QuizGrader $grader): View
    {
        abort_unless($result->user_id === $request->user()->id, 403);

        $quiz = $result->quiz->load('questions.answers');

        return view('japanese.quiz-result', [
            'level' => $level,
            'quiz' => $quiz,
            'result' => $result,
            'review' => $quiz->questions->map(fn ($question) => [
                'question' => $question,
                'correct' => $grader->isCorrect($question, $result->answers[$question->id] ?? null),
                'given' => $grader->givenText($question, $result->answers[$question->id] ?? null),
                'answer' => $grader->correctText($question),
            ]),
        ]);
    }

    private function ensureVisible(Quiz $quiz, Level $level): void
    {
        abort_unless($level->isPublished() && $quiz->level_id === $level->id && $quiz->isPublished(), 404);
    }
}
