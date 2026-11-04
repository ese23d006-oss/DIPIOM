<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    private const OPTION_ROWS = 6;

    public function create(Quiz $quiz): View
    {
        return $this->form($quiz, new Question(['points' => 1]));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $question = new Question([
            'quiz_id' => $quiz->id,
            'position' => (int) $quiz->questions()->max('position') + 1,
        ]);

        return $this->save($request, $quiz, $question);
    }

    public function edit(Question $question): View
    {
        return $this->form($question->quiz, $question);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        return $this->save($request, $question->quiz, $question);
    }

    public function destroy(Question $question): RedirectResponse
    {
        $quiz = $question->quiz;
        $question->delete();

        // Сүүлийн асуулт устахад хоосон шалгалт нийтлэгдсэн хэвээр үлдэхгүй.
        if ($quiz->is_published && ! $quiz->isPublishable()) {
            $quiz->update(['is_published' => false]);

            return back()->with('status', 'Асуултыг устгалаа. Асуултгүй болсон тул шалгалтыг ноорог болголоо.');
        }

        return back()->with('status', 'Асуултыг устгалаа.');
    }

    private function form(Quiz $quiz, Question $question): View
    {
        return view('admin.quizzes.question-form', [
            'quiz' => $quiz,
            'question' => $question,
            'rows' => self::OPTION_ROWS,
        ]);
    }

    private function save(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'points' => ['required', 'integer', 'between:1,100'],
            'options' => ['required', 'array', 'max:'.self::OPTION_ROWS],
            'options.*' => ['nullable', 'string', 'max:500'],
            'correct' => ['nullable', 'integer'],
        ]);

        // Хоосон мөрийг хасаж, зөв хариултын индексийг шинэ дарааллаар тооцно.
        $options = [];
        $correctOption = null;

        foreach ($data['options'] as $index => $text) {
            if (blank($text)) {
                continue;
            }

            if ((int) $index === (int) ($data['correct'] ?? -1)) {
                $correctOption = count($options);
            }

            $options[] = $text;
        }

        if (count($options) < 2) {
            throw ValidationException::withMessages(['options' => 'Хамгийн багадаа 2 хариулт оруулна уу.']);
        }

        if ($correctOption === null) {
            throw ValidationException::withMessages(['options' => 'Хариулттай мөрүүдээс зөв хариултыг сонгоно уу.']);
        }

        $question->fill([
            'body' => $data['body'],
            'points' => $data['points'],
            'options' => $options,
            'correct_option' => $correctOption,
        ])->save();

        return redirect()->route('admin.quizzes.edit', $quiz)->with('status', 'Асуултыг хадгаллаа.');
    }
}
