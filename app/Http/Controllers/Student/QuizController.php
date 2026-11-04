<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Support\QuizSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        return view('student.quizzes.index', [
            'quizzes' => Quiz::published()
                ->whereHas('course', fn ($course) => $course->published())
                ->with('course')
                ->withCount('questions')
                ->orderBy('course_id')
                ->orderBy('id')
                ->get(),
            'best' => QuizResult::where('user_id', $userId)
                ->selectRaw('quiz_id, max(percent) as best')
                ->groupBy('quiz_id')
                ->pluck('best', 'quiz_id'),
            'results' => QuizResult::where('user_id', $userId)->with('quiz.course')->latest('submitted_at')->paginate(20),
        ]);
    }

    public function show(Course $course, Quiz $quiz): View
    {
        $this->ensureAvailable($course, $quiz);

        return view('student.quizzes.take', [
            'course' => $course,
            'quiz' => $quiz->load('questions'),
        ]);
    }

    public function submit(Request $request, Course $course, Quiz $quiz, QuizSubmission $submission): RedirectResponse
    {
        $this->ensureAvailable($course, $quiz);

        $result = $submission->submit($quiz, $request->user(), $request->input('answers'));

        return redirect()->route('quizzes.result', $result);
    }

    public function result(Request $request, QuizResult $result): View
    {
        abort_unless($result->user_id === $request->user()->id, 403);

        return view('student.quizzes.result', ['result' => $result->load('quiz.course')]);
    }

    private function ensureAvailable(Course $course, Quiz $quiz): void
    {
        abort_unless($course->is_published && $quiz->is_published, 404);
    }
}
