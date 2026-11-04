<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function makeLesson(): Lesson
    {
        $category = Category::create(['name' => 'Ангилал', 'slug' => 'angilal']);
        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Сургалт',
            'slug' => 'surgalt',
            'description' => 'Тайлбар',
        ]);

        return Lesson::create(['course_id' => $course->id, 'title' => 'Хичээл']);
    }

    public function test_tests_never_run_against_the_real_database(): void
    {
        $this->assertSame('accessible_lms_testing', DB::connection()->getDatabaseName());
    }

    public function test_all_twelve_required_tables_exist(): void
    {
        foreach (['users', 'user_settings', 'categories', 'courses', 'lessons', 'lesson_media', 'progress', 'bookmarks', 'quizzes', 'questions', 'quiz_results', 'feedback'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} хүснэгт байхгүй");
        }
    }

    public function test_new_user_is_a_student_with_hashed_password_and_default_settings(): void
    {
        $user = User::create(['name' => 'Бат', 'email' => 'bat@example.com', 'password' => 'nuuts-ug-123']);
        $user->refresh();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotSame('nuuts-ug-123', $user->password);
        $this->assertTrue(Hash::check('nuuts-ug-123', $user->password));
        $this->assertSame(100, $user->settings->font_size_percent);
    }

    public function test_role_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Халдагч',
            'email' => 'attacker@example.com',
            'password' => 'nuuts-ug-123',
            'role' => 'admin',
        ]);

        $this->assertFalse($user->refresh()->isAdmin());
    }

    public function test_progress_is_unique_per_user_and_lesson(): void
    {
        $user = User::factory()->create();
        $lesson = $this->makeLesson();
        Progress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);

        $this->expectException(QueryException::class);
        Progress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);
    }

    public function test_category_with_courses_cannot_be_deleted(): void
    {
        $lesson = $this->makeLesson();

        $this->expectException(QueryException::class);
        $lesson->course->category->delete();
    }

    public function test_deleting_course_cascades_to_lessons_and_progress(): void
    {
        $user = User::factory()->create();
        $lesson = $this->makeLesson();
        Progress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);
        Bookmark::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);

        $lesson->course->delete();

        $this->assertSame(0, Lesson::count());
        $this->assertSame(0, Progress::count());
        $this->assertSame(0, Bookmark::count());
    }

    public function test_correct_answer_is_hidden_from_serialized_question(): void
    {
        $course = $this->makeLesson()->course;
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Шалгалт']);
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'body' => 'Асуулт?',
            'options' => ['А', 'Б'],
            'correct_option' => 1,
        ]);

        $this->assertArrayNotHasKey('correct_option', $question->fresh()->toArray());
        $this->assertSame(['А', 'Б'], $question->fresh()->options);
    }

    public function test_feedback_status_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'subject' => 'Гарчиг',
            'message' => 'Агуулга',
            'status' => 'answered',
        ]);

        $this->assertSame('pending', $feedback->refresh()->status->value);
    }

    public function test_seeder_creates_admin_once_without_fake_content(): void
    {
        config(['lms.admin.email' => 'admin@example.com', 'lms.admin.password' => 'anhnii-nuuts-ug']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($admin->isAdmin());

        // Дахин ажиллуулахад нууц үг өөрчлөгдөхгүй, давхардахгүй.
        config(['lms.admin.password' => 'oor-nuuts-ug']);
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check('anhnii-nuuts-ug', $admin->fresh()->password));
        $this->assertSame(1, User::count());
        $this->assertSame(4, Category::count());
        $this->assertSame(0, Course::count());
    }

    public function test_seeder_refuses_to_run_without_admin_credentials(): void
    {
        config(['lms.admin.email' => null, 'lms.admin.password' => null]);

        $this->expectException(RuntimeException::class);
        $this->seed(DatabaseSeeder::class);
    }
}
