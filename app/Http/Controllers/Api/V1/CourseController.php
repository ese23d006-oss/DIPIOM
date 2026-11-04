<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';
        $category = is_string($request->query('category')) ? $request->query('category') : '';

        $courses = Course::published()
            ->with('category:id,name,slug')
            ->withCount('publishedLessons')
            ->when($term !== '', fn ($query) => $query->search($term))
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->orderBy('title')
            ->paginate(20);

        return response()->json($courses->through(fn (Course $course) => $this->courseData($course)));
    }

    public function show(Course $course): JsonResponse
    {
        abort_unless($course->is_published, 404);

        return response()->json([
            'course' => $this->courseData($course->load('category:id,name,slug')->loadCount('publishedLessons')) + [
                'lessons' => $course->publishedLessons()->get(['id', 'title', 'summary', 'position', 'duration_seconds']),
                'quizzes' => $course->quizzes()->published()->get(['id', 'title', 'description', 'pass_percent']),
            ],
        ]);
    }

    public function lesson(Request $request, Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->is_published && $lesson->course->is_published, 404);

        $media = $lesson->media()->get()->keyBy(fn (LessonMedia $item) => $item->type->value);
        $url = fn (MediaType $type) => isset($media[$type->value]) ? route('api.media', $media[$type->value]) : null;
        $progress = $request->user()->progress()->where('lesson_id', $lesson->id)->first();

        return response()->json([
            'lesson' => [
                'id' => $lesson->id,
                'course_id' => $lesson->course_id,
                'title' => $lesson->title,
                'summary' => $lesson->summary,
                'transcript' => $lesson->transcript,
                'duration_seconds' => $lesson->duration_seconds,
                // Байхгүй файлыг null гэж шударгаар буцаана.
                'video_url' => $url(MediaType::Video),
                'audio_description_url' => $url(MediaType::AudioDescription),
                'subtitle_url' => $url(MediaType::Subtitle),
                'progress' => $progress?->only(['last_position_seconds', 'percent', 'is_completed', 'last_watched_at']),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function courseData(Course $course): array
    {
        return [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'description' => $course->description,
            'category' => $course->category?->only(['name', 'slug']),
            'lesson_count' => $course->published_lessons_count,
            'thumbnail_url' => $course->thumbnail_path ? asset('storage/'.$course->thumbnail_path) : null,
            'thumbnail_alt' => $course->thumbnail_alt,
        ];
    }
}
