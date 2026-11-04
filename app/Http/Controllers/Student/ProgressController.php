<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\Duration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $visible = fn ($progress) => $progress->whereHas(
            'lesson',
            fn ($lesson) => $lesson->published()->whereHas('course', fn ($course) => $course->published())
        );

        $courses = Course::startedBy($user)->with('category')->get();

        return view('student.progress', [
            'stats' => [
                ['Нийт суралцсан хугацаа', Duration::human($user->watchedSeconds()), 'bi-clock-history'],
                ['Эхэлсэн хичээл', $visible($user->progress())->count(), 'bi-play-circle'],
                ['Дууссан хичээл', $visible($user->progress())->where('is_completed', true)->count(), 'bi-check2-circle'],
                ['Үргэлжилж буй сургалт', $courses->filter(fn (Course $c) => $c->completion_percent < 100)->count(), 'bi-collection'],
                ['Дууссан сургалт', $courses->filter(fn (Course $c) => $c->completion_percent === 100)->count(), 'bi-mortarboard'],
            ],
            'courses' => $courses,
            'history' => $visible($user->progress())->with('lesson.course')
                ->whereNotNull('last_watched_at')
                ->latest('last_watched_at')
                ->paginate(15, ['*'], 'history_page'),
            'completed' => $visible($user->progress())->with('lesson.course')
                ->where('is_completed', true)
                ->latest('completed_at')
                ->paginate(15, ['*'], 'completed_page'),
        ]);
    }
}
