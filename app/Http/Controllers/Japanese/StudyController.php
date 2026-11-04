<?php

namespace App\Http\Controllers\Japanese;

use App\Http\Controllers\Controller;
use App\Models\Japanese\Audio;
use App\Models\Japanese\Category;
use App\Models\Japanese\Character;
use App\Models\Japanese\Flashcard;
use App\Models\Japanese\Grammar;
use App\Models\Japanese\Kanji;
use App\Models\Japanese\Lesson;
use App\Models\Japanese\Level;
use App\Models\Japanese\Quiz;
use App\Models\Japanese\TestResult;
use App\Models\Japanese\UserProgress;
use App\Models\Japanese\Vocabulary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $settings = $user->settings;
        $levels = Level::orderBy('position')->get();
        $current = $levels->firstWhere('id', $settings->japanese_level_id);
        $today = now()->startOfDay();

        $studiedToday = UserProgress::where('user_id', $user->id)->where('last_studied_at', '>=', $today)->count()
            + Flashcard::where('user_id', $user->id)->where('last_reviewed_at', '>=', $today)->count();

        $results = TestResult::where('user_id', $user->id)->with('quiz')->latest('submitted_at')->limit(5)->get();
        $grammarTotal = Grammar::published()->count();
        $grammarDone = UserProgress::where('user_id', $user->id)->where('item_type', 'grammar')->count();

        return view('japanese.index', [
            'levels' => $levels,
            'current' => $current,
            'goal' => $settings->japanese_daily_goal,
            'studiedToday' => $studiedToday,
            'stats' => [
                ['Одоо сурч буй түвшин', $current?->code ?? 'Сонгоогүй', 'bi-signpost'],
                ['Дууссан хичээл', UserProgress::where('user_id', $user->id)->whereIn('item_type', ['lesson', 'audio'])->count(), 'bi-check2-circle'],
                ['Цээжилсэн үг', Flashcard::where('user_id', $user->id)->where('status', 'known')->count(), 'bi-card-text'],
                ['Дүрмийн ахиц', "{$grammarDone} / {$grammarTotal}", 'bi-journal-text'],
                ['Тестийн дундаж оноо', $results->isEmpty() ? 'Тест өгөөгүй' : round($results->avg('percent')).'%', 'bi-pencil-square'],
            ],
            'results' => $results,
            'lastStudied' => $this->lastStudied($user->id),
        ]);
    }

    public function setLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id' => ['sometimes', 'integer', Rule::exists('japanese_levels', 'id')->where('status', 'published')],
            'daily_goal' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);

        $settings = $request->user()->settings;

        if (isset($data['level_id'])) {
            $settings->japanese_level_id = $data['level_id'];
        }
        if (isset($data['daily_goal'])) {
            $settings->japanese_daily_goal = $data['daily_goal'];
        }
        $settings->save();

        return back()->with('status', 'Тохиргоо хадгалагдлаа.');
    }

    public function level(Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        $lessonCounts = $level->lessons()->published()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        return view('japanese.level', [
            'level' => $level,
            'sections' => [
                ['Хирагана', 'ひらがな', 'Үсэг бүрийн дуудлага, жишээ үг.', $level->characters()->published()->where('script', 'hiragana')->count(), route('japanese.kana', [$level, 'hiragana'])],
                ['Катакана', 'カタカナ', 'Гадаад гаралтай үгсийн үсэг.', $level->characters()->published()->where('script', 'katakana')->count(), route('japanese.kana', [$level, 'katakana'])],
                ['Ханз', '漢字', 'Уншлага, утга, жишээ.', $level->kanji()->published()->count(), route('japanese.kanji', $level)],
                ['Үгийн сан', '語彙', 'Сэдвээр ангилсан үгс, Flashcard.', $level->vocabulary()->published()->count(), route('japanese.vocabulary', $level)],
                ['Дүрэм', '文法', 'Бүтэц, тайлбар, жишээ өгүүлбэр.', $level->grammar()->published()->count(), route('japanese.grammar', $level)],
                ['Сонсох дасгал', '聴解', 'Япон аудио, бичвэр, орчуулга.', $level->audio()->published()->count(), route('japanese.listening', $level)],
                ['Унших дасгал', '読解', 'Богино эх унших.', $lessonCounts['reading'] ?? 0, route('japanese.lessons', [$level, 'reading'])],
                ['Ярианы дасгал', '会話', 'Хоёр хүний яриа.', $lessonCounts['conversation'] ?? 0, route('japanese.lessons', [$level, 'conversation'])],
                ['Өдөр тутмын харилцан яриа', '日常会話', 'Өдөр тутмын нөхцөл байдал.', $lessonCounts['daily'] ?? 0, route('japanese.lessons', [$level, 'daily'])],
                ['Дасгал, JLPT бэлтгэл', '試験', 'Тест ажиллаж оноогоо шалгах.', $level->quizzes()->published()->count(), route('japanese.quizzes', $level)],
            ],
        ]);
    }

    public function kana(Request $request, Level $level, string $script): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.kana', [
            'level' => $level,
            'script' => $script,
            'scriptName' => $script === 'hiragana' ? 'Хирагана' : 'Катакана',
            'rows' => $level->characters()->published()->where('script', $script)->orderBy('position')->get()->groupBy('row_name'),
            'done' => $this->doneIds($request, 'character'),
        ]);
    }

    public function character(Request $request, Level $level, Character $character): View
    {
        $this->ensureVisible($character, $level);

        $siblings = $level->characters()->published()->where('script', $character->script)->orderBy('position')->get()->values();
        $index = $siblings->search(fn ($item) => $item->id === $character->id);

        return view('japanese.character', [
            'level' => $level,
            'character' => $character,
            'scriptName' => $character->script === 'hiragana' ? 'Хирагана' : 'Катакана',
            'previous' => $index > 0 ? $siblings[$index - 1] : null,
            'next' => $siblings[$index + 1] ?? null,
            'isDone' => $this->doneIds($request, 'character')->contains($character->id),
        ]);
    }

    public function vocabulary(Request $request, Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        $slug = is_string($request->query('category')) ? $request->query('category') : '';

        return view('japanese.vocabulary', [
            'level' => $level,
            'categories' => Category::orderBy('position')->get(),
            'categorySlug' => $slug,
            'words' => $level->vocabulary()->published()
                ->with('category')
                ->when($slug !== '', fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $slug)))
                ->orderBy('category_id')->orderBy('position')
                ->paginate(40)
                ->withQueryString(),
            'done' => $this->doneIds($request, 'vocabulary'),
        ]);
    }

    public function kanji(Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.kanji', ['level' => $level, 'items' => $level->kanji()->published()->orderBy('position')->get()]);
    }

    public function kanjiShow(Request $request, Level $level, Kanji $kanji): View
    {
        $this->ensureVisible($kanji, $level);

        return view('japanese.kanji-show', [
            'level' => $level,
            'kanji' => $kanji,
            'isDone' => $this->doneIds($request, 'kanji')->contains($kanji->id),
        ]);
    }

    public function grammar(Request $request, Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.grammar', [
            'level' => $level,
            'items' => $level->grammar()->published()->orderBy('position')->get(),
            'done' => $this->doneIds($request, 'grammar'),
        ]);
    }

    public function grammarShow(Request $request, Level $level, Grammar $grammar): View
    {
        $this->ensureVisible($grammar, $level);

        return view('japanese.grammar-show', [
            'level' => $level,
            'grammar' => $grammar->load('examples'),
            'isDone' => $this->doneIds($request, 'grammar')->contains($grammar->id),
            'quiz' => $level->quizzes()->published()->where('type', 'grammar')->first(),
        ]);
    }

    public function lessons(Request $request, Level $level, string $type): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.lessons', [
            'level' => $level,
            'typeName' => Lesson::TYPES[$type],
            'items' => $level->lessons()->published()->where('type', $type)->orderBy('position')->get(),
            'done' => $this->doneIds($request, 'lesson'),
        ]);
    }

    public function lesson(Request $request, Level $level, Lesson $lesson): View
    {
        $this->ensureVisible($lesson, $level);

        return view('japanese.lesson', [
            'level' => $level,
            'lesson' => $lesson->load('examples'),
            'typeName' => Lesson::TYPES[$lesson->type],
            'isDone' => $this->doneIds($request, 'lesson')->contains($lesson->id),
        ]);
    }

    public function listening(Request $request, Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        return view('japanese.listening', [
            'level' => $level,
            'items' => $level->audio()->published()->orderBy('position')->get(),
            'done' => $this->doneIds($request, 'audio'),
        ]);
    }

    public function listeningShow(Request $request, Level $level, Audio $audio): View
    {
        $this->ensureVisible($audio, $level);

        return view('japanese.listening-show', [
            'level' => $level,
            'audio' => $audio,
            'quiz' => $audio->quizzes()->published()->first(),
            'isDone' => $this->doneIds($request, 'audio')->contains($audio->id),
        ]);
    }

    public function audioFile(Request $request, Audio $audio): BinaryFileResponse
    {
        abort_unless($request->user()->isAdmin() || $audio->isPublished(), 404);
        abort_unless($audio->file_path && Storage::disk('local')->exists($audio->file_path), 404);

        return response()->file(Storage::disk('local')->path($audio->file_path), [
            'Content-Type' => $audio->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function toggleProgress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(UserProgress::TYPES))],
            'id' => ['required', 'integer'],
        ]);

        $model = UserProgress::TYPES[$data['type']];
        abort_unless($model::published()->whereKey($data['id'])->exists(), 404);

        $key = ['user_id' => $request->user()->id, 'item_type' => $data['type'], 'item_id' => $data['id']];
        $existing = UserProgress::where($key)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Сурсан тэмдгийг хаслаа.');
        }

        UserProgress::create($key + ['last_studied_at' => now()]);

        return back()->with('status', 'Сурсан гэж тэмдэглэлээ.');
    }

    private function ensureVisible(Model $item, Level $level): void
    {
        abort_unless($level->isPublished() && $item->level_id === $level->id && $item->isPublished(), 404);
    }

    private function doneIds(Request $request, string $type): Collection
    {
        return UserProgress::where('user_id', $request->user()->id)->where('item_type', $type)->pluck('item_id');
    }

    /**
     * Сүүлд суралцсан зүйлийн нэр (зөвхөн одоо нийтлэгдсэн хэвээр байвал).
     */
    private function lastStudied(int $userId): ?string
    {
        $progress = UserProgress::where('user_id', $userId)->latest('last_studied_at')->first();
        $card = Flashcard::where('user_id', $userId)->with('vocabulary')->latest('last_reviewed_at')->first();

        if ($card && (! $progress || $card->last_reviewed_at->gt($progress->last_studied_at))) {
            return $card->vocabulary?->word;
        }

        if (! $progress) {
            return null;
        }

        $item = (UserProgress::TYPES[$progress->item_type])::find($progress->item_id);

        return $item?->title ?? $item?->word ?? $item?->kanji ?? $item?->character;
    }
}
