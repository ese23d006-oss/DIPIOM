<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuizAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    private function course(array $attributes = []): Course
    {
        $category = Category::firstOrCreate(['slug' => 'c'], ['name' => 'Ангилал']);

        return Course::create(array_merge([
            'category_id' => $category->id, 'title' => 'Сургалт', 'slug' => 'course-'.Course::count(),
            'description' => 'Т', 'is_published' => true,
        ], $attributes));
    }

    private function quiz(?Course $course = null, array $attributes = []): Quiz
    {
        $quiz = Quiz::create(array_merge([
            'course_id' => ($course ?? $this->course())->id, 'title' => 'Шалгалт', 'pass_percent' => 60, 'is_published' => true,
        ], $attributes));

        Question::create(['quiz_id' => $quiz->id, 'body' => 'Нэг?', 'options' => ['А', 'Б'], 'correct_option' => 1, 'points' => 3, 'position' => 1]);
        Question::create(['quiz_id' => $quiz->id, 'body' => 'Хоёр?', 'options' => ['В', 'Г'], 'correct_option' => 0, 'points' => 1, 'position' => 2]);

        return $quiz;
    }

    public function test_profile_can_be_viewed_and_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk()->assertSee($user->email);

        $this->put('/profile', ['name' => 'Шинэ нэр', 'email' => $user->email, 'phone' => '99112233'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->assertSame('Шинэ нэр', $user->fresh()->name);
        $this->assertSame('99112233', $user->fresh()->phone);
    }

    public function test_changing_email_requires_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->put('/profile', ['name' => 'Н', 'email' => 'new@example.com'])->assertSessionHasErrors('email_password');
        $this->put('/profile', ['name' => 'Н', 'email' => 'new@example.com', 'email_password' => 'buruu'])->assertSessionHasErrors('email_password');
        $this->assertNotSame('new@example.com', $user->fresh()->email);

        $this->put('/profile', ['name' => 'Н', 'email' => 'NEW@example.com', 'email_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_profile_cannot_change_role_or_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'Н', 'email' => $user->email, 'role' => 'admin', 'is_active' => '0']);

        $this->assertFalse($user->fresh()->isAdmin());
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_password_change_requires_current_and_validates_new(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->put('/profile/password', ['current_password' => 'buruu', 'password' => 'shineug456', 'password_confirmation' => 'shineug456'])
            ->assertSessionHasErrors(['current_password' => 'Одоогийн нууц үг буруу байна.']);
        $this->put('/profile/password', ['current_password' => 'password', 'password' => 'short', 'password_confirmation' => 'short'])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $this->put('/profile/password', ['current_password' => 'password', 'password' => 'shineug456', 'password_confirmation' => 'shineug456'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('shineug456', $user->fresh()->password));
    }

    public function test_quiz_page_does_not_leak_correct_answer(): void
    {
        $quiz = $this->quiz();

        $html = $this->actingAs(User::factory()->create())
            ->get(route('quizzes.show', [$quiz->course, $quiz]))
            ->assertOk()
            ->assertSee('Нэг?')
            ->assertSee('<legend', false)
            ->getContent();

        $this->assertStringNotContainsString('correct_option', $html);
    }

    public function test_submission_scores_by_points_on_server(): void
    {
        $student = User::factory()->create();
        $quiz = $this->quiz();
        [$first, $second] = $quiz->questions()->get()->all();

        $this->actingAs($student)
            ->post(route('quizzes.submit', [$quiz->course, $quiz]), ['answers' => [$first->id => 1, $second->id => 1], 'score' => 999, 'passed' => false])
            ->assertRedirect();

        $result = QuizResult::firstOrFail();
        $this->assertSame(3, $result->score);
        $this->assertSame(4, $result->max_score);
        $this->assertSame(75, $result->percent);
        $this->assertTrue($result->passed);
        $this->assertSame($student->id, $result->user_id);

        $this->get(route('quizzes.result', $result))
            ->assertOk()
            ->assertSee('3 / 4 оноо (75%)')
            ->assertSee('Та тэнцлээ.')
            ->assertSee('Зөв хариулт: В');
    }

    public function test_unanswered_questions_are_reported_and_answers_kept(): void
    {
        $quiz = $this->quiz();
        [$first, $second] = $quiz->questions()->get()->all();
        $url = route('quizzes.show', [$quiz->course, $quiz]);

        $this->actingAs(User::factory()->create())
            ->from($url)
            ->post(route('quizzes.submit', [$quiz->course, $quiz]), ['answers' => [$first->id => 1]])
            ->assertRedirect($url)
            ->assertSessionHasErrors(["answers.{$second->id}" => '2-р асуултад хариулна уу.']);

        $this->assertSame(0, QuizResult::count());

        $this->get($url)
            ->assertSee('href="#question-'.$second->id.'"', false)
            ->assertSee('2-р асуултад хариулна уу.');

        $this->post(route('quizzes.submit', [$quiz->course, $quiz]), ['answers' => 'hack'])->assertSessionHasErrors();
        $this->post(route('quizzes.submit', [$quiz->course, $quiz]), ['answers' => [$first->id => 99, $second->id => ['x']]])
            ->assertSessionHasErrors(["answers.{$first->id}", "answers.{$second->id}"]);
    }

    public function test_result_survives_later_quiz_edits_and_can_be_retaken(): void
    {
        $student = User::factory()->create();
        $quiz = $this->quiz();
        [$first, $second] = $quiz->questions()->get()->all();
        $answers = ['answers' => [$first->id => 1, $second->id => 0]];

        $this->actingAs($student)->post(route('quizzes.submit', [$quiz->course, $quiz]), $answers);
        $this->post(route('quizzes.submit', [$quiz->course, $quiz]), $answers);
        $this->assertSame(2, QuizResult::count());

        $first->update(['body' => 'Өөрчилсөн', 'options' => ['Ш']]);

        $this->get(route('quizzes.result', QuizResult::first()))->assertSee('Нэг?')->assertDontSee('Өөрчилсөн');
    }

    public function test_draft_quiz_and_foreign_course_are_hidden(): void
    {
        $draft = $this->quiz(null, ['title' => 'Ноорог тест', 'is_published' => false]);
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('quizzes.show', [$draft->course, $draft]))->assertNotFound();
        $this->get('/quizzes')->assertDontSee($draft->title);

        $published = $this->quiz(null, ['title' => 'Нийтэлсэн']);
        $other = $this->course(['title' => 'Өөр']);
        $this->get("/courses/{$other->slug}/quizzes/{$published->id}")->assertNotFound();

        $hiddenCourse = $this->course(['is_published' => false]);
        $inHidden = $this->quiz($hiddenCourse, ['title' => 'Нуугдсан']);
        $this->get('/quizzes')->assertDontSee('Нуугдсан')->assertSee('Нийтэлсэн');
        $this->get(route('quizzes.show', [$hiddenCourse, $inHidden]))->assertNotFound();
    }

    public function test_results_are_private_to_owner_and_admin_page_lists_them(): void
    {
        $student = User::factory()->create(['name' => 'Болд Суралцагч']);
        $quiz = $this->quiz();
        [$first, $second] = $quiz->questions()->get()->all();

        $this->actingAs($student)->post(route('quizzes.submit', [$quiz->course, $quiz]), ['answers' => [$first->id => 0, $second->id => 1]]);
        $result = QuizResult::firstOrFail();
        $this->assertFalse($result->passed);

        $this->actingAs(User::factory()->create())->get(route('quizzes.result', $result))->assertForbidden();
        $this->get(route('admin.quiz-results.index'))->assertForbidden();

        $this->actingAs($this->admin());
        $this->get(route('admin.quiz-results.index'))->assertOk()->assertSee('Болд Суралцагч')->assertSee('Тэнцээгүй');
        $this->get(route('admin.quiz-results.index', ['status' => 'passed']))->assertDontSee('Болд Суралцагч');
        $this->get(route('admin.quiz-results.index', ['course' => ['x'], 'status' => ['y']]))->assertOk();
    }

    public function test_admin_quiz_crud_and_publish_rules(): void
    {
        $this->actingAs($this->admin());
        $course = $this->course();

        $this->post(route('admin.courses.quizzes.store', $course), ['title' => '', 'pass_percent' => 200])
            ->assertSessionHasErrors(['title', 'pass_percent']);

        $this->post(route('admin.courses.quizzes.store', $course), ['title' => 'Шинэ', 'pass_percent' => 70])->assertSessionHasNoErrors();
        $quiz = Quiz::firstOrFail();
        $this->assertFalse($quiz->is_published);

        $this->patch(route('admin.quizzes.toggle', $quiz))->assertSessionHas('error', 'Асуултгүй шалгалтыг нийтлэх боломжгүй.');

        $this->post(route('admin.quizzes.questions.store', $quiz), ['body' => 'А?', 'points' => 1, 'options' => ['Ганц']])
            ->assertSessionHasErrors('options');
        $this->post(route('admin.quizzes.questions.store', $quiz), ['body' => 'А?', 'points' => 1, 'options' => ['Н', 'Х']])
            ->assertSessionHasErrors('options');
        // Хоосон мөр дээр зөв гэж тэмдэглэсэн бол татгалзана.
        $this->post(route('admin.quizzes.questions.store', $quiz), ['body' => 'А?', 'points' => 1, 'correct' => 3, 'options' => ['Н', 'Х', '', '']])
            ->assertSessionHasErrors('options');

        // Хоосон мөр хасагдаж, зөв хариултын индекс дагаж шилжинэ.
        $this->post(route('admin.quizzes.questions.store', $quiz), ['body' => 'А?', 'points' => 2, 'correct' => 3, 'options' => ['Н', '', '', 'Зөв']])
            ->assertSessionHasNoErrors();
        $question = Question::firstOrFail();
        $this->assertSame(['Н', 'Зөв'], $question->options);
        $this->assertSame(1, $question->correct_option);

        $this->patch(route('admin.quizzes.toggle', $quiz))->assertSessionHas('status');
        $this->assertTrue($quiz->fresh()->is_published);

        $this->get(route('admin.questions.edit', $question))->assertOk()->assertSee('value="Зөв"', false);
        $this->put(route('admin.questions.update', $question), ['body' => 'Засагдсан', 'points' => 2, 'correct' => 0, 'options' => ['Н', 'Зөв']])->assertSessionHasNoErrors();
        $this->assertSame('Засагдсан', $question->fresh()->body);
        $this->assertSame(0, $question->fresh()->correct_option);

        // Сүүлийн асуултыг устгавал хоосон шалгалт нийтлэгдсэн үлдэхгүй.
        $this->delete(route('admin.questions.destroy', $question));
        $this->assertFalse($quiz->fresh()->is_published);

        $this->delete(route('admin.quizzes.destroy', $quiz));
        $this->assertNull($quiz->fresh());

        foreach ([route('admin.courses.quizzes.index', $course), route('admin.courses.quizzes.create', $course)] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_student_cannot_use_admin_quiz_actions_and_course_page_links_quiz(): void
    {
        $quiz = $this->quiz();

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.courses.quizzes.index', $quiz->course))->assertForbidden();
        $this->delete(route('admin.quizzes.destroy', $quiz))->assertForbidden();
        $this->patch(route('admin.quizzes.toggle', $quiz))->assertForbidden();
        $this->assertNotNull($quiz->fresh());

        $this->get(route('courses.show', $quiz->course))->assertSee(route('quizzes.show', [$quiz->course, $quiz]), false);
    }

    public function test_admin_cannot_take_quiz(): void
    {
        $quiz = $this->quiz();

        $this->actingAs($this->admin())->post(route('quizzes.submit', [$quiz->course, $quiz]), [])->assertForbidden();
        $this->assertSame(0, DB::table('quiz_results')->count());
    }
}
