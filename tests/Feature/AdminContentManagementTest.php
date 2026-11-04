<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::factory()->create();
        $this->admin->role = UserRole::Admin;
        $this->admin->save();
        $this->actingAs($this->admin);
    }

    private function category(string $name = 'Компьютер'): Category
    {
        return Category::create(['name' => $name, 'slug' => 'c-'.Category::count()]);
    }

    private function course(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'category_id' => ($attributes['category_id'] ?? $this->category('К'.Course::count())->id),
            'title' => 'Сургалт',
            'slug' => 'course-'.Course::count(),
            'description' => 'Тайлбар',
        ], $attributes));
    }

    private function lesson(?Course $course = null, array $attributes = []): Lesson
    {
        return Lesson::create(array_merge(['course_id' => ($course ?? $this->course())->id, 'title' => 'Хичээл'], $attributes));
    }

    private function upload(Lesson $lesson, MediaType $type, UploadedFile $file, array $extra = [])
    {
        return $this->post(route('admin.lessons.media.store', $lesson), array_merge(['type' => $type->value, 'file' => $file], $extra));
    }

    public function test_admin_can_create_user_with_role_and_status(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'Шинэ Админ',
            'email' => 'NEW@example.com',
            'role' => 'admin',
            'is_active' => '1',
            'password' => 'nuutsug123',
            'password_confirmation' => 'nuutsug123',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('nuutsug123', $user->password));
        $this->assertNotNull($user->settings);
    }

    public function test_admin_can_edit_user_and_empty_password_keeps_old_one(): void
    {
        $user = User::factory()->create();

        $this->put(route('admin.users.update', $user), [
            'name' => 'Өөрчилсөн',
            'email' => $user->email,
            'role' => 'student',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Өөрчилсөн', $user->name);
        $this->assertFalse($user->is_active);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_admin_cannot_demote_or_deactivate_self_through_edit_form(): void
    {
        $this->put(route('admin.users.update', $this->admin), [
            'name' => 'Админ',
            'email' => $this->admin->email,
            'role' => 'student',
        ])->assertSessionHasNoErrors();

        $this->admin->refresh();
        $this->assertTrue($this->admin->isAdmin());
        $this->assertTrue($this->admin->is_active);
    }

    public function test_duplicate_email_and_invalid_role_are_rejected(): void
    {
        $other = User::factory()->create();

        $this->post(route('admin.users.store'), [
            'name' => 'Х', 'email' => $other->email, 'role' => 'superuser',
            'password' => 'nuutsug123', 'password_confirmation' => 'nuutsug123',
        ])->assertSessionHasErrors(['email', 'role']);
    }

    public function test_category_crud_with_cyrillic_slug(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Гадаад хэл', 'description' => 'Тайлбар'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Гадаад хэл')->firstOrFail();
        $this->assertNotSame('', $category->slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $category->slug);
        $slug = $category->slug;

        $this->put(route('admin.categories.update', $category), ['name' => 'Шинэ нэр']);
        $category->refresh();
        $this->assertSame('Шинэ нэр', $category->name);
        $this->assertSame($slug, $category->slug);

        $this->delete(route('admin.categories.destroy', $category));
        $this->assertNull($category->fresh());
    }

    public function test_category_with_courses_shows_error_instead_of_crashing(): void
    {
        $course = $this->course();

        $this->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $course->category_id))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertNotNull($course->category->fresh());
        $this->get(route('admin.categories.index'))->assertOk();
    }

    public function test_course_can_be_created_with_thumbnail_and_requires_alt_text(): void
    {
        $category = $this->category();
        $data = ['category_id' => $category->id, 'title' => 'Excel сургалт', 'description' => 'Тайлбар', 'is_published' => '1'];

        $this->post(route('admin.courses.store'), $data + ['thumbnail' => UploadedFile::fake()->image('a.png')])
            ->assertSessionHasErrors('thumbnail_alt');
        $this->assertSame(0, Course::count());

        $this->post(route('admin.courses.store'), $data + ['thumbnail' => UploadedFile::fake()->image('a.png'), 'thumbnail_alt' => 'Хүснэгтийн зураг'])
            ->assertSessionHasNoErrors();

        $course = Course::firstOrFail();
        $this->assertTrue($course->is_published);
        $this->assertSame($this->admin->id, $course->created_by);
        Storage::disk('public')->assertExists($course->thumbnail_path);

        $this->post('/logout');
        $this->get('/courses')->assertSee('alt="Хүснэгтийн зураг"', false);
    }

    public function test_svg_and_non_image_thumbnails_are_rejected(): void
    {
        $data = ['category_id' => $this->category()->id, 'title' => 'С', 'description' => 'Т', 'thumbnail_alt' => 'А'];

        $this->post(route('admin.courses.store'), $data + ['thumbnail' => UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')])
            ->assertSessionHasErrors('thumbnail');
        $this->post(route('admin.courses.store'), $data + ['thumbnail' => UploadedFile::fake()->create('x.php', 1, 'application/x-php')])
            ->assertSessionHasErrors('thumbnail');
    }

    public function test_same_course_title_gets_unique_slug_and_edit_keeps_slug(): void
    {
        $category = $this->category();
        $data = ['category_id' => $category->id, 'title' => 'Ижил нэр', 'description' => 'Т'];

        $this->post(route('admin.courses.store'), $data);
        $this->post(route('admin.courses.store'), $data);

        [$first, $second] = Course::orderBy('id')->get();
        $this->assertNotSame($first->slug, $second->slug);

        $slug = $first->slug;
        $this->put(route('admin.courses.update', $first), ['title' => 'Өөр нэр'] + $data);
        $this->assertSame('Өөр нэр', $first->fresh()->title);
        $this->assertSame($slug, $first->fresh()->slug);
    }

    public function test_replacing_and_removing_thumbnail_deletes_old_file(): void
    {
        $category = $this->category();
        $data = ['category_id' => $category->id, 'title' => 'С', 'description' => 'Т', 'thumbnail_alt' => 'А'];

        $this->post(route('admin.courses.store'), $data + ['thumbnail' => UploadedFile::fake()->image('a.png')]);
        $course = Course::firstOrFail();
        $old = $course->thumbnail_path;

        $this->put(route('admin.courses.update', $course), $data + ['thumbnail' => UploadedFile::fake()->image('b.png')]);
        $new = $course->fresh()->thumbnail_path;
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);

        $this->put(route('admin.courses.update', $course), array_diff_key($data, ['thumbnail_alt' => 1]) + ['remove_thumbnail' => '1'])
            ->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($new);
        $this->assertNull($course->fresh()->thumbnail_path);
    }

    public function test_lesson_crud_appends_position(): void
    {
        $course = $this->course();

        $this->post(route('admin.courses.lessons.store', $course), ['title' => 'Нэг', 'transcript' => 'Бичвэр'])->assertSessionHasNoErrors();
        $this->post(route('admin.courses.lessons.store', $course), ['title' => 'Хоёр', 'is_published' => '1'])->assertSessionHasNoErrors();

        [$one, $two] = $course->lessons()->get();
        $this->assertSame([1, 2], [$one->position, $two->position]);
        $this->assertFalse($one->is_published);
        $this->assertTrue($two->is_published);

        $this->put(route('admin.lessons.update', $one), ['title' => 'Нэг шинэ', 'is_published' => '1']);
        $this->assertSame('Нэг шинэ', $one->fresh()->title);

        $this->delete(route('admin.lessons.destroy', $two));
        $this->assertNull($two->fresh());
    }

    public function test_lessons_can_be_reordered(): void
    {
        $course = $this->course();
        $first = $this->lesson($course, ['title' => 'A', 'position' => 1]);
        $second = $this->lesson($course, ['title' => 'B', 'position' => 2]);

        $this->patch(route('admin.lessons.move', $second), ['direction' => 'up'])
            ->assertSessionHas('status', 'B хичээл одоо 1-р байрлалд байна.');
        $this->assertSame(['B', 'A'], $course->lessons()->pluck('title')->all());

        // Эхнийхийг дээш зөөх нь юу ч өөрчлөхгүй.
        $this->patch(route('admin.lessons.move', $second), ['direction' => 'up']);
        $this->assertSame(['B', 'A'], $course->lessons()->pluck('title')->all());

        $this->patch(route('admin.lessons.move', $first), ['direction' => 'sideways'])->assertSessionHasErrors('direction');
    }

    public function test_video_upload_is_stored_privately_with_safe_name_and_duration(): void
    {
        $lesson = $this->lesson();

        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('../../evil name.mp4', 100, 'video/mp4'), ['duration_seconds' => 125])
            ->assertRedirect(route('admin.lessons.media', $lesson))
            ->assertSessionHas('status');

        $media = $lesson->media()->firstOrFail();
        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertSame(102400, $media->size_bytes);
        $this->assertSame(125, $lesson->fresh()->duration_seconds);
        $this->assertStringStartsWith("lesson-media/{$lesson->id}/", $media->file_path);
        $this->assertStringNotContainsString('evil', $media->file_path);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\/\-.]+\.mp4$/', $media->file_path);
        Storage::disk('local')->assertExists($media->file_path);
        Storage::disk('public')->assertMissing($media->file_path);
    }

    public function test_wrong_file_types_are_rejected(): void
    {
        $lesson = $this->lesson();

        // Өргөтгөл зөв, агуулга буруу.
        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('fake.mp4', 1, 'application/x-php'))
            ->assertSessionHasErrors(['file' => 'Энэ файлын төрөл зөвшөөрөгдөхгүй. Зөвшөөрөгдөх төрөл: MP4, WEBM.']);
        // Агуулга зөв, өргөтгөл буруу.
        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('video.php', 1, 'video/mp4'))
            ->assertSessionHasErrors('file');
        $this->upload($lesson, MediaType::AudioDescription, UploadedFile::fake()->create('v.mp4', 1, 'video/mp4'))
            ->assertSessionHasErrors('file');
        // Файлын төрөл буруу.
        $this->post(route('admin.lessons.media.store', $lesson), ['type' => 'hack', 'file' => UploadedFile::fake()->create('v.mp4', 1, 'video/mp4')])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, $lesson->media()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_oversized_file_is_rejected(): void
    {
        config(['lms.uploads.video_kb' => 10]);

        $this->upload($this->lesson(), MediaType::Video, UploadedFile::fake()->create('big.mp4', 11, 'video/mp4'))
            ->assertSessionHasErrors('file');
    }

    public function test_vtt_content_is_validated(): void
    {
        $lesson = $this->lesson();

        $this->upload($lesson, MediaType::Subtitle, UploadedFile::fake()->createWithContent('bad.vtt', 'энгийн текст'))
            ->assertSessionHasErrors('file');
        $this->upload($lesson, MediaType::Subtitle, UploadedFile::fake()->createWithContent('bad.vtt', "WEBVTT\n\n\xFF\xFE"))
            ->assertSessionHasErrors('file');

        $this->upload($lesson, MediaType::Subtitle, UploadedFile::fake()->createWithContent('ok.vtt', "\xEF\xBB\xBFWEBVTT\n\n00:00.000 --> 00:01.000\nМонгол\n"))
            ->assertSessionHasNoErrors();

        $this->assertSame('text/vtt', $lesson->media()->firstOrFail()->mime_type);
    }

    public function test_upload_returns_json_for_progress_form(): void
    {
        $lesson = $this->lesson();
        $file = UploadedFile::fake()->create('a.webm', 1, 'video/webm');

        $this->post(route('admin.lessons.media.store', $lesson), ['type' => 'video', 'file' => $file], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['redirect' => route('admin.lessons.media', $lesson)]);

        $this->post(route('admin.lessons.media.store', $lesson), ['type' => 'video'], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_replacing_media_removes_old_file_and_keeps_one_row(): void
    {
        $lesson = $this->lesson();

        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('a.mp4', 1, 'video/mp4'));
        $old = $lesson->media()->firstOrFail()->file_path;

        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('b.mp4', 1, 'video/mp4'));
        $this->assertSame(1, $lesson->media()->count());
        $new = $lesson->media()->firstOrFail()->file_path;

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($new);
    }

    public function test_deleting_media_removes_file_and_video_duration(): void
    {
        $lesson = $this->lesson();
        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('a.mp4', 1, 'video/mp4'), ['duration_seconds' => 60]);
        $media = $lesson->media()->firstOrFail();

        $this->delete(route('admin.media.destroy', $media))->assertSessionHas('status');

        Storage::disk('local')->assertMissing($media->file_path);
        $this->assertNull($media->fresh());
        $this->assertNull($lesson->fresh()->duration_seconds);
    }

    public function test_deleting_course_removes_all_files_and_progress(): void
    {
        $category = $this->category();
        $this->post(route('admin.courses.store'), [
            'category_id' => $category->id, 'title' => 'С', 'description' => 'Т',
            'thumbnail' => UploadedFile::fake()->image('a.png'), 'thumbnail_alt' => 'А',
        ]);
        $course = Course::firstOrFail();
        $lesson = $this->lesson($course);
        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('a.mp4', 1, 'video/mp4'));
        Progress::create(['user_id' => User::factory()->create()->id, 'lesson_id' => $lesson->id]);

        $this->assertNotEmpty(Storage::disk('local')->allFiles());

        $this->delete(route('admin.courses.destroy', $course));

        $this->assertSame(0, Lesson::count());
        $this->assertSame(0, LessonMedia::count());
        $this->assertSame(0, Progress::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_student_cannot_reach_any_admin_action(): void
    {
        $lesson = $this->lesson();
        $this->upload($lesson, MediaType::Video, UploadedFile::fake()->create('a.mp4', 1, 'video/mp4'));
        $media = $lesson->media()->firstOrFail();
        $course = $lesson->course;

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.courses.index'))->assertForbidden();
        $this->get(route('admin.categories.index'))->assertForbidden();
        $this->get(route('admin.users.create'))->assertForbidden();
        $this->post(route('admin.courses.store'), [])->assertForbidden();
        $this->delete(route('admin.courses.destroy', $course))->assertForbidden();
        $this->post(route('admin.lessons.media.store', $lesson), [])->assertForbidden();
        $this->delete(route('admin.media.destroy', $media))->assertForbidden();

        $this->assertNotNull($course->fresh());
        $this->assertNotNull($media->fresh());
    }

    public function test_admin_pages_render_and_list_filters_work(): void
    {
        $course = $this->course(['title' => 'Хайх сургалт', 'is_published' => true]);
        $this->course(['title' => 'Ноорог гарчиг']);
        $lesson = $this->lesson($course);

        foreach ([
            route('admin.users.create'),
            route('admin.users.edit', $this->admin),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.courses.create'),
            route('admin.courses.edit', $course),
            route('admin.courses.lessons.index', $course),
            route('admin.courses.lessons.create', $course),
            route('admin.lessons.edit', $lesson),
            route('admin.lessons.media', $lesson),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('admin.courses.index', ['status' => 'published']))
            ->assertSee('Хайх сургалт')
            ->assertDontSee('Ноорог гарчиг');
        $this->get(route('admin.courses.index', ['q' => 'Ноорог']))
            ->assertSee('Ноорог гарчиг')
            ->assertDontSee('Хайх сургалт');
        $this->get(route('admin.courses.index', ['q' => ['x'], 'category' => ['y'], 'status' => ['z']]))->assertOk();
    }

    public function test_media_page_states_missing_files_honestly(): void
    {
        $lesson = $this->lesson();

        $this->get(route('admin.lessons.media', $lesson))->assertSee('Оруулаагүй');

        $this->upload($lesson, MediaType::AudioDescription, UploadedFile::fake()->create('t.mp3', 1, 'audio/mpeg'));

        $this->get(route('admin.lessons.media', $lesson))->assertSee('t.mp3')->assertSee('Оруулсан');
    }
}
