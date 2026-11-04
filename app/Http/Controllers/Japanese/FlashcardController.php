<?php

namespace App\Http\Controllers\Japanese;

use App\Http\Controllers\Controller;
use App\Models\Japanese\Flashcard;
use App\Models\Japanese\Level;
use App\Models\Japanese\Vocabulary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FlashcardController extends Controller
{
    public function show(Request $request, Level $level): View
    {
        abort_unless($level->isPublished(), 404);

        $filter = $this->filter($request);
        $words = $this->deck($request, $level, $filter)->pluck('japanese_vocabulary.id')->values();
        $index = min(max(0, $request->integer('card')), max(0, $words->count() - 1));
        $word = $words->isEmpty() ? null : Vocabulary::find($words[$index]);

        return view('japanese.flashcards', [
            'level' => $level,
            'filter' => $filter,
            'word' => $word,
            'index' => $index,
            'total' => $words->count(),
            'card' => $word ? Flashcard::where('user_id', $request->user()->id)->where('vocabulary_id', $word->id)->first() : null,
            'counts' => Flashcard::where('user_id', $request->user()->id)
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, Level $level, Vocabulary $vocabulary): RedirectResponse
    {
        abort_unless($vocabulary->isPublished() && $vocabulary->level_id === $level->id, 404);

        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Flashcard::STATUSES))]]);

        $card = Flashcard::firstOrNew(['user_id' => $request->user()->id, 'vocabulary_id' => $vocabulary->id]);
        $card->status = $data['status'];
        $card->reviews = (int) $card->reviews + 1;
        $card->last_reviewed_at = now();
        $card->save();

        // Шүүлтээс гарсан бол ижил дугаар дараагийн үг болно.
        $filter = $this->filter($request);
        $index = max(0, $request->integer('card'));
        $stillInDeck = $this->deck($request, $level, $filter)->where('japanese_vocabulary.id', $vocabulary->id)->exists();

        return redirect()
            ->route('japanese.flashcards', [$level, 'filter' => $filter, 'card' => $stillInDeck ? $index + 1 : $index])
            ->with('status', '"'.Flashcard::STATUSES[$data['status']].'" гэж хадгаллаа. Дараагийн карт.');
    }

    private function filter(Request $request): string
    {
        $filter = $request->input('filter');

        return in_array($filter, ['all', 'new', 'known', 'review', 'hard'], true) ? $filter : 'all';
    }

    private function deck(Request $request, Level $level, string $filter): Builder
    {
        $userId = $request->user()->id;
        $query = Vocabulary::query()->published()->where('level_id', $level->id)->orderBy('category_id')->orderBy('position');

        return match ($filter) {
            'new' => $query->whereDoesntHave('flashcards', fn ($card) => $card->where('user_id', $userId)),
            'known', 'review', 'hard' => $query->whereHas('flashcards', fn ($card) => $card->where('user_id', $userId)->where('status', $filter)),
            default => $query,
        };
    }
}
