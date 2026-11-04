<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $attributes = []): Course
    {
        $category = Category::firstOrCreate(['slug' => 'c'], ['name' => 'Ангилал']);

        return Course::create(array_merge([
            'category_id' => $category->id, 'title' => 'Сургалт', 'slug' => 'course-'.Course::count(), 'description' => 'Т', 'is_published' => true,
        ], $attributes));
    }

    private function lesson(?Course $course = null, array $attributes = []): Lesson
    {
        return Lesson::create(array_merge(['course_id' => ($course ?? $this->course())->id, 'title' => 'Хичээл', 'transcript' => 'Бичвэр', 'is_published' => true], $attributes));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_login_issues_expiring_token_and_rejects_bad_credentials(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'buruu', 'device_name' => 'phone'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Имэйл хаяг эсвэл нууц үг буруу байна.');

        $token = $this->postJson('/api/v1/login', ['email' => strtoupper($user->email), 'password' => 'password', 'device_name' => 'phone'])
            ->assertCreated()
            ->assertJsonPath('user.role', 'student')
            ->assertJsonMissingPath('user.password')
            ->json('token');

        $this->assertNotNull(PersonalAccessToken::firstOrFail()->expires_at);

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();
        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_inactive_user_cannot_login_or_use_existing_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/me')->assertOk();

        $user->is_active = false;
        $user->save();

        $this->getJson('/api/v1/me')->assertForbidden();
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password', 'device_name' => 'phone'])->assertForbidden();
    }

    public function test_guests_get_json_401_not_html_or_redirect(): void
    {
        foreach (['/api/v1/me', '/api/v1/my-courses', '/api/v1/lessons/1', '/api/v1/admin/stats'] as $path) {
            $this->get($path)->assertUnauthorized()->assertHeader('content-type', 'application/json');
        }

        $this->get('/api/v1/courses/none')->assertNotFound()->assertHeader('content-type', 'application/json');
    }

    public function test_password_change_revokes_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('phone');

        $this->actingAs($user)->put('/profile/password', ['current_password' => 'password', 'password' => 'shineug456', 'password_confirmation' => 'shineug456'])->assertSessionHasNoErrors();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_deleting_user_removes_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('phone');
        $user->delete();

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_course_list_is_public_and_hides_drafts(): void
    {
        $this->course(['title' => 'Нийтэлсэн', 'slug' => 'pub']);
        $this->course(['title' => 'Ноорог', 'slug' => 'draft', 'is_published' => false]);

        $this->getJson('/api/v1/courses')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'pub');
        $this->getJson('/api/v1/courses?q=Ноорог')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/courses/draft')->assertNotFound();
        $this->getJson('/api/v1/courses/pub')->assertOk()->assertJsonPath('course.title', 'Нийтэлсэн');
    }

    public function test_lesson_reports_missing_media_as_null_and_saves_progress(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $lesson = $this->lesson();
        LessonMedia::create(['lesson_id' => $lesson->id, 'type' => MediaType::Video->value, 'file_path' => 'x.mp4', 'original_name' => 'x.mp4', 'mime_type' => 'video/mp4', 'size_bytes' => 1]);

        $this->getJson("/api/v1/lessons/{$lesson->id}")
            ->assertOk()
            ->assertJsonPath('lesson.audio_description_url', null)
            ->assertJsonPath('lesson.subtitle_url', null)
            ->assertJsonPath('lesson.progress', null);
        $this->assertNotNull($this->getJson("/api/v1/lessons/{$lesson->id}")->json('lesson.video_url'));

        $this->postJson("/api/v1/lessons/{$lesson->id}/progress", ['position' => 50, 'duration' => 100])
            ->assertOk()->assertJson(['percent' => 50, 'is_completed' => false]);

        $this->getJson("/api/v1/lessons/{$lesson->id}")->assertJsonPath('lesson.progress.last_position_seconds', 50);
        $this->getJson('/api/v1/my-courses')->assertJsonPath('courses.0.lessons_total', 1);
    }

    public function test_quiz_api_hides_correct_answer_and_grades_on_server(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $quiz = Quiz::create(['course_id' => $this->course()->id, 'title' => 'Ш', 'is_published' => true]);
        $question = Question::create(['quiz_id' => $quiz->id, 'body' => 'А?', 'options' => ['Н', 'Х'], 'correct_option' => 1]);

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}")->assertOk();
        $this->assertStringNotContainsString('correct_option', $response->getContent());

        $this->postJson("/api/v1/quizzes/{$quiz->id}/submit", ['answers' => []])->assertStatus(422);

        $this->postJson("/api/v1/quizzes/{$quiz->id}/submit", ['answers' => [$question->id => 1], 'score' => 0])
            ->assertCreated()
            ->assertJsonPath('result.percent', 100)
            ->assertJsonPath('result.passed', true);

        $this->assertSame($user->id, QuizResult::firstOrFail()->user_id);
        $this->getJson('/api/v1/quiz-results')->assertJsonCount(1, 'data');

        Quiz::whereKey($quiz->id)->update(['is_published' => false]);
        $this->getJson("/api/v1/quizzes/{$quiz->id}")->assertNotFound();
    }

    public function test_settings_bookmarks_and_feedback_belong_to_the_token_user(): void
    {
        $other = User::factory()->create();
        $other->feedback()->create(['subject' => 'Бусдынх', 'message' => 'Х']);
        Sanctum::actingAs(User::factory()->create());
        $lesson = $this->lesson();

        $this->postJson("/api/v1/lessons/{$lesson->id}/bookmark")->assertCreated()->assertJsonPath('bookmarked', true);
        $this->getJson('/api/v1/bookmarks')->assertJsonCount(1, 'lessons');
        $this->postJson("/api/v1/lessons/{$lesson->id}/bookmark")->assertOk()->assertJsonPath('bookmarked', false);

        $this->putJson('/api/v1/settings', ['font_size_percent' => 150, 'line_spacing' => 'wide', 'high_contrast' => true])
            ->assertOk()->assertJsonPath('settings.line_spacing', 'wide');
        $this->putJson('/api/v1/settings', ['font_style' => '"><script>'])->assertStatus(422);

        $this->postJson('/api/v1/feedback', ['subject' => 'Минийх', 'message' => 'М', 'status' => 'answered'])
            ->assertCreated()->assertJsonPath('feedback.status', 'pending');
        $this->getJson('/api/v1/feedback')->assertJsonCount(1, 'data')->assertDontSee('Бусдынх');
    }

    public function test_roles_are_enforced_on_api(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/admin/stats')->assertForbidden();
        $this->getJson('/api/v1/admin/users')->assertForbidden();

        Sanctum::actingAs($this->admin());
        $this->getJson('/api/v1/admin/stats')->assertOk()->assertJsonStructure(['stats' => [['label', 'value']]]);
        $this->getJson('/api/v1/admin/users')->assertOk()->assertJsonMissingPath('data.0.password');
        $this->getJson('/api/v1/my-courses')->assertForbidden();
    }
}
