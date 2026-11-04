<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Request $request, Course $course, Lesson $lesson): View
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || ($course->is_published && $lesson->is_published), 404);

        $lesson->load(['video', 'audioDescription', 'subtitle']);
        $lessons = $course->publishedLessons()->get();
        $index = $lessons->search(fn (Lesson $item) => $item->id === $lesson->id);

        return view('courses.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'lessons' => $lessons,
            'previous' => $index > 0 ? $lessons->get($index - 1) : null,
            'next' => $index !== false ? $lessons->get($index + 1) : null,
            'progress' => $user->progress()->whereIn('lesson_id', $lessons->pluck('id'))->get()->keyBy('lesson_id'),
            'currentProgress' => $user->progress()->where('lesson_id', $lesson->id)->first(),
            'isBookmarked' => $user->bookmarks()->where('lesson_id', $lesson->id)->exists(),
            'settings' => $user->settings,
            'isStudent' => ! $user->isAdmin(),
        ]);
    }

    /**
     * Үзсэн хугацааг хадгална. 95%-д хүрэх эсвэл төгсгөл хүртэл үзвэл дууссан.
     * Дууссан төлөв болон хамгийн өндөр хувь буурахгүй (дахин үзэж болно).
     */
    public function saveProgress(Request $request, Lesson $lesson): JsonResponse
    {
        $this->ensurePublished($lesson);

        $data = $request->validate([
            'position' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:1'],
            'ended' => ['sometimes', 'boolean'],
        ]);

        $duration = $lesson->duration_seconds ?: (int) $data['duration'];
        $position = min((int) $data['position'], $duration);
        $percent = (int) floor($position * 100 / $duration);

        $progress = Progress::firstOrNew(['user_id' => $request->user()->id, 'lesson_id' => $lesson->id]);
        $finished = $progress->is_completed || $request->boolean('ended') || $percent >= 95;

        $progress->last_position_seconds = $position;
        $progress->percent = $finished ? 100 : max((int) $progress->percent, $percent);
        $progress->last_watched_at = now();

        if ($finished && ! $progress->is_completed) {
            $progress->is_completed = true;
            $progress->completed_at = now();
        }

        $progress->save();

        return response()->json([
            'percent' => $progress->percent,
            'is_completed' => (bool) $progress->is_completed,
        ]);
    }

    /**
     * Видеогүй эсвэл бичвэрээр уншсан хичээлийг гараар дуусгах.
     */
    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->ensurePublished($lesson);

        $progress = Progress::firstOrNew(['user_id' => $request->user()->id, 'lesson_id' => $lesson->id]);
        $progress->percent = 100;
        $progress->is_completed = true;
        $progress->completed_at ??= now();
        $progress->last_watched_at = now();
        $progress->save();

        return back()->with('status', 'Хичээлийг дууссан гэж тэмдэглэлээ.');
    }

    private function ensurePublished(Lesson $lesson): void
    {
        abort_unless($lesson->is_published && $lesson->course->is_published, 404);
    }
}
