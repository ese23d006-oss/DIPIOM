<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeedbackStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CHART_DAYS = 14;

    public function __invoke(): View
    {
        $students = User::where('role', UserRole::Student);
        $quizTotal = QuizResult::count();
        $quizPassed = QuizResult::where('passed', true)->count();

        return view('admin.dashboard', [
            'stats' => [
                ['Нийт хэрэглэгч', User::count(), 'bi-people'],
                ['Нийт суралцагч', (clone $students)->count(), 'bi-mortarboard'],
                ['Идэвхтэй суралцагч', (clone $students)->where('is_active', true)->count(), 'bi-person-check'],
                ['Нийт сургалт', Course::count(), 'bi-collection'],
                ['Нийтэлсэн сургалт', Course::published()->count(), 'bi-globe'],
                ['Нийт хичээл', Lesson::count(), 'bi-journal-text'],
                ['Өгсөн шалгалт', $quizTotal, 'bi-pencil-square'],
                ['Хариу хүлээж буй санал хүсэлт', Feedback::where('status', FeedbackStatus::Pending)->count(), 'bi-chat-left-text'],
            ],
            'quizTotal' => $quizTotal,
            'quizPassed' => $quizPassed,
            'quizPercent' => $quizTotal > 0 ? (int) floor($quizPassed * 100 / $quizTotal) : 0,
            'registrations' => $this->registrationsPerDay(),
            'chartDays' => self::CHART_DAYS,
            'courseStats' => $this->courseStats(),
            'recentUsers' => User::latest()->limit(5)->get(),
            'activity' => $this->activity(),
        ]);
    }

    /**
     * Сүүлийн өдрүүдийн бүртгэл, бүртгэлгүй өдрүүдийг 0-ээр бөглөнө.
     *
     * @return Collection<int, array{date: Carbon, count: int}>
     */
    private function registrationsPerDay(): Collection
    {
        $start = now()->startOfDay()->subDays(self::CHART_DAYS - 1);

        $counts = User::where('role', UserRole::Student)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, self::CHART_DAYS - 1))->map(function (int $offset) use ($start, $counts) {
            $date = $start->copy()->addDays($offset);

            return ['date' => $date, 'count' => (int) ($counts[$date->toDateString()] ?? 0)];
        });
    }

    private function courseStats(): Collection
    {
        // Сургалт бүрт дэд асуулга ажиллуулбал их өгөгдөл дээр олон секунд зарцуулдаг.
        $learners = Progress::query()
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->selectRaw('lessons.course_id, COUNT(DISTINCT progress.user_id) as total')
            ->groupBy('lessons.course_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'course_id');

        $withLearners = Course::withCount('lessons')->whereIn('id', $learners->keys())->get();
        $others = Course::withCount('lessons')
            ->whereNotIn('id', $learners->keys())
            ->latest()
            ->limit(5 - $withLearners->count())
            ->get();

        return $withLearners->concat($others)
            ->each(fn (Course $course) => $course->setAttribute('learners', (int) ($learners[$course->id] ?? 0)))
            ->sortByDesc('learners')
            ->values();
    }

    private function activity(): Collection
    {
        $registrations = User::where('role', UserRole::Student)->latest()->limit(5)->get()
            ->map(fn (User $user) => [
                'at' => $user->created_at,
                'icon' => 'bi-person-plus',
                'text' => "Шинэ суралцагч: {$user->name}",
            ]);

        $quizzes = QuizResult::with(['user', 'quiz'])->latest('submitted_at')->limit(5)->get()
            ->map(fn (QuizResult $result) => [
                'at' => $result->submitted_at,
                'icon' => 'bi-pencil-square',
                'text' => "{$result->user->name}: {$result->quiz->title} шалгалт ({$result->percent}%)",
            ]);

        $feedback = Feedback::with('user')->latest()->limit(5)->get()
            ->map(fn (Feedback $item) => [
                'at' => $item->created_at,
                'icon' => 'bi-chat-left-text',
                'text' => "Санал хүсэлт: {$item->subject}",
            ]);

        return $registrations->concat($quizzes)->concat($feedback)->sortByDesc('at')->take(6)->values();
    }
}
