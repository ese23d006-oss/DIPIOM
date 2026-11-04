<?php

namespace App\Support;

use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Шалгалтын хариуг шалгаж дүнг хадгална. Вэб болон API ижил дүрэм ашиглана.
 */
class QuizSubmission
{
    /**
     * @throws ValidationException
     */
    public function submit(Quiz $quiz, User $user, mixed $given): QuizResult
    {
        $questions = $quiz->questions()->get();
        $given = is_array($given) ? $given : [];

        // Хариулаагүй асуултыг алгасаж дүн гаргахгүй: аль нь дутуу гэдгийг хэлнэ.
        $errors = [];

        foreach ($questions as $number => $question) {
            $answer = $given[$question->id] ?? null;

            if (! is_scalar($answer) || ! array_key_exists((int) $answer, $question->options)) {
                $errors["answers.{$question->id}"] = ($number + 1).'-р асуултад хариулна уу.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $score = 0;
        $max = 0;
        $snapshot = [];

        foreach ($questions as $question) {
            $chosen = (int) $given[$question->id];
            $correct = $chosen === $question->correct_option;

            $max += $question->points;
            $score += $correct ? $question->points : 0;

            // Шалгалтыг дараа нь засахад өгсөн дүнгийн тайлбар өөрчлөгдөхгүй.
            $snapshot[] = [
                'body' => $question->body,
                'options' => $question->options,
                'given' => $chosen,
                'correct_option' => $question->correct_option,
                'is_correct' => $correct,
                'points' => $question->points,
            ];
        }

        $percent = $max > 0 ? (int) floor($score * 100 / $max) : 0;

        return QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'max_score' => $max,
            'percent' => $percent,
            'passed' => $percent >= $quiz->pass_percent,
            'answers' => $snapshot,
            'submitted_at' => now(),
        ]);
    }
}
