<?php

namespace App\Http\Controllers\Admin\Japanese;

use App\Http\Controllers\Controller;
use App\Models\Japanese\Question;
use App\Models\Japanese\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    private const ROWS = 6;

    public function create(Quiz $quiz): View
    {
        return $this->form($quiz, new Question(['kind' => 'choice', 'points' => 1]));
    }

    public function edit(Question $question): View
    {
        return $this->form($question->quiz, $question->load('answers'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $question = new Question(['quiz_id' => $quiz->id, 'position' => (int) $quiz->questions()->max('position') + 1]);

        return $this->save($request, $quiz, $question);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        return $this->save($request, $question->quiz, $question);
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', 'Асуултыг устгалаа.');
    }

    private function form(Quiz $quiz, Question $question): View
    {
        return view('admin.japanese.question-form', ['quiz' => $quiz, 'question' => $question, 'rows' => self::ROWS]);
    }

    private function save(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(array_keys(Question::KINDS))],
            'prompt' => ['required', 'string', 'max:2000'],
            'prompt_ja' => ['nullable', 'string', 'max:2000'],
            'explanation_mn' => ['nullable', 'string', 'max:2000'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'answers' => ['array'],
            'answers.*.text' => ['nullable', 'string', 'max:255'],
            'answers.*.match_text' => ['nullable', 'string', 'max:255'],
            'answers.*.lang' => ['nullable', Rule::in(['ja', 'mn', 'ja-Latn'])],
            'correct' => ['nullable', 'integer'],
        ], [], ['prompt' => 'Асуулт', 'kind' => 'Төрөл', 'points' => 'Оноо']);

        $rows = collect($data['answers'] ?? [])
            ->map(fn ($row, $index) => $row + ['index' => (int) $index])
            ->filter(fn ($row) => filled($row['text'] ?? null))
            ->values();

        $this->ensureValidAnswers($data, $rows);

        DB::transaction(function () use ($question, $data, $rows) {
            $question->fill(collect($data)->only(['kind', 'prompt', 'prompt_ja', 'explanation_mn', 'points'])->all());
            $question->save();
            $question->answers()->delete();

            foreach ($rows as $position => $row) {
                $question->answers()->create([
                    'text' => $row['text'],
                    'lang' => $row['lang'] ?? 'ja',
                    'match_text' => $data['kind'] === 'match' ? $row['match_text'] : null,
                    'is_correct' => $data['kind'] === 'choice' && (int) ($data['correct'] ?? -1) === $row['index'],
                    'position' => $position + 1,
                ]);
            }
        });

        return redirect()->route('admin.japanese.edit', ['quizzes', $quiz->id])->with('status', 'Асуултыг хадгаллаа.');
    }

    private function ensureValidAnswers(array $data, $rows): void
    {
        if ($rows->count() < 2) {
            throw ValidationException::withMessages(['answers' => 'Хамгийн багадаа 2 хариулт оруулна уу.']);
        }

        if ($data['kind'] === 'choice' && ! $rows->contains(fn ($row) => $row['index'] === (int) ($data['correct'] ?? -1))) {
            throw ValidationException::withMessages(['answers' => 'Зөв хариултыг сонгоно уу.']);
        }

        if ($data['kind'] === 'match' && $rows->contains(fn ($row) => blank($row['match_text'] ?? null))) {
            throw ValidationException::withMessages(['answers' => 'Хариулт бүрд тохирох утгыг бичнэ үү.']);
        }
    }
}
