<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\UserSetting;
use App\Support\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function myCourses(Request $request): JsonResponse
    {
        $courses = Course::startedBy($request->user())->get()->map(fn (Course $course) => [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'lessons_total' => (int) $course->lessons_total,
            'lessons_completed' => (int) $course->lessons_completed,
            'completion_percent' => $course->completion_percent,
            'last_watched_at' => $course->last_watched_at,
        ]);

        return response()->json(['courses' => $courses, 'watched_seconds' => $request->user()->watchedSeconds()]);
    }

    public function bookmarks(Request $request): JsonResponse
    {
        return response()->json([
            'lessons' => $request->user()->bookmarkedLessons()->published()->get(['lessons.id', 'lessons.title', 'lessons.course_id']),
        ]);
    }

    public function toggleBookmark(Request $request, Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->is_published && $lesson->course->is_published, 404);

        $existing = $request->user()->bookmarks()->where('lesson_id', $lesson->id)->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['bookmarked' => false]);
        }

        $request->user()->bookmarks()->create(['lesson_id' => $lesson->id]);

        return response()->json(['bookmarked' => true], 201);
    }

    public function quiz(Quiz $quiz): JsonResponse
    {
        $this->ensureAvailable($quiz);

        // Question-ийн correct_option нь $hidden тул JSON-д орохгүй.
        return response()->json([
            'quiz' => $quiz->only(['id', 'course_id', 'title', 'description', 'pass_percent']) + [
                'questions' => $quiz->questions()->get(),
            ],
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz, QuizSubmission $submission): JsonResponse
    {
        $this->ensureAvailable($quiz);

        return response()->json(['result' => $this->resultData($submission->submit($quiz, $request->user(), $request->input('answers')))], 201);
    }

    public function results(Request $request): JsonResponse
    {
        return response()->json(
            QuizResult::where('user_id', $request->user()->id)->latest('submitted_at')->paginate(20)
                ->through(fn (QuizResult $result) => $this->resultData($result))
        );
    }

    public function settings(Request $request): JsonResponse
    {
        return response()->json(['settings' => $this->settingsData($request->user()->settings)]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $settings = $request->user()->settings;

        $settings->update($request->validate([
            'font_size_percent' => ['sometimes', 'integer', Rule::in([100, 125, 150, 175, 200])],
            'font_style' => ['sometimes', Rule::in(array_keys(UserSetting::FONT_STYLES))],
            'line_spacing' => ['sometimes', Rule::in(array_keys(UserSetting::LINE_SPACINGS))],
            'accessibility_mode' => ['sometimes', 'boolean'],
            'high_contrast' => ['sometimes', 'boolean'],
            'audio_description_enabled' => ['sometimes', 'boolean'],
            'subtitles_enabled' => ['sometimes', 'boolean'],
            'caption_size' => ['sometimes', Rule::in(array_keys(UserSetting::CAPTION_SIZES))],
            'tts_rate' => ['sometimes', 'numeric', 'between:0.5,2'],
        ]));

        return response()->json(['settings' => $this->settingsData($settings->fresh())]);
    }

    public function feedback(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->feedback()->latest()->paginate(20, ['id', 'subject', 'message', 'status', 'admin_reply', 'replied_at', 'created_at'])
        );
    }

    public function storeFeedback(Request $request): JsonResponse
    {
        $feedback = $request->user()->feedback()->create($request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]));

        return response()->json(['feedback' => $feedback->only(['id', 'subject', 'message', 'status'])], 201);
    }

    private function ensureAvailable(Quiz $quiz): void
    {
        abort_unless($quiz->is_published && $quiz->course->is_published, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function resultData(QuizResult $result): array
    {
        return $result->only(['id', 'quiz_id', 'score', 'max_score', 'percent', 'passed', 'answers', 'submitted_at']);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsData(UserSetting $settings): array
    {
        return $settings->only(['font_size_percent', 'font_style', 'line_spacing', 'accessibility_mode', 'high_contrast', 'audio_description_enabled', 'subtitles_enabled', 'caption_size', 'tts_rate']);
    }
}
