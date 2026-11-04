<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\JapaneseText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_reading_preferences_are_saved_and_applied(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', [
            'font_size_percent' => 100, 'tts_rate' => '1',
            'font_style' => 'verdana', 'line_spacing' => 'wider', 'caption_size' => 'large', 'accessibility_mode' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user->fresh())->get('/dashboard')
            ->assertSee('data-font="verdana"', false)
            ->assertSee('data-spacing="wider"', false)
            ->assertSee('data-captions="large"', false)
            ->assertSee('data-a11y="on"', false);
    }

    public function test_invalid_preferences_are_rejected_and_defaults_add_nothing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', ['font_size_percent' => 100, 'tts_rate' => '1', 'font_style' => '"><script>'])
            ->assertSessionHasErrors('font_style');

        $this->get('/dashboard')->assertDontSee('data-font', false)->assertDontSee('data-spacing', false)->assertDontSee('data-a11y', false);
    }

    public function test_japanese_marking_tags_only_japanese_and_escapes_html(): void
    {
        $this->assertSame('Нэр үг + <span lang="ja">です</span>', (string) JapaneseText::mark('Нэр үг + です'));
        $this->assertSame('<span lang="ja">私</span>: Би', (string) JapaneseText::mark('私: Би'));
        $this->assertSame('Зөвхөн Монгол', (string) JapaneseText::mark('Зөвхөн Монгол'));
        $this->assertSame('&lt;script&gt;<span lang="ja">私</span>', (string) JapaneseText::mark('<script>私'));
        $this->assertSame('', (string) JapaneseText::mark(null));
    }

    public function test_forms_use_native_required_without_redundant_aria(): void
    {
        $this->get('/login')->assertSee(' required', false)->assertDontSee('aria-required', false);
    }

    public function test_my_progress_page_shows_only_own_published_data(): void
    {
        $student = User::factory()->create();
        $category = Category::create(['name' => 'К', 'slug' => 'k']);
        $course = Course::create(['category_id' => $category->id, 'title' => 'Сургалт', 'slug' => 'c', 'description' => 'Т', 'is_published' => true]);
        $done = Lesson::create(['course_id' => $course->id, 'title' => 'Дууссан хичээл', 'is_published' => true, 'duration_seconds' => 600]);
        $ongoing = Lesson::create(['course_id' => $course->id, 'title' => 'Явж буй хичээл', 'is_published' => true, 'duration_seconds' => 600]);
        $draft = Lesson::create(['course_id' => $course->id, 'title' => 'Ноорог хичээл', 'is_published' => false]);
        $foreign = Lesson::create(['course_id' => $course->id, 'title' => 'Бусдын хичээл', 'is_published' => true]);

        Progress::create(['user_id' => $student->id, 'lesson_id' => $done->id, 'percent' => 100, 'is_completed' => true, 'completed_at' => now(), 'last_position_seconds' => 5, 'last_watched_at' => now()]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $ongoing->id, 'percent' => 50, 'last_position_seconds' => 300, 'last_watched_at' => now()]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $draft->id, 'percent' => 10, 'last_watched_at' => now()]);
        Progress::create(['user_id' => User::factory()->create()->id, 'lesson_id' => $foreign->id, 'percent' => 10, 'last_watched_at' => now()]);

        $this->actingAs($student)->get('/progress')
            ->assertOk()
            ->assertSee('Дууссан хичээл')
            ->assertSee('Явж буй хичээл')
            ->assertSee('50% үзсэн')
            ->assertSee('Дууссан хичээлүүд (1)')
            ->assertDontSee('Ноорог хичээл')
            ->assertDontSee('Бусдын хичээл');

        // Буцааж гүйлгэсэн ч (байрлал 5) дууссан хичээлийн бүтэн хугацаа тоологдоно: 600 + 300 секунд.
        $this->assertSame(900, $student->fresh()->watchedSeconds());
        $this->get('/progress')->assertSee('15 минут');

        $this->actingAs($this->admin())->get('/progress')->assertForbidden();
    }

    public function test_admin_can_close_registration_and_set_contact(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/settings')->assertOk()->assertSee('Имэйл илгээлт')->assertDontSee(config('database.connections.mysql.password') ?: 'no-password-configured');

        $this->put('/admin/settings', ['support_contact' => 'Утас: 7000-0000'])->assertSessionHas('status');

        $this->assertFalse(SystemSetting::registrationOpen());
        $this->assertSame('Утас: 7000-0000', SystemSetting::supportContact());

        $this->post('/logout');

        $this->get('/')->assertSee('Холбоо барих: Утас: 7000-0000')->assertDontSee(route('register'), false);
        $this->get('/register')->assertRedirect(route('login'));
        $this->post('/register', ['name' => 'Х', 'email' => 'x@example.com', 'password' => 'nuutsug123', 'password_confirmation' => 'nuutsug123'])
            ->assertRedirect(route('login'));
        $this->assertNull(User::where('email', 'x@example.com')->first());

        $this->actingAs($this->admin())->put('/admin/settings', ['registration_open' => '1']);
        $this->post('/logout');
        $this->get('/register')->assertOk();
    }

    public function test_login_and_register_headers_show_only_home_and_auth_actions(): void
    {
        foreach (['/login', '/register'] as $path) {
            $header = str($this->get($path)->assertOk()->getContent())->between('<header', '</header>');

            $this->assertStringContainsString('Нүүр', $header);
            $this->assertStringContainsString('Нэвтрэх', $header);
            $this->assertStringContainsString('Бүртгүүлэх', $header);
            $this->assertStringContainsString(config('app.name'), $header);
            $this->assertStringNotContainsString('Сургалтууд', $header);
            $this->assertStringNotContainsString('Системийн тухай', $header);
            $this->assertSame(1, substr_count($this->get($path)->getContent(), 'class="public-header"'));
        }
    }

    public function test_other_public_pages_keep_the_full_header(): void
    {
        foreach (['/', '/about', '/courses', '/forgot-password'] as $path) {
            $header = str($this->get($path)->assertOk()->getContent())->between('<header', '</header>');

            $this->assertStringContainsString('Сургалтууд', $header);
            $this->assertStringContainsString('Системийн тухай', $header);
        }
    }

    public function test_student_cannot_change_system_settings(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/settings')->assertForbidden();
        $this->put('/admin/settings', ['support_contact' => 'hack'])->assertForbidden();
        $this->assertNull(SystemSetting::supportContact());
    }
}
