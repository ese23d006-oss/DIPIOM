<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_on_pages_errors_and_health(): void
    {
        foreach (['/', '/login', '/page-that-does-not-exist', '/up'] as $path) {
            $response = $this->get($path);

            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'DENY');
            $this->assertStringContainsString("script-src 'self'", $response->headers->get('Content-Security-Policy'));
            $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        }
    }

    public function test_hsts_only_over_https(): void
    {
        $this->assertFalse($this->get('/')->headers->has('Strict-Transport-Security'));
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
    }

    public function test_force_https_redirects_except_health_check(): void
    {
        config(['lms.force_https' => true]);

        $response = $this->get('/courses?q=abc');
        $response->assertStatus(301);
        $this->assertStringStartsWith('https://', $response->headers->get('Location'));
        $this->assertStringContainsString('/courses?q=abc', $response->headers->get('Location'));

        $this->get('/up')->assertOk();
    }

    public function test_csrf_is_enforced_when_not_disabled(): void
    {
        $this->withMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Тестийн орчинд Laravel CSRF-ийг алгасдаг тул тохиргоог нь шалгана.
        $this->assertTrue(class_exists(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class));
        $this->assertContains(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web']
        );
    }

    public function test_debug_off_hides_exception_details(): void
    {
        config(['app.debug' => false]);

        Route::middleware('web')->get('/__boom', fn () => throw new RuntimeException('NUUTS_DB_PASSWORD'));

        $this->get('/__boom')
            ->assertStatus(500)
            ->assertSee('Системийн алдаа гарлаа')
            ->assertDontSee('NUUTS_DB_PASSWORD');
    }

    public function test_every_admin_route_rejects_students_and_guests(): void
    {
        $student = User::factory()->create();
        $routes = collect(Route::getRoutes())->filter(fn ($route) => str_starts_with($route->uri(), 'admin'));

        $this->assertGreaterThan(40, $routes->count());

        foreach ($routes as $route) {
            $uri = preg_replace(['/\{resource\}/', '/\{report\}/', '/\{format\}/', '/\{[^}]+\}/'], ['vocabulary', 'progress', 'pdf', '1'], $route->uri());
            $method = strtolower($route->methods()[0]);

            auth()->logout();
            $guest = $this->{$method}('/'.$uri);
            $this->assertTrue($guest->isRedirect(route('login')), "Зочин нэвтэрсэн: {$method} {$uri} => {$guest->status()}");

            // Байхгүй дугаарт 404, бусад нь 403: аль ч тохиолдолд хандалт хаалттай.
            $status = $this->actingAs($student)->{$method}('/'.$uri)->status();
            $this->assertContains($status, [403, 404], "Суралцагч нэвтэрсэн: {$method} {$uri} => {$status}");
        }
    }

    public function test_media_of_draft_course_is_hidden_from_students(): void
    {
        $category = Category::create(['name' => 'К', 'slug' => 'k']);
        $course = Course::create(['category_id' => $category->id, 'title' => 'Ноорог', 'slug' => 'd', 'description' => 'Т', 'is_published' => false]);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Х', 'is_published' => true]);
        $media = LessonMedia::create(['lesson_id' => $lesson->id, 'type' => MediaType::Video->value, 'file_path' => 'x.mp4', 'original_name' => 'x.mp4', 'mime_type' => 'video/mp4', 'size_bytes' => 1]);

        $this->actingAs(User::factory()->create())->get(route('media.show', $media))->assertNotFound();
    }

    public function test_student_cannot_read_or_answer_other_students_data(): void
    {
        $owner = User::factory()->create();
        $feedback = Feedback::create(['user_id' => $owner->id, 'subject' => 'Нууц сэдэв', 'message' => 'Нууц агуулга']);

        $this->actingAs(User::factory()->create())
            ->get('/feedback')
            ->assertDontSee('Нууц сэдэв')
            ->assertDontSee('Нууц агуулга');

        $this->put(route('admin.feedback.reply', $feedback), ['admin_reply' => 'Х'])->assertForbidden();
    }

    public function test_deactivated_admin_loses_access_immediately(): void
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        $this->actingAs($admin)->get('/admin')->assertOk();

        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin->fresh())->get('/admin')->assertRedirect(route('login'));
    }
}
