<?php

namespace App\Support\Japanese;

use App\Models\Japanese\Answer;
use App\Models\Japanese\Question;
use Illuminate\Support\Collection;

/**
 * Хариултыг зөвхөн сервер дээр шалгана: зөв хариулт хуудасны HTML-д ордоггүй.
 */
class QuizGrader
{
    public function isCorrect(Question $question, mixed $given): bool
    {
        $answers = $question->answers;

        return match ($question->kind) {
            'match' => is_array($given) && $this->matchIsCorrect($answers, $given),
            'order' => is_array($given) && $this->orderIsCorrect($answers, $given),
            default => is_scalar($given) && $answers->firstWhere('is_correct', true)?->id === (int) $given,
        };
    }

    public function correctText(Question $question): string
    {
        $answers = $question->answers;

        return match ($question->kind) {
            'match' => $answers->map(fn (Answer $a) => "{$a->text}: {$a->match_text}")->implode('; '),
            'order' => $answers->pluck('text')->implode(' '),
            default => (string) $answers->firstWhere('is_correct', true)?->text,
        };
    }

    public function givenText(Question $question, mixed $given): string
    {
        $answers = $question->answers->keyBy('id');

        if ($question->kind === 'match' && is_array($given)) {
            return collect($given)
                ->map(fn ($rightId, $leftId) => ($answers[(int) $leftId]->text ?? '?').': '.($answers[(int) $rightId]->match_text ?? '?'))
                ->implode('; ');
        }

        if ($question->kind === 'order' && is_array($given)) {
            return collect($given)->map(fn ($id) => $answers[(int) $id]->text ?? '?')->implode(' ');
        }

        return is_scalar($given) ? (string) ($answers[(int) $given]->text ?? '') : '';
    }

    private function matchIsCorrect(Collection $answers, array $given): bool
    {
        $byId = $answers->keyBy('id');

        if (count($given) !== $answers->count()) {
            return false;
        }

        foreach ($answers as $left) {
            $right = $byId[(int) ($given[$left->id] ?? 0)] ?? null;

            if (! $right || $right->match_text !== $left->match_text) {
                return false;
            }
        }

        return true;
    }

    private function orderIsCorrect(Collection $answers, array $given): bool
    {
        $byId = $answers->keyBy('id');
        $sequence = collect($given)->values()->map(fn ($id) => $byId[(int) $id]->text ?? null)->all();

        return $sequence === $answers->pluck('text')->all();
    }
}
