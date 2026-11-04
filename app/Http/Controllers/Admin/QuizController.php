<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Course $course): View
    {
        return view('admin.quizzes.index', [
            'course' => $course,
            'quizzes' => $course->quizzes()->withCount(['questions', 'results'])->orderBy('id')->get(),
        ]);
    }

    public function create(Course $course): View
    {
        return view('admin.quizzes.form', ['course' => $course, 'quiz' => new Quiz(['pass_percent' => 60])]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $quiz = new Quiz($this->validated($request));
        $quiz->course_id = $course->id;
        $quiz->save();

        return redirect()->route('admin.quizzes.edit', $quiz)
            ->with('status', "{$quiz->title} шалгалтыг үүсгэлээ. Одоо асуулт нэмнэ үү.");
    }

    public function edit(Quiz $quiz): View
    {
        return view('admin.quizzes.form', ['course' => $quiz->course, 'quiz' => $quiz->load('questions')]);
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $quiz->update($this->validated($request));

        return redirect()->route('admin.courses.quizzes.index', $quiz->course_id)
            ->with('status', "{$quiz->title} шалгалтыг хадгаллаа.");
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $title = $quiz->title;
        $quiz->delete();

        return back()->with('status', "{$title} шалгалтыг устгалаа.");
    }

    public function toggle(Quiz $quiz): RedirectResponse
    {
        if (! $quiz->is_published && ! $quiz->isPublishable()) {
            return back()->with('error', 'Асуултгүй шалгалтыг нийтлэх боломжгүй.');
        }

        $quiz->is_published = ! $quiz->is_published;
        $quiz->save();

        return back()->with('status', $quiz->is_published ? 'Шалгалтыг нийтэллээ.' : 'Шалгалтыг ноорог болголоо.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'pass_percent' => ['required', 'integer', 'between:1,100'],
        ]);
    }
}
