<?php

namespace App\Support\Reports;

use App\Enums\FeedbackStatus;
use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Japanese\Flashcard;
use App\Models\Japanese\TestResult;
use App\Models\Japanese\UserProgress;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Тайлангийн өгөгдөл: хуудас, Excel, PDF бүгд ижил эх сурвалжаас авна.
 * Хувийн мэдээллийг шаардлагатай хэмжээнд байлгах үүднээс имэйл, утас оруулаагүй.
 */
class ReportBuilder
{
    public const KEYS = ['progress', 'results', 'stats'];

    public const TITLES = [
        'progress' => 'Суралцагчдын явц',
        'results' => 'Шалгалтын үр дүн',
        'stats' => 'Системийн статистик',
    ];

    /**
     * @return array{title: string, headings: list<string>, rows: Collection<int, list<string|int>>}
     */
    public function build(string $key): array
    {
        return [
            'title' => self::TITLES[$key],
            ...match ($key) {
                'progress' => $this->progress(),
                'results' => $this->results(),
                'stats' => $this->stats(),
            },
        ];
    }

    private function progress(): array
    {
        // Суралцагч бүрт дэд асуулга ажиллуулахгүй: бүлэглэсэн асуулгаар нэг дор авна.
        $progress = Progress::selectRaw('user_id, count(*) as started, sum(is_completed) as completed, max(last_watched_at) as last_watched')
            ->groupBy('user_id')->get()->keyBy('user_id');
        $tests = QuizResult::selectRaw('user_id, count(*) as total, round(avg(percent)) as average')
            ->groupBy('user_id')->get()->keyBy('user_id');
        $saved = Bookmark::selectRaw('user_id, count(*) as total')->groupBy('user_id')->pluck('total', 'user_id');
        $studied = UserProgress::selectRaw('user_id, count(*) as total')->groupBy('user_id')->pluck('total', 'user_id');
        $words = Flashcard::where('status', 'known')->selectRaw('user_id, count(*) as total')->groupBy('user_id')->pluck('total', 'user_id');

        $students = User::where('role', UserRole::Student)->orderBy('name')->get(['id', 'name', 'is_active']);

        return [
            'headings' => ['Суралцагч', 'Төлөв', 'Эхэлсэн хичээл', 'Дууссан хичээл', 'Хадгалсан', 'Өгсөн шалгалт', 'Дундаж дүн', 'Сүүлд үзсэн', 'Япон: сурсан зүйл', 'Япон: цээжилсэн үг'],
            'rows' => $students->map(fn (User $s) => [
                $s->name,
                $s->is_active ? 'Идэвхтэй' : 'Идэвхгүй',
                (int) ($progress[$s->id]->started ?? 0),
                (int) ($progress[$s->id]->completed ?? 0),
                (int) ($saved[$s->id] ?? 0),
                (int) ($tests[$s->id]->total ?? 0),
                isset($tests[$s->id]) ? ((int) $tests[$s->id]->average).'%' : 'Шалгалт өгөөгүй',
                $progress[$s->id]->last_watched ?? 'Үзээгүй',
                (int) ($studied[$s->id] ?? 0),
                (int) ($words[$s->id] ?? 0),
            ])->values(),
        ];
    }

    private function results(): array
    {
        $course = QuizResult::with(['user:id,name', 'quiz.course:id,title'])->get()->map(fn (QuizResult $r) => [
            'at' => $r->submitted_at,
            'row' => [$r->user->name, $r->quiz->title, $r->quiz->course->title, "{$r->score} / {$r->max_score}", "{$r->percent}%", $r->passed ? 'Тэнцсэн' : 'Тэнцээгүй', $r->submitted_at->format('Y-m-d H:i')],
        ]);

        $japanese = TestResult::with(['quiz.level:id,code'])->get();
        $names = User::whereIn('id', $japanese->pluck('user_id')->unique())->pluck('name', 'id');

        $japaneseRows = $japanese->map(fn (TestResult $r) => [
            'at' => $r->submitted_at,
            'row' => [$names[$r->user_id] ?? '', $r->quiz->title, 'Япон хэл '.$r->quiz->level->code, "{$r->score} / {$r->max_score}", "{$r->percent}%", $r->passed ? 'Тэнцсэн' : 'Тэнцээгүй', $r->submitted_at->format('Y-m-d H:i')],
        ]);

        return [
            'headings' => ['Суралцагч', 'Шалгалт', 'Сургалт', 'Оноо', 'Хувь', 'Төлөв', 'Огноо'],
            'rows' => $course->concat($japaneseRows)->sortByDesc('at')->pluck('row')->values(),
        ];
    }

    private function stats(): array
    {
        $students = User::where('role', UserRole::Student);
        $quizTotal = QuizResult::count();
        $quizPassed = QuizResult::where('passed', true)->count();

        return [
            'headings' => ['Үзүүлэлт', 'Утга'],
            'rows' => collect([
                ['Нийт хэрэглэгч', User::count()],
                ['Нийт суралцагч', (clone $students)->count()],
                ['Идэвхтэй суралцагч', (clone $students)->where('is_active', true)->count()],
                ['Нийт сургалт', Course::count()],
                ['Нийтэлсэн сургалт', Course::published()->count()],
                ['Нийт хичээл', Lesson::count()],
                ['Эхэлсэн хичээлийн тоо', Progress::count()],
                ['Дууссан хичээлийн тоо', Progress::where('is_completed', true)->count()],
                ['Өгсөн шалгалт', $quizTotal],
                ['Тэнцсэн шалгалт', $quizPassed],
                ['Тэнцсэн хувь', $quizTotal > 0 ? floor($quizPassed * 100 / $quizTotal).'%' : 'Шалгалт өгөөгүй'],
                ['Япон хэлний өгсөн тест', TestResult::count()],
                ['Нийт санал хүсэлт', Feedback::count()],
                ['Хариу хүлээж буй санал хүсэлт', Feedback::where('status', FeedbackStatus::Pending)->count()],
            ]),
        ];
    }
}
