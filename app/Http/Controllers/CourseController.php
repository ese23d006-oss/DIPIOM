<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        // Массив зэрэг буруу параметрийг хоосон гэж үзнэ.
        $term = $this->textQuery($request, 'q', 100);
        $categorySlug = $this->textQuery($request, 'category', 255);

        $courses = Course::published()
            ->with('category')
            ->withCount('publishedLessons')
            ->when($term !== '', fn ($query) => $query->search($term))
            ->when($categorySlug !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $categorySlug)
            ))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('courses.index', [
            'courses' => $courses,
            'categories' => Category::orderBy('name')->get(),
            'term' => $term,
            'categorySlug' => $categorySlug,
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        abort_unless($course->is_published || $user?->isAdmin(), 404);

        $course->load('category');
        $lessons = $course->publishedLessons()->get();

        // Явцыг зөвхөн нэвтэрсэн суралцагчид өөрийнх нь хэмжээнд харуулна.
        $progress = $user && ! $user->isAdmin()
            ? $user->progress()->whereIn('lesson_id', $lessons->pluck('id'))->get()->keyBy('lesson_id')
            : collect();

        $quizzes = $course->quizzes()->published()->withCount('questions')->orderBy('id')->get();

        return view('courses.show', compact('course', 'lessons', 'progress', 'quizzes'));
    }

    private function textQuery(Request $request, string $key, int $maxLength): string
    {
        $value = $request->query($key);

        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }
}
