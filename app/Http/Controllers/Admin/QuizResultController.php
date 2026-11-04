<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizResultController extends Controller
{
    public function index(Request $request): View
    {
        $courseId = is_string($request->query('course')) ? (int) $request->query('course') : 0;
        $status = is_string($request->query('status')) ? $request->query('status') : '';

        return view('admin.quizzes.results', [
            'results' => QuizResult::with(['user:id,name', 'quiz.course:id,title'])
                ->when($courseId > 0, fn ($query) => $query->whereHas('quiz', fn ($quiz) => $quiz->where('course_id', $courseId)))
                ->when(in_array($status, ['passed', 'failed'], true), fn ($query) => $query->where('passed', $status === 'passed'))
                ->latest('submitted_at')
                ->paginate(30)
                ->withQueryString(),
            'courses' => Course::orderBy('title')->get(['id', 'title']),
            'courseId' => $courseId,
            'status' => $status,
        ]);
    }
}
