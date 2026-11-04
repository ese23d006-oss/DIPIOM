<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Support\Duration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $started = Course::startedBy($user)->with('category')->get();
        $startedIds = $started->pluck('id');

        $notStarted = fn () => Course::published()
            ->with('category')
            ->withCount('publishedLessons')
            ->whereNotIn('id', $startedIds);

        // Санал болгох: суралцагчийн эхэлсэн сургалтуудын ангиллаас.
        $recommended = $startedIds->isEmpty()
            ? collect()
            : $notStarted()->whereIn('category_id', $started->pluck('category_id')->unique())->latest()->limit(3)->get();

        return view('student.dashboard', [
            'stats' => [
                ['Нийт сургалт', Course::published()->count(), 'bi-collection'],
                ['Үргэлжилж буй сургалт', $started->filter(fn (Course $c) => $c->completion_percent < 100)->count(), 'bi-play-circle'],
                ['Дууссан сургалт', $started->filter(fn (Course $c) => $c->completion_percent === 100)->count(), 'bi-check2-circle'],
                ['Нийт суралцсан хугацаа', Duration::human($user->watchedSeconds()), 'bi-clock-history'],
            ],
            'lastProgress' => $user->progress()->with('lesson.course')->whereNotNull('last_watched_at')->latest('last_watched_at')->first(),
            'continueCourses' => $started->filter(fn (Course $c) => $c->completion_percent < 100)->take(3)->values(),
            'recentCourses' => $started->take(3),
            'bookmarks' => $user->bookmarks()->with('lesson.course')->latest()->limit(4)->get(),
            'recommended' => $recommended,
            'newCourses' => $notStarted()->whereNotIn('id', $recommended->pluck('id'))->latest()->limit(3)->get(),
            'categories' => Category::withCount(['courses' => fn (Builder $query) => $query->published()])->orderBy('name')->get(),
            'activity' => $this->activity($user),
        ]);
    }

    /**
     * Сүүлийн үйл ажиллагаа: зөвхөн бодит бичлэгээс.
     */
    private function activity($user): Collection
    {
        $lessons = $user->progress()->with('lesson')->whereNotNull('last_watched_at')->latest('last_watched_at')->limit(5)->get()
            ->map(fn ($p) => [
                'at' => $p->last_watched_at,
                'icon' => $p->is_completed ? 'bi-check2-circle' : 'bi-play-circle',
                'text' => ($p->is_completed ? 'Дуусгасан: ' : 'Үзсэн: ').$p->lesson->title,
            ]);

        $quizzes = $user->quizResults()->with('quiz')->latest('submitted_at')->limit(5)->get()
            ->map(fn ($r) => [
                'at' => $r->submitted_at,
                'icon' => 'bi-pencil-square',
                'text' => "Шалгалт өгсөн: {$r->quiz->title} ({$r->percent}%)",
            ]);

        return $lessons->concat($quizzes)->sortByDesc('at')->take(5)->values();
    }
}
