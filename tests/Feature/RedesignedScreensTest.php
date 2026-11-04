<?php

namespace Tests\Feature;

use App\Enums\FeedbackStatus;
use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RedesignedScreensTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $attributes = []): Course
    {
        $category = Category::firstOrCreate(['slug' => 'computer'], ['name' => 'Компьютер']);

        return Course::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Excel сургалт',
            'slug' => 'excel',
            'description' => 'Тайлбар',
            'is_published' => true,
        ], $attributes));
    }

    private function lesson(?Course $course = null, array $attributes = []): Lesson
    {
        return Lesson::create(array_merge([
            'course_id' => ($course ?? $this->course())->id,
            'title' => 'Эхний хичээл',
            'transcript' => 'Хичээлийн бүрэн бичвэр.',
            'is_published' => true,
        ], $attributes));
    }

    private function media(Lesson $lesson, MediaType $type, string $path = 'media/file.bin', string $mime = 'video/webm'): LessonMedia
    {
        return LessonMedia::create([
            'lesson_id' => $lesson->id,
            'type' => $type->value,
            'file_path' => $path,
            'original_name' => 'file.bin',
            'mime_type' => $mime,
            'size_bytes' => 10,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_landing_page_shows_only_real_categories_and_courses(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Хүн бүрт хүртээмжтэй сургалт')
            ->assertDontSee('Шинэ сургалтууд');

        $this->course(['title' => 'Нийтлэгдсэн']);
        $this->course(['title' => 'Ноорог', 'slug' => 'draft', 'is_published' => false]);

        $this->get('/')
            ->assertSee('Шинэ сургалтууд')
            ->assertSee('Нийтлэгдсэн')
            ->assertDontSee('Ноорог')
            ->assertSee('1 сургалт');
    }

    public function test_student_shell_has_sidebar_search_and_working_links(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk()
            ->assertSee('id="app-sidebar"', false)
            ->assertSee('role="search"', false)
            ->assertSee(route('student.my-courses'), false)
            ->assertSee(route('student.bookmarks'), false)
            ->assertSee(route('settings.edit'), false)
            ->assertDontSee(route('admin.users.index'), false)
            ->assertSee('шинэ мэдэгдэл алга');
    }

    public function test_notifications_come_from_real_feedback(): void
    {
        $student = User::factory()->create();
        $feedback = Feedback::create(['user_id' => $student->id, 'subject' => 'Миний асуулт', 'message' => 'Агуулга']);

        $this->actingAs($student)->get('/dashboard')->assertDontSee('Хариу ирлээ: Миний асуулт');

        $feedback->status = FeedbackStatus::Answered;
        $feedback->replied_at = now();
        $feedback->save();

        $this->actingAs($student)->get('/dashboard')
            ->assertSee('Хариу ирлээ: Миний асуулт')
            ->assertSee('Мэдэгдэл, 1 шинэ');
    }

    public function test_lesson_requires_login_and_published_state(): void
    {
        $lesson = $this->lesson();
        $url = route('lessons.show', [$lesson->course, $lesson]);

        $this->get($url)->assertRedirect(route('login'));

        $lesson->is_published = false;
        $lesson->save();
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    public function test_lesson_url_must_match_course(): void
    {
        $lesson = $this->lesson();
        $other = $this->course(['slug' => 'other']);

        $this->actingAs(User::factory()->create())
            ->get("/courses/{$other->slug}/lessons/{$lesson->id}")
            ->assertNotFound();
    }

    public function test_lesson_without_media_states_this_honestly_and_shows_no_player(): void
    {
        $lesson = $this->lesson();

        $this->actingAs(User::factory()->create())
            ->get(route('lessons.show', [$lesson->course, $lesson]))
            ->assertOk()
            ->assertSee('Энэ хичээлд видео оруулаагүй байна.')
            ->assertDontSee('data-player ', false)
            ->assertDontSee('data-control="play"', false)
            ->assertSee('Хичээлийн бүрэн бичвэр.');
    }

    public function test_player_never_autoplays_and_reports_missing_description(): void
    {
        $lesson = $this->lesson();
        $this->media($lesson, MediaType::Video);

        $this->actingAs(User::factory()->create())
            ->get(route('lessons.show', [$lesson->course, $lesson]))
            ->assertSee('data-control="play"', false)
            ->assertDontSee('autoplay', false)
            ->assertSee('Энэ хичээлд аудио тайлбар оруулаагүй байна.')
            ->assertSee('Энэ хичээлд хадмал оруулаагүй байна.')
            ->assertDontSee('data-control="describe"', false)
            ->assertDontSee('data-control="captions"', false);
    }

    public function test_player_offers_description_and_subtitles_when_uploaded(): void
    {
        $lesson = $this->lesson();
        $this->media($lesson, MediaType::Video);
        $this->media($lesson, MediaType::AudioDescription, 'media/ad.wav', 'audio/wav');
        $this->media($lesson, MediaType::Subtitle, 'media/s.vtt', 'text/vtt');

        $this->actingAs(User::factory()->create())
            ->get(route('lessons.show', [$lesson->course, $lesson]))
            ->assertSee('data-control="describe"', false)
            ->assertSee('data-control="captions"', false)
            ->assertSee('<track kind="subtitles"', false)
            ->assertDontSee('аудио тайлбар оруулаагүй');
    }

    public function test_media_requires_login_and_published_lesson(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('media/file.bin', 'content');

        $lesson = $this->lesson();
        $media = $this->media($lesson, MediaType::Video);

        $this->get(route('media.show', $media))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('media.show', $media))->assertOk();

        $lesson->is_published = false;
        $lesson->save();
        $this->actingAs(User::factory()->create())->get(route('media.show', $media))->assertNotFound();
    }

    public function test_progress_is_saved_and_resumed(): void
    {
        $student = User::factory()->create();
        $lesson = $this->lesson();
        $this->media($lesson, MediaType::Video);

        $this->actingAs($student)
            ->postJson(route('lessons.progress', $lesson), ['position' => 50, 'duration' => 200])
            ->assertOk()
            ->assertJson(['percent' => 25, 'is_completed' => false]);

        $this->actingAs($student)
            ->get(route('lessons.show', [$lesson->course, $lesson]))
            ->assertSee('data-resume="50"', false);
    }

    public function test_progress_completes_at_end_and_never_regresses(): void
    {
        $student = User::factory()->create();
        $lesson = $this->lesson();
        $url = route('lessons.progress', $lesson);

        $this->actingAs($student)->postJson($url, ['position' => 190, 'duration' => 200])
            ->assertJson(['percent' => 100, 'is_completed' => true]);

        $this->actingAs($student)->postJson($url, ['position' => 5, 'duration' => 200])
            ->assertJson(['percent' => 100, 'is_completed' => true]);

        $progress = Progress::firstOrFail();
        $this->assertSame(5, $progress->last_position_seconds);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_progress_cannot_exceed_duration_or_be_saved_by_admin(): void
    {
        $lesson = $this->lesson(null, ['duration_seconds' => 100]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('lessons.progress', $lesson), ['position' => 99999, 'duration' => 1])
            ->assertJson(['percent' => 100]);
        $this->assertSame(100, Progress::firstOrFail()->last_position_seconds);

        $this->actingAs($this->admin())
            ->postJson(route('lessons.progress', $lesson), ['position' => 1, 'duration' => 100])
            ->assertForbidden();
    }

    public function test_lesson_can_be_marked_complete_and_bookmarked(): void
    {
        $student = User::factory()->create();
        $lesson = $this->lesson();

        $this->actingAs($student)->post(route('lessons.complete', $lesson))->assertSessionHas('status');
        $this->assertTrue(Progress::firstOrFail()->is_completed);

        $this->actingAs($student)->post(route('lessons.bookmark', $lesson));
        $this->assertSame(1, Bookmark::count());
        $this->actingAs($student)->get('/bookmarks')->assertOk()->assertSee('Эхний хичээл');

        $this->actingAs($student)->post(route('lessons.bookmark', $lesson));
        $this->assertSame(0, Bookmark::count());
    }

    public function test_accessibility_settings_are_saved_and_applied_to_every_page(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->put('/settings', [
            'font_size_percent' => 150,
            'tts_rate' => '1.25',
            'high_contrast' => '1',
        ])->assertSessionHasNoErrors();

        $settings = $student->fresh()->settings;
        $this->assertSame(150, $settings->font_size_percent);
        $this->assertTrue($settings->high_contrast);
        $this->assertFalse($settings->subtitles_enabled);
        $this->assertSame(1.25, $settings->tts_rate);

        $this->actingAs($student->fresh())->get('/dashboard')
            ->assertSee('data-contrast="high"', false)
            ->assertSee('font-size: 150%', false);
    }

    public function test_settings_reject_values_outside_the_allowed_list(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/settings', ['font_size_percent' => 900, 'tts_rate' => '9'])
            ->assertSessionHasErrors(['font_size_percent', 'tts_rate']);
    }

    public function test_student_dashboard_sections_use_real_data(): void
    {
        $student = User::factory()->create();
        $started = $this->course(['title' => 'Эхэлсэн сургалт']);
        $lesson = $this->lesson($started);
        $this->course(['title' => 'Шинэ анги', 'slug' => 'new-one']);

        Progress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'percent' => 40,
            'last_position_seconds' => 3900,
            'last_watched_at' => now(),
        ]);

        $this->actingAs($student)->get('/dashboard')
            ->assertSee('Сүүлд үзсэн хичээл')
            ->assertSee('Үргэлжлүүлэх')
            ->assertSee('1 цаг 5 минут')
            ->assertSee('Үзсэн: Эхний хичээл')
            ->assertSee('Шинэ анги');
    }

    public function test_admin_can_filter_users(): void
    {
        User::factory()->create(['name' => 'Болд Хайх', 'email' => 'bold@example.com']);
        $inactive = User::factory()->create(['name' => 'Сараа Идэвхгүй']);
        $inactive->is_active = false;
        $inactive->save();

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/users?q=bold')
            ->assertSee('Болд Хайх')
            ->assertDontSee('Сараа Идэвхгүй');

        $this->actingAs($admin)->get('/admin/users?status=inactive')
            ->assertSee('Сараа Идэвхгүй')
            ->assertDontSee('Болд Хайх');

        $this->actingAs($admin)->get('/admin/users?q[]=x&role[]=y&status[]=z')->assertOk();
    }

    public function test_admin_can_toggle_and_delete_user_but_not_self(): void
    {
        $admin = $this->admin();
        $student = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.toggle-active', $student))->assertSessionHas('status');
        $this->assertFalse($student->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $student))->assertSessionHas('status');
        $this->assertNull($student->fresh());

        $this->actingAs($admin)->patch(route('admin.users.toggle-active', $admin))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_student_cannot_manage_users(): void
    {
        $student = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($student)->get('/admin/users')->assertForbidden();
        $this->actingAs($student)->delete(route('admin.users.destroy', $target))->assertForbidden();
        $this->assertNotNull($target->fresh());
    }

    public function test_admin_dashboard_shows_chart_table_only_when_there_is_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')
            ->assertSee('Энэ хугацаанд шинэ бүртгэл алга байна.')
            ->assertDontSee('Хүснэгтээр харах');

        User::factory()->create();

        $this->actingAs($admin)->get('/admin')
            ->assertSee('Нийт 1 бүртгэл')
            ->assertSee('Хүснэгтээр харах');
    }
}
