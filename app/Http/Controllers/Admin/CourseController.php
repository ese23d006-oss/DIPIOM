<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Support\UniqueSlug;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $term = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';
        $categoryId = is_string($request->query('category')) ? (int) $request->query('category') : 0;
        $status = is_string($request->query('status')) ? $request->query('status') : '';
        $hasStatus = in_array($status, ['published', 'draft'], true);

        return view('admin.courses.index', [
            'courses' => Course::with('category')
                ->withCount('lessons')
                ->when($term !== '', fn ($query) => $query->search($term))
                ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
                ->when($hasStatus, fn ($query) => $query->where('is_published', $status === 'published'))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'term' => $term,
            'categoryId' => $categoryId,
            'status' => $status,
            'isFiltered' => $term !== '' || $categoryId > 0 || $hasStatus,
        ]);
    }

    public function create(): View
    {
        return $this->form(new Course);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $course = new Course($request->safe()->only(['category_id', 'title', 'description', 'thumbnail_alt']));
        $course->slug = UniqueSlug::for(Course::class, $course->title, 'course');
        $course->created_by = $request->user()->id;
        $course->is_published = $request->boolean('is_published');

        if ($request->hasFile('thumbnail')) {
            $course->thumbnail_path = $request->file('thumbnail')->store('course-thumbnails', 'public');
        }

        $course->save();

        return redirect()->route('admin.courses.lessons.index', $course)
            ->with('status', "{$course->title} сургалтыг үүсгэлээ. Одоо хичээл нэмнэ үү.");
    }

    public function edit(Course $course): View
    {
        return $this->form($course);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        // Хуучин холбоос ажиллах ёстой тул slug-ийг өөрчлөхгүй.
        $course->fill($request->safe()->only(['category_id', 'title', 'description', 'thumbnail_alt']));
        $course->is_published = $request->boolean('is_published');

        $oldThumbnail = $course->thumbnail_path;

        if ($request->hasFile('thumbnail')) {
            $course->thumbnail_path = $request->file('thumbnail')->store('course-thumbnails', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $course->thumbnail_path = null;
            $course->thumbnail_alt = null;
        }

        $course->save();

        if ($oldThumbnail && $oldThumbnail !== $course->thumbnail_path) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return redirect()->route('admin.courses.index')->with('status', "{$course->title} сургалтыг хадгаллаа.");
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return back()->with('status', "{$title} сургалтыг устгалаа.");
    }

    private function form(Course $course): View
    {
        return view('admin.courses.form', [
            'course' => $course,
            'categories' => Category::orderBy('name')->get(),
            'imageLimit' => UploadLimit::label(config('lms.uploads.image_kb')),
        ]);
    }
}
